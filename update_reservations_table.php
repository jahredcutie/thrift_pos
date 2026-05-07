<?php
require_once __DIR__ . '/config/database.php';

$db = getDB();

try {
    $stmt = $db->query("SHOW COLUMNS FROM reservations LIKE 'category_id'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN category_id INT NULL AFTER item_id");
    }
    $stmtQty = $db->query("SHOW COLUMNS FROM reservations LIKE 'quantity'");
    if (!$stmtQty->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN quantity INT NOT NULL DEFAULT 1 AFTER contact_number");
    }
    echo "Reservations table updated successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
