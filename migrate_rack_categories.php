<?php
require_once __DIR__ . '/config/database.php';

$db = getDB();

try {
    // Check if price_tiers column exists
    $stmt = $db->query("SHOW COLUMNS FROM rack_categories LIKE 'price_tiers'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE rack_categories ADD COLUMN price_tiers JSON NULL AFTER price");
    }

    // Check if subcategory column exists
    $stmt = $db->query("SHOW COLUMNS FROM rack_categories LIKE 'subcategory'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE rack_categories ADD COLUMN subcategory VARCHAR(50) NULL AFTER name");
    }

    // Drop unique constraint on name (since men/women can have same category names)
    $checkIndex = $db->query("SHOW INDEX FROM rack_categories WHERE Key_name = 'name'");
    if ($checkIndex->fetch()) {
        $db->exec("ALTER TABLE rack_categories DROP INDEX name");
    }

    // Clear existing rack categories
    $db->exec("TRUNCATE TABLE rack_categories");

    // Now start transaction for inserting data
    $db->beginTransaction();

    // Define new rack categories with price tiers
    $rackCategories = [
        // MEN SECTION
        ['name' => 'Plain T-Shirt', 'subcategory' => 'TOPS', 'gender' => 'men', 'price_tiers' => [100, 120, 150]],
        ['name' => 'Printed T-Shirt', 'subcategory' => 'TOPS', 'gender' => 'men', 'price_tiers' => [120, 150, 180]],
        ['name' => 'Polo Shirt', 'subcategory' => 'TOPS', 'gender' => 'men', 'price_tiers' => [150, 200, 300]],
        ['name' => 'Long Sleeve Plain', 'subcategory' => 'TOPS', 'gender' => 'men', 'price_tiers' => [150, 200, 300]],
        ['name' => 'Long Sleeve Printed', 'subcategory' => 'TOPS', 'gender' => 'men', 'price_tiers' => [180, 250, 350]],
        ['name' => 'Jeans', 'subcategory' => 'BOTTOMS', 'gender' => 'men', 'price_tiers' => [200, 300, 400]],
        ['name' => 'Cargo Pants', 'subcategory' => 'BOTTOMS', 'gender' => 'men', 'price_tiers' => [250, 350, 500]],
        ['name' => 'Jogger Pants', 'subcategory' => 'BOTTOMS', 'gender' => 'men', 'price_tiers' => [200, 300, 400]],
        ['name' => 'Shorts', 'subcategory' => 'BOTTOMS', 'gender' => 'men', 'price_tiers' => [120, 150, 200]],
        ['name' => 'Jackets', 'subcategory' => 'OUTERWEAR', 'gender' => 'men', 'price_tiers' => [300, 500, 800]],
        ['name' => 'Hoodies', 'subcategory' => 'OUTERWEAR', 'gender' => 'men', 'price_tiers' => [300, 400, 600]],
        ['name' => 'Windbreakers', 'subcategory' => 'OUTERWEAR', 'gender' => 'men', 'price_tiers' => [200, 300, 400]],
        ['name' => 'Casual Shoes', 'subcategory' => 'FOOTWEAR', 'gender' => 'men', 'price_tiers' => [300, 400, 500]],
        ['name' => 'Branded Shoes', 'subcategory' => 'FOOTWEAR', 'gender' => 'men', 'price_tiers' => [600, 800, 1000, 1200]],
        ['name' => 'Slippers', 'subcategory' => 'FOOTWEAR', 'gender' => 'men', 'price_tiers' => [100, 150, 200]],
        ['name' => 'Caps', 'subcategory' => 'ACCESSORIES', 'gender' => 'men', 'price_tiers' => [100, 150, 200]],
        ['name' => 'Belts', 'subcategory' => 'ACCESSORIES', 'gender' => 'men', 'price_tiers' => [100, 150, 200]],
        ['name' => 'Bags', 'subcategory' => 'ACCESSORIES', 'gender' => 'men', 'price_tiers' => [200, 300, 500]],

        // WOMEN SECTION
        ['name' => 'Plain T-Shirt', 'subcategory' => 'TOPS', 'gender' => 'women', 'price_tiers' => [100, 120, 150]],
        ['name' => 'Printed T-Shirt', 'subcategory' => 'TOPS', 'gender' => 'women', 'price_tiers' => [120, 150, 180]],
        ['name' => 'Crop Tops', 'subcategory' => 'TOPS', 'gender' => 'women', 'price_tiers' => [100, 120, 150]],
        ['name' => 'Blouses', 'subcategory' => 'TOPS', 'gender' => 'women', 'price_tiers' => [150, 200, 300]],
        ['name' => 'Long Sleeve Plain', 'subcategory' => 'TOPS', 'gender' => 'women', 'price_tiers' => [150, 200, 300]],
        ['name' => 'Long Sleeve Printed', 'subcategory' => 'TOPS', 'gender' => 'women', 'price_tiers' => [180, 250, 350]],
        ['name' => 'Plain Dress', 'subcategory' => 'DRESSES', 'gender' => 'women', 'price_tiers' => [150, 200, 300]],
        ['name' => 'Printed Dress', 'subcategory' => 'DRESSES', 'gender' => 'women', 'price_tiers' => [200, 300, 500]],
        ['name' => 'Party Dress', 'subcategory' => 'DRESSES', 'gender' => 'women', 'price_tiers' => [300, 500, 800]],
        ['name' => 'Jeans', 'subcategory' => 'BOTTOMS', 'gender' => 'women', 'price_tiers' => [200, 300, 400]],
        ['name' => 'Skirts', 'subcategory' => 'BOTTOMS', 'gender' => 'women', 'price_tiers' => [150, 200, 300]],
        ['name' => 'Shorts', 'subcategory' => 'BOTTOMS', 'gender' => 'women', 'price_tiers' => [120, 150, 200]],
        ['name' => 'Leggings', 'subcategory' => 'BOTTOMS', 'gender' => 'women', 'price_tiers' => [150, 180, 250]],
        ['name' => 'Jackets', 'subcategory' => 'OUTERWEAR', 'gender' => 'women', 'price_tiers' => [300, 500, 800]],
        ['name' => 'Cardigans', 'subcategory' => 'OUTERWEAR', 'gender' => 'women', 'price_tiers' => [200, 300, 400]],
        ['name' => 'Blazers', 'subcategory' => 'OUTERWEAR', 'gender' => 'women', 'price_tiers' => [300, 500, 800]],
        ['name' => 'Heels', 'subcategory' => 'FOOTWEAR', 'gender' => 'women', 'price_tiers' => [300, 400, 600]],
        ['name' => 'Branded Shoes', 'subcategory' => 'FOOTWEAR', 'gender' => 'women', 'price_tiers' => [600, 800, 1000, 1200]],
        ['name' => 'Sandals', 'subcategory' => 'FOOTWEAR', 'gender' => 'women', 'price_tiers' => [150, 200, 300]],
        ['name' => 'Bags', 'subcategory' => 'ACCESSORIES', 'gender' => 'women', 'price_tiers' => [200, 300, 500]],
        ['name' => 'Jewelry', 'subcategory' => 'ACCESSORIES', 'gender' => 'women', 'price_tiers' => [100, 150, 300]],
        ['name' => 'Hair Accessories', 'subcategory' => 'ACCESSORIES', 'gender' => 'women', 'price_tiers' => [50, 100, 150]],
    ];

    $stmt = $db->prepare("INSERT INTO rack_categories (name, subcategory, gender, price, price_tiers) VALUES (?, ?, ?, ?, ?)");
    foreach ($rackCategories as $cat) {
        $stmt->execute([
            $cat['name'],
            $cat['subcategory'],
            $cat['gender'],
            $cat['price_tiers'][0], // default price is first tier
            json_encode($cat['price_tiers'])
        ]);
    }

    $db->commit();
    echo "Migration completed successfully!";

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
