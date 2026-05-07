<?php
require_once __DIR__ . '/../Models/Item.php';
require_once __DIR__ . '/../Models/RackCategory.php';

class PosController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
    }

    private function expireReservations() {
        $db = getDB();
        $stmtCheckExpire = $db->query("SHOW COLUMNS FROM reservations LIKE 'expiration_date'");
        $hasExpiration = $stmtCheckExpire->fetch() !== false;
        if (!$hasExpiration) {
            return;
        }

        $stmt = $db->prepare(
            "SELECT r.id, r.item_id
            FROM reservations r
            WHERE r.status IN ('reserved', 'pending')
            AND r.expiration_date IS NOT NULL
            AND r.expiration_date < NOW()"
        );
        $stmt->execute();
        $expiredReservations = $stmt->fetchAll();

        foreach ($expiredReservations as $res) {
            try {
                $db->beginTransaction();
                $stmtUpdateRes = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
                $stmtUpdateRes->execute(['expired', $res['id']]);

                $stmtUpdateItem = $db->prepare('UPDATE items SET status = ? WHERE id = ?');
                $stmtUpdateItem->execute(['available', $res['item_id']]);
                $db->commit();
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
            }
        }
    }

    public function index() {
        $rackCategoryModel = new RackCategory();
        $categories = $rackCategoryModel->getAll();
        $this->view('pos/index', ['categories' => $categories]);
    }

    public function getItems() {
        $this->expireReservations();

        $section = $_GET['section'] ?? null;
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $itemModel = new Item();
        $items = $itemModel->getAll($section, $category, $search);
        
        $this->json($items);
    }

    public function getRackCategories() {
        $section = $_GET['section'] ?? null;
        
        $rackCategoryModel = new RackCategory();
        if ($section === 'women') {
            $categories = $rackCategoryModel->getByGender('women');
        } elseif ($section === 'men') {
            $categories = $rackCategoryModel->getByGender('men');
        } else {
            $categories = $rackCategoryModel->getAll();
        }
        
        $this->json($categories);
    }

    public function checkout() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['items'], $data['total'], $data['payment_method'])) {
            $this->json(['success' => false, 'message' => 'Invalid checkout data.']);
        }

        $items = $data['items'];
        $total = (float) $data['total'];
        $bargainedPrice = isset($data['bargained_price']) ? (float) $data['bargained_price'] : null;
        $paymentMethod = $data['payment_method'];
        $cashReceived = isset($data['cash_received']) ? (float) $data['cash_received'] : null;
        $change = isset($data['change']) ? (float) $data['change'] : null;
        $imageData = isset($data['image']) ? $data['image'] : null;

        if (!is_array($items) || count($items) === 0) {
            $this->json(['success' => false, 'message' => 'Cart cannot be empty.']);
        }
        $allowedPaymentMethods = ['cash', 'gcash', 'paymaya', 'card', 'bdo', 'bpi', 'unionbank', 'maribank', 'other_bank'];
        if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
            $this->json(['success' => false, 'message' => 'Invalid payment method.']);
        }
        if ($paymentMethod === 'cash' && ($cashReceived === null || round($cashReceived, 2) < round($total, 2))) {
            $this->json(['success' => false, 'message' => 'Cash received must cover the total amount. Received: ' . $cashReceived . ' Total: ' . $total]);
        }

        $db = getDB();
        try {
            $db->beginTransaction();

            $imageUrl = null;
            if ($imageData) {
                $upload_dir = __DIR__ . '/../../../public/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $imageData = str_replace('data:image/png;base64,', '', $imageData);
                $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
                $imageData = str_replace('data:image/jpg;base64,', '', $imageData);
                $imageData = str_replace(' ', '+', $imageData);
                $imageData = base64_decode($imageData);
                
                $file_name = 'sale_' . time() . '_' . uniqid() . '.png';
                $target_file = $upload_dir . $file_name;
                
                if (file_put_contents($target_file, $imageData)) {
                    $imageUrl = '/thrift_pos/uploads/' . $file_name;
                }
            }

            $salesColumns = $db->query("SHOW COLUMNS FROM sales LIKE 'status'")->fetch();
            if ($salesColumns) {
                $stmtInsertSale = $db->prepare('INSERT INTO sales (user_id, total_amount, payment_method, status, cash_received, `change`, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmtInsertSale->execute([$_SESSION['user_id'], $total, $paymentMethod, 'paid', $cashReceived, $change, $imageUrl]);
            } else {
                $stmtInsertSale = $db->prepare('INSERT INTO sales (user_id, total_amount, payment_method, cash_received, `change`, image_url) VALUES (?, ?, ?, ?, ?, ?)');
                $stmtInsertSale->execute([$_SESSION['user_id'], $total, $paymentMethod, $cashReceived, $change, $imageUrl]);
            }
            $saleId = $db->lastInsertId();

            $stmtInsertItem = $db->prepare('INSERT INTO sale_items (sale_id, item_id, price, discount, final_price) VALUES (?, ?, ?, ?, ?)');

            $originalTotal = 0;
            $totalItemsQuantity = 0;
            
            // First, calculate original total and total quantity
            foreach ($items as $item) {
                if (isset($item['category_id'])) {
                    $quantity = $item['quantity'] ?? 1;
                    $price = (float) $item['selected_price'];
                    $originalTotal += $price * $quantity;
                    $totalItemsQuantity += $quantity;
                } else if (isset($item['id'])) {
                    $price = isset($item['price']) ? (float) $item['price'] : 0;
                    $discount = isset($item['discount']) ? (float) $item['discount'] : 0;
                    $finalPrice = isset($item['final_price']) ? (float) $item['final_price'] : $price - $discount;
                    $originalTotal += $finalPrice;
                    $totalItemsQuantity += 1;
                }
            }

            // Process items and apply bargained price if needed
            foreach ($items as $item) {
                if (isset($item['category_id'])) {
                    $rackCategoryModel = new RackCategory();
                    $category = $rackCategoryModel->findById($item['category_id']);
                    
                    if (!$category) {
                        throw new Exception('Category not found: ' . $item['category_id']);
                    }

                    $quantity = $item['quantity'] ?? 1;
                    $originalItemPrice = (float) $item['selected_price'];
                    
                    // Check stock availability
                    if ($category['stock_available'] < $quantity) {
                        throw new Exception('Not enough stock available for ' . $category['name'] . '. Available: ' . $category['stock_available'] . ', Requested: ' . $quantity);
                    }

                    // Decrement stock
                    $stmtUpdateStock = $db->prepare('UPDATE rack_categories SET stock_available = stock_available - ? WHERE id = ?');
                    $stmtUpdateStock->execute([$quantity, $item['category_id']]);
                    
                    $stmtCreateItem = $db->prepare("INSERT INTO items (name, category, gender, price, tag_color, status, batch_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    
                    for ($i = 0; $i < $quantity; $i++) {
                        $itemName = $category['name'] . ' - Sale #' . $saleId . ' Item ' . ($i + 1);
                        
                        // Calculate item price: if bargained, distribute evenly per item; else use original
                        $itemFinalPrice = $bargainedPrice ? ($total / $totalItemsQuantity) : $originalItemPrice;
                        
                        $stmtCreateItem->execute([
                            $itemName,
                            $category['name'],
                            $category['gender'],
                            $itemFinalPrice,
                            'yellow',
                            'sold',
                            'POS Sale #' . $saleId
                        ]);
                        $itemId = $db->lastInsertId();
                        
                        $stmtInsertItem->execute([$saleId, $itemId, $originalItemPrice, 0, $itemFinalPrice]);
                    }
                } else if (isset($item['id'])) {
                    $stmtCheck = $db->prepare('SELECT status FROM items WHERE id = ? FOR UPDATE');
                    $stmtCheck->execute([$item['id']]);
                    $storedItem = $stmtCheck->fetch();
                    if (!$storedItem) {
                        throw new Exception('Item with ID ' . $item['id'] . ' not found.');
                    }
                    if ($storedItem['status'] !== 'available') {
                        throw new Exception('Item with ID ' . $item['id'] . ' is not available for sale.');
                    }

                    $originalPrice = isset($item['price']) ? (float) $item['price'] : 0;
                    $discount = isset($item['discount']) ? (float) $item['discount'] : 0;
                    $originalFinalPrice = isset($item['final_price']) ? (float) $item['final_price'] : $originalPrice - $discount;
                    
                    // Calculate final price: if bargained, distribute evenly per item; else use original
                    $finalPrice = $bargainedPrice ? ($total / $totalItemsQuantity) : $originalFinalPrice;

                    $stmtInsertItem->execute([$saleId, $item['id'], $originalPrice, $discount, $finalPrice]);
                    $stmtUpdateItem = $db->prepare('UPDATE items SET status = ? WHERE id = ?');
                    $stmtUpdateItem->execute(['sold', $item['id']]);
                } else {
                    throw new Exception('Invalid item entry in cart.');
                }
            }

            $db->commit();
            $this->json(['success' => true, 'sale_id' => $saleId]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
