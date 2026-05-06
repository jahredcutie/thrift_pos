<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();

$items = $db->query("SELECT id, name, category, gender, status FROM items ORDER BY id ASC")->fetchAll();
echo "Total items: " . count($items) . "\n";
echo "======================================\n";
foreach ($items as $item) {
    echo "ID: {$item['id']} | Name: {$item['name']} | Category: {$item['category']} | Gender: {$item['gender']} | Status: {$item['status']}\n";
}
