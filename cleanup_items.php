<?php
require_once __DIR__ . '/config/database.php';

$db = getDB();

// Get total items count
$countStmt = $db->query("SELECT COUNT(*) FROM items");
$totalItems = (int)$countStmt->fetchColumn();

echo "Total items before cleanup: " . $totalItems . "\n";

if ($totalItems > 220) {
    // Keep the first 220 items (ordered by id to be deterministic)
    $keepIds = $db->query("SELECT id FROM items ORDER BY id ASC LIMIT 220")->fetchAll(PDO::FETCH_COLUMN);
    $keepIdsStr = implode(',', array_map('intval', $keepIds));
    
    // First, get all sale_ids that have items we're going to delete
    $saleIdsToDelete = $db->query("SELECT DISTINCT sale_id FROM sale_items WHERE item_id NOT IN ($keepIdsStr)")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($saleIdsToDelete)) {
        $saleIdsStr = implode(',', array_map('intval', $saleIdsToDelete));
        
        // Delete sale_items first (foreign key constraint)
        $db->exec("DELETE FROM sale_items WHERE sale_id IN ($saleIdsStr)");
        echo "Deleted sale items for affected sales\n";
        
        // Delete sales
        $db->exec("DELETE FROM sales WHERE id IN ($saleIdsStr)");
        echo "Deleted affected sales\n";
    }
    
    // Delete any remaining sale_items that reference items we're deleting
    $db->exec("DELETE FROM sale_items WHERE item_id NOT IN ($keepIdsStr)");
    echo "Deleted remaining sale items referencing items to delete\n";
    
    // Finally, delete the extra items
    $deleteStmt = $db->exec("DELETE FROM items WHERE id NOT IN ($keepIdsStr)");
    echo "Deleted " . $deleteStmt . " extra items!\n";
} else {
    echo "No cleanup needed! Total items: " . $totalItems . "\n";
}

// Verify the count
$newCountStmt = $db->query("SELECT COUNT(*) FROM items");
$newTotalItems = (int)$newCountStmt->fetchColumn();
echo "Total items after cleanup: " . $newTotalItems . "\n";
