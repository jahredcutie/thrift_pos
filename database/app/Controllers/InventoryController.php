<?php
require_once __DIR__ . '/../Models/Item.php';

class InventoryController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    public function index() {
        $itemModel = new Item();
        $items = $itemModel->getAll();
        $categories = $itemModel->getCategories();
        
        require_once __DIR__ . '/../Models/RackCategory.php';
        $rackCategoryModel = new RackCategory();
        $rackCategories = $rackCategoryModel->getAll();
        
        $this->view('admin/inventory', ['items' => $items, 'categories' => $categories, 'rackCategories' => $rackCategories]);
    }

    public function add() {
        $image_url = 'https://picsum.photos/400/400?random=' . rand(1, 1000);
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../public/assets/images/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = '/thrift_pos/assets/images/' . $file_name;
            }
        }

        $db = getDB();
        $stmt = $db->prepare("INSERT INTO items (name, category, gender, price, tag_color, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['gender'],
            $_POST['price'],
            $_POST['tag_color'],
            $image_url,
            'available'
        ]);
        $this->redirect('/inventory');
    }

    public function update() {
        $db = getDB();
        
        $image_sql = "";
        $params = [
            $_POST['name'],
            $_POST['category'],
            $_POST['gender'],
            $_POST['price'],
            $_POST['tag_color'],
            $_POST['status']
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../public/assets/images/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = '/thrift_pos/assets/images/' . $file_name;
                $image_sql = ", image_url = ?";
                $params[] = $image_url;
            }
        }

        $params[] = $_POST['id'];
        $stmt = $db->prepare("UPDATE items SET name = ?, category = ?, gender = ?, price = ?, tag_color = ?, status = ? $image_sql WHERE id = ?");
        $stmt->execute($params);
        $this->redirect('/inventory');
    }

    public function delete() {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $this->redirect('/inventory');
    }

    public function addBulk() {
        $category = $_POST['category'];
        $gender = $_POST['gender'];
        $quantity = (int)$_POST['quantity'];
        $tag_color = $_POST['tag_color'] ?? 'yellow';
        $batch_name = $_POST['batch_name'] ?? '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : null;

        if ($quantity <= 0 || $quantity > 1000) {
            $this->redirect('/inventory');
            return;
        }

        $db = getDB();
        
        // Get rack category
        require_once __DIR__ . '/../Models/RackCategory.php';
        $rackCategoryModel = new RackCategory();
        $cat = $rackCategoryModel->findByName($category);
        
        if (!$price && $cat) {
            $price = $cat['price'];
        }

        $itemModel = new Item();
        
        for ($i = 0; $i < $quantity; $i++) {
            // Generate unique name
            $name = $this->generateItemName($category, $gender, $batch_name, $i);
            
            $stmt = $db->prepare("INSERT INTO items (name, category, gender, price, tag_color, status, batch_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $name,
                $category,
                $gender,
                $price,
                $tag_color,
                'available',
                $batch_name ?: null
            ]);
        }
        
        // Update rack stock if we found the category
        if ($cat) {
            $stmtUpdateStock = $db->prepare('UPDATE rack_categories SET stock_total = stock_total + ?, stock_available = stock_available + ? WHERE id = ?');
            $stmtUpdateStock->execute([$quantity, $quantity, $cat['id']]);
        }
        
        $this->redirect('/inventory');
    }

    private function generateItemName($category, $gender, $batch_name = '', $index = 0) {
        $itemModel = new Item();
        $names = $itemModel->getGeneratedNamesForGender($gender);
        
        if (isset($names[$category])) {
            $categoryNames = $names[$category];
            $name = $categoryNames[array_rand($categoryNames)];
        } else {
            $name = ucfirst($category) . ' Item';
        }
        
        if ($batch_name) {
            $name .= ' (' . $batch_name . ')';
        }
        
        // Ensure uniqueness
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM items WHERE name = ?");
        $stmt->execute([$name]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            $name .= ' ' . ($count + 1);
        }
        
        return $name;
    }
}
