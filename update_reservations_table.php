<?php
require_once __DIR__ . '/config/database.php';

$db = getDB();

try {
    $stmt = $db->query("SHOW COLUMNS FROM reservations LIKE 'category_id'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN category_id INT NULL AFTER item_id");
    }
    echo "Reservations table updated successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
