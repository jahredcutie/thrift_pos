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

        // Date filter
        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        
        $dailyEarningsStmt = $db->prepare("
            SELECT SUM(total_amount) as total 
            FROM sales 
            WHERE DATE(created_at) = ?
        ");
        $dailyEarningsStmt->execute([$selectedDate]);
        $dailyEarnings = $dailyEarningsStmt->fetch()['total'] ?? 0;

        $dailyTransactionsStmt = $db->prepare("
            SELECT * 
            FROM sales 
            WHERE DATE(created_at) = ?
            ORDER BY created_at DESC
        ");
        $dailyTransactionsStmt->execute([$selectedDate]);
        $dailyTransactions = $dailyTransactionsStmt->fetchAll();

        $this->view('admin/reports', [
            'dailySales' => $dailySales,
            'selectedDate' => $selectedDate,
            'dailyEarnings' => $dailyEarnings,
            'dailyTransactions' => $dailyTransactions
        ]);
    }
}
