<?php

class SalesHistoryController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        
        // Get all recent sales
        $allSales = $db->query("
            SELECT s.*, u.username 
            FROM sales s 
            JOIN users u ON s.user_id = u.id 
            ORDER BY s.created_at DESC
        ")->fetchAll();

        $this->view('admin/sales_history', [
            'allSales' => $allSales
        ]);
    }
}
