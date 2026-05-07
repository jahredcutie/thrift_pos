<?php
require_once __DIR__ . '/../Models/Item.php';

class DashboardController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        
        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        
        // Sales Stats
        $salesOnDateStmt = $db->prepare("SELECT SUM(total_amount) as total FROM sales WHERE DATE(created_at) = ?");
        $salesOnDateStmt->execute([$selectedDate]);
        $salesOnDate = $salesOnDateStmt->fetch();
        
        $totalSales = $db->query("SELECT SUM(total_amount) as total FROM sales")->fetch();
        
        // Accurate Inventory Counts from the same source of truth
        $inventoryCounts = $db->query("
            SELECT 
                COUNT(CASE WHEN status = 'available' THEN 1 END) as available,
                COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold,
                COUNT(CASE WHEN status = 'reserved' THEN 1 END) as reserved,
                COUNT(*) as total
            FROM items
        ")->fetch();
        
        // Recent Sales (latest 5 from Sales History)
        $recentSalesStmt = $db->query("SELECT s.*, u.username FROM sales s JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC LIMIT 5");
        $recentSales = $recentSalesStmt->fetchAll();
        
        // Sales by Category (all time)
        $salesByCategory = $db->query("SELECT i.category, COUNT(si.id) as count FROM sale_items si JOIN items i ON si.item_id = i.id GROUP BY i.category")->fetchAll();
        
        // Earnings Calendar Data (daily sales for selected month)
        $currentMonth = date('Y-m', strtotime($selectedDate));
        $earningsCalendarStmt = $db->prepare("
            SELECT DATE(created_at) as date, SUM(total_amount) as total 
            FROM sales 
            WHERE DATE(created_at) LIKE ? 
            GROUP BY DATE(created_at)
        ");
        $earningsCalendarStmt->execute([$currentMonth . '%']);
        $earningsCalendarData = $earningsCalendarStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Rack Stock Overview - real time rack category stock totals
        require_once __DIR__ . '/../Models/RackCategory.php';
        $rackCategoryModel = new RackCategory();
        $rackCategories = $rackCategoryModel->getAll();

        $totalRackAvailable = 0;
        $totalRackStock = 0;
        foreach ($rackCategories as &$cat) {
            $totalRackAvailable += $cat['stock_available'];
            $totalRackStock += $cat['stock_total'];
        }

        $this->view('admin/dashboard', [
            'stats' => [
                'selected_date' => $selectedDate,
                'sales_on_date' => $salesOnDate['total'] ?? 0,
                'total' => $totalSales['total'] ?? 0,
                'items_sold' => $inventoryCounts['sold'] ?? 0,
                'available' => $totalRackAvailable,
                'reserved' => $inventoryCounts['reserved'] ?? 0,
                'total_items' => $totalRackStock
            ],
            'recentSales' => $recentSales,
            'categoryStats' => $salesByCategory,
            'earningsCalendarData' => $earningsCalendarData,
            'rackCategories' => $rackCategories
        ]);
    }
}
