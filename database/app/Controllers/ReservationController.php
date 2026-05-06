<?php
require_once __DIR__ . '/../Models/RackCategory.php';

class ReservationController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        $db = getDB();
        $this->checkAndExpireReservations($db);
    }

    private function checkAndExpireReservations($db) {
        $stmtCheckExpire = $db->query("SHOW COLUMNS FROM reservations LIKE 'expiration_date'");
        $hasExpiration = $stmtCheckExpire->fetch() !== false;
        
        if (!$hasExpiration) {
            return;
        }

        $stmt = $db->prepare("
            SELECT r.id, r.category_id 
            FROM reservations r 
            WHERE r.status IN ('reserved', 'pending') 
            AND r.expiration_date IS NOT NULL 
            AND r.expiration_date < NOW()
        ");
        $stmt->execute();
        $expiredReservations = $stmt->fetchAll();

        foreach ($expiredReservations as $res) {
            try {
                $db->beginTransaction();
                $stmtUpdateRes = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
                $stmtUpdateRes->execute(['expired', $res['id']]);

                if ($res['category_id']) {
                    $stmtUpdateStock = $db->prepare('UPDATE rack_categories SET stock_available = stock_available + 1 WHERE id = ?');
                    $stmtUpdateStock->execute([$res['category_id']]);
                }

                $db->commit();
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
            }
        }
    }

    public function index() {
        $db = getDB();
        $this->checkAndExpireReservations($db);

        $reservations = $db->query("
            SELECT r.*, r.item_id, rc.name as item_name
            FROM reservations r 
            LEFT JOIN rack_categories rc ON r.category_id = rc.id 
            ORDER BY r.created_at DESC
        ")->fetchAll();
        
        $this->view('pos/reservations', ['reservations' => $reservations]);
    }

    public function add() {
        $db = getDB();
        try {
            $categoryId = $_POST['category_id'] ?? null;
            $customerName = trim($_POST['customer_name'] ?? '');
            $contactNumber = trim($_POST['contact_number'] ?? '');
            $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 1;
            $imageData = $_POST['image'] ?? null;
            $price = isset($_POST['price']) ? (float)$_POST['price'] : null;

            $contactNumber = preg_replace('/\D+/', '', $contactNumber);

            if ($duration <= 0) {
                throw new Exception('Reservation duration must be at least 1 day.');
            }

            if (!$categoryId) {
                throw new Exception('Category ID is required.');
            }
            if (empty($customerName)) {
                throw new Exception('Customer name is required.');
            }
            if (empty($contactNumber)) {
                throw new Exception('Contact number is required.');
            }
            if (!$price) {
                throw new Exception('Price selection is required.');
            }
            if (!$imageData) {
                throw new Exception('Item photo is required.');
            }

            $db->beginTransaction();

            $rackCategoryModel = new RackCategory();
            $category = $rackCategoryModel->findById($categoryId);
            if (!$category) {
                throw new Exception('Category not found');
            }

            if ($category['stock_available'] <= 0) {
                throw new Exception('No stock available for this rack.');
            }

            $stmtUpdateStock = $db->prepare('UPDATE rack_categories SET stock_available = stock_available - 1 WHERE id = ?');
            $stmtUpdateStock->execute([$categoryId]);

            $imageUrl = null;
            if ($imageData) {
                $upload_dir = __DIR__ . '/../../../public/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $imageData = str_replace('data:image/png;base64,', '', $imageData);
                $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
                $imageData = str_replace('data:image/jpg;base64,', '', $imageData);
                $imageData = str_replace(' ', '+', $imageData);
                $imageData = base64_decode($imageData);
                
                $file_name = 'reservation_' . time() . '_' . uniqid() . '.png';
                $target_file = $upload_dir . $file_name;
                
                if (file_put_contents($target_file, $imageData)) {
                    $imageUrl = '/thrift_pos/uploads/' . $file_name;
                }
            }

            $stmtEnum = $db->query("SHOW COLUMNS FROM reservations LIKE 'status'")->fetch();
            $statusValue = 'reserved';
            if ($stmtEnum && strpos($stmtEnum['Type'], "'reserved'") === false) {
                $statusValue = 'pending';
            }

            $expirationDate = date('Y-m-d H:i:s', strtotime('+' . $duration . ' days'));

            $stmtRes = $db->prepare('INSERT INTO reservations (customer_name, contact_number, status, duration_days, expiration_date, image_url, category_id, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmtRes->execute([$customerName, $contactNumber, $statusValue, $duration, $expirationDate, $imageUrl, $categoryId, $price]);

            $db->commit();

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($isAjax) {
                $this->json(['success' => true, 'message' => 'Item successfully reserved!', 'redirect' => '/thrift_pos/reservations']);
            }

            $this->redirect('/reservations');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $e->getMessage()]);
            }

            die('Reservation Error: ' . $e->getMessage());
        }
    }

    public function delete() {
        $db = getDB();
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $this->redirect('/reservations');
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare('SELECT category_id, status FROM reservations WHERE id = ?');
            $stmt->execute([$id]);
            $res = $stmt->fetch();

            if ($res) {
                if (in_array($res['status'], ['reserved', 'pending'], true) && $res['category_id']) {
                    $stmtUpdate = $db->prepare('UPDATE rack_categories SET stock_available = stock_available + 1 WHERE id = ?');
                    $stmtUpdate->execute([$res['category_id']]);
                }

                $stmtDel = $db->prepare('DELETE FROM reservations WHERE id = ?');
                $stmtDel->execute([$id]);
            }

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }

        $this->redirect('/reservations');
    }

    public function complete() {
        $db = getDB();
        $id = $_POST['id'];

        try {
            $db->beginTransaction();

            $stmt = $db->prepare('SELECT r.* FROM reservations r WHERE r.id = ?');
            $stmt->execute([$id]);
            $res = $stmt->fetch();

            if (!$res || $res['status'] !== 'paid') {
                throw new Exception('Only paid reservations can be finalized.');
            }

            $stmtComplete = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
            $stmtComplete->execute(['completed', $id]);

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }

        $this->redirect('/reservations');
    }

    public function pay() {
        $db = getDB();
        $id = $_POST['reservation_id'] ?? null;
        $payment_method = $_POST['payment_method'] ?? null;
        $user_id = $_SESSION['user_id'];

        try {
            if (!$id) {
                throw new Exception('Reservation ID is required.');
            }
            if (!in_array($payment_method, ['cash', 'gcash', 'paymaya', 'card', 'bdo', 'bpi', 'unionbank', 'maribank', 'other_bank'], true)) {
                throw new Exception('Invalid payment method.');
            }

            $db->beginTransaction();

            $stmt = $db->prepare(
                'SELECT r.* FROM reservations r WHERE r.id = ? AND r.status IN (\'reserved\', \'pending\')'
            );
            $stmt->execute([$id]);
            $res = $stmt->fetch();

            if (!$res) {
                throw new Exception('Reservation not found or already processed.');
            }

            $final_price = $res['price'];

            $salesColumns = $db->query("SHOW COLUMNS FROM sales LIKE 'status'")->fetch();
            if ($salesColumns) {
                $stmtSale = $db->prepare('INSERT INTO sales (user_id, total_amount, payment_method, status) VALUES (?, ?, ?, ?)');
                $stmtSale->execute([$user_id, $final_price, $payment_method, 'paid']);
            } else {
                $stmtSale = $db->prepare('INSERT INTO sales (user_id, total_amount, payment_method) VALUES (?, ?, ?)');
                $stmtSale->execute([$user_id, $final_price, $payment_method]);
            }

            $stmtRes = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
            $stmtRes->execute(['paid', $id]);

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            die($e->getMessage());
        }

        $this->redirect('/reservations');
    }

    public function cancel() {
        $db = getDB();
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $this->redirect('/reservations');
        }

        $stmt = $db->prepare('SELECT category_id, status FROM reservations WHERE id = ?');
        $stmt->execute([$id]);
        $res = $stmt->fetch();

        if ($res && in_array($res['status'], ['reserved', 'pending'], true)) {
            $db->beginTransaction();
            $stmtCancel = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
            $stmtCancel->execute(['cancelled', $id]);
            if ($res['category_id']) {
                $stmtUpdate = $db->prepare('UPDATE rack_categories SET stock_available = stock_available + 1 WHERE id = ?');
                $stmtUpdate->execute([$res['category_id']]);
            }
            $db->commit();
        }

        $this->redirect('/reservations');
    }
}
