<?php

class ReturnController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        // Get last 20 sales for return selection
        $sales = $db->query("
            SELECT s.*, u.username 
            FROM sales s 
            JOIN users u ON s.user_id = u.id 
            ORDER BY s.created_at DESC 
            LIMIT 20
        ")->fetchAll();
        
        $this->view('pos/returns', ['sales' => $sales]);
    }

    public function getSaleItems($id) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT si.*, i.name, i.image_url 
            FROM sale_items si 
            LEFT JOIN items i ON si.item_id = i.id 
            WHERE si.sale_id = ?
        ");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();
        $this->json($items);
    }

    public function process() {
        $db = getDB();
        
        try {
            $db->beginTransaction();
            
            if (isset($_POST['return_all_sales']) && $_POST['return_all_sales'] === '1') {
                // Return ALL items from ALL sales
                // Get all items from all sales
                $stmt = $db->query("SELECT item_id FROM sale_items");
                $items = $stmt->fetchAll();
                
                // Restore all items to available
                foreach ($items as $item) {
                    $stmt1 = $db->prepare("UPDATE items SET status = 'available' WHERE id = ?");
                    $stmt1->execute([$item['item_id']]);
                }
                
                // Delete ALL sale items records
                $stmt2 = $db->query("DELETE FROM sale_items");
                
            } elseif (isset($_POST['return_all']) && $_POST['return_all'] === '1' && isset($_POST['sale_id'])) {
                // Return all items from a single sale
                $saleId = $_POST['sale_id'];
                
                // Get all items from this sale first
                $stmt = $db->prepare("SELECT item_id FROM sale_items WHERE sale_id = ?");
                $stmt->execute([$saleId]);
                $items = $stmt->fetchAll();
                
                // Restore all items to available
                foreach ($items as $item) {
                    $stmt1 = $db->prepare("UPDATE items SET status = 'available' WHERE id = ?");
                    $stmt1->execute([$item['item_id']]);
                }
                
                // Delete all sale items records
                $stmt2 = $db->prepare("DELETE FROM sale_items WHERE sale_id = ?");
                $stmt2->execute([$saleId]);
                
            } else {
                // Return single item
                $itemId = $_POST['item_id'];
                $saleItemId = $_POST['sale_item_id'];
                
                // 1. Restore item status to 'available'
                $stmt1 = $db->prepare("UPDATE items SET status = 'available' WHERE id = ?");
                $stmt1->execute([$itemId]);
                
                // 2. Remove from sale_items
                $stmt2 = $db->prepare("DELETE FROM sale_items WHERE id = ?");
                $stmt2->execute([$saleItemId]);
            }
            
            $db->commit();
            $this->redirect('/returns');
        } catch (Exception $e) {
            $db->rollBack();
            die($e->getMessage());
        }
    }
}
