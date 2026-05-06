<?php
require_once __DIR__ . '/config/database.php';

$db = getDB();

try {
    $db->beginTransaction();

    // 1. Add stock columns to rack_categories
    $stmt = $db->query("SHOW COLUMNS FROM rack_categories LIKE 'stock_total'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE rack_categories ADD COLUMN stock_total INT NOT NULL DEFAULT 10 AFTER price_tiers");
    }

    $stmt = $db->query("SHOW COLUMNS FROM rack_categories LIKE 'stock_available'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE rack_categories ADD COLUMN stock_available INT NOT NULL DEFAULT 10 AFTER stock_total");
    }

    // 2. Add category_id and price to reservations table
    $stmt = $db->query("SHOW COLUMNS FROM reservations LIKE 'category_id'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN category_id INT NULL AFTER item_id");
    }

    $stmt = $db->query("SHOW COLUMNS FROM reservations LIKE 'price'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN price DECIMAL(10,2) NULL AFTER category_id");
    }

    // 3. Update existing rack categories to have 10 stock
    $db->exec("UPDATE rack_categories SET stock_total = 10, stock_available = 10 WHERE stock_total = 0 OR stock_total IS NULL");

    $db->commit();
    echo "Migration completed successfully!";
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
