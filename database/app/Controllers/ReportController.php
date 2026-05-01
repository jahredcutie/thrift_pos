<?php

class ReportController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        
        // Sales Report (Daily for last 7 days)
        $dailySales = $db->query("
            SELECT DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count 
            FROM sales 
            GROUP BY DATE(created_at) 
            ORDER BY date DESC 
            LIMIT 7
        ")->fetchAll();

        // Get all recent sales (for orders list)
        $allSales = $db->query("
            SELECT s.*, u.username 
            FROM sales s 
            JOIN users u ON s.user_id = u.id 
            ORDER BY s.created_at DESC
        ")->fetchAll();

        // Staff Performance
        $staffPerformance = $db->query("
            SELECT u.username, COUNT(s.id) as sales_count, SUM(s.total_amount) as total_revenue
            FROM users u
            LEFT JOIN sales s ON u.id = s.user_id
            GROUP BY u.id
        ")->fetchAll();

        // Inventory Status
        $inventoryStatus = $db->query("
            SELECT status, COUNT(*) as count
            FROM items
            GROUP BY status
        ")->fetchAll();

        $this->view('admin/reports', [
            'dailySales' => $dailySales,
            'staffPerformance' => $staffPerformance,
            'inventoryStatus' => $inventoryStatus,
            'allSales' => $allSales
        ]);
    }
}
