<?php
require_once __DIR__ . '/config/database.php';

$db = getDB();

// Get the name lists from Item model (we'll copy them here since it's a standalone script)
$womenGeneratedNames = [
    'Tops' => [
        'Uniqlo Women Airism Cotton Tee',
        'H&M Ribbed Scoop Neck Top',
        'Zara Floral Print Blouse',
        'Gucci GG Logo Crop Top',
        'Louis Vuitton Monogram Tank Top',
        'Nike Women Sports Tank Top',
        'Adidas Women Essentials Tee',
        'Uniqlo Satin V-Neck Blouse',
        'Zara Puff Sleeve Shirt',
        'H&M Draped Wrap Top',
        'Gucci Logo Jersey Tee',
        'Louis Vuitton Cotton Crew Tee',
        'Nike Dri-FIT Muscle Tank',
        'Adidas Originals Three Stripe Tee',
        'Uniqlo Linen Blend Shirt',
        'Zara Ruffled Crop Top',
        'H&M Lace Trim Camisole',
        'Gucci Signature Polo Shirt',
        'Louis Vuitton Silk Blouse',
        'Zara Satin Cami Top'
    ],
    'Dresses' => [
        'H&M Floral Wrap Midi Dress',
        'Zara High-Waist Mini Dress',
        'Gucci Monogram Mini Dress',
        'Louis Vuitton Monogram Mini Dress',
        'Uniqlo Shirred Waist Dress',
        'Zara Satin Slip Dress',
        'H&M Smocked Maxi Dress',
        'Nike Women Sports Dress',
        'Adidas Originals Tee Dress',
        'Uniqlo Linen Shirt Dress',
        'Zara Tiered Midi Dress',
        'H&M Puff Sleeve Dress',
        'Gucci GG Floral Dress',
        'Louis Vuitton Velvet Wrap Dress',
        'Zara Ruffled Midi Dress',
        'H&M Polka Dot Dress',
        'Uniqlo V-Neck Jersey Dress',
        'Zara Structured Fit Dress',
        'H&M Satin Slip Dress',
        'Nike Women Racerback Dress'
    ],
    'Bottoms' => [
        'Zara High-Waist Skinny Jeans',
        'H&M Wide Leg Trousers',
        'Uniqlo High-Rise Straight Jeans',
        'Gucci Logo Denim Skirt',
        'Louis Vuitton Monogram Denim Skirt',
        'Adidas Women 3-Stripes Leggings',
        'Nike Dri-FIT Crop Pants',
        'Zara Pleated Midi Skirt',
        'H&M Slim Fit Chinos',
        'Uniqlo Ankle Length Jeans',
        'Gucci Leather Leggings',
        'Louis Vuitton Tailored Trousers',
        'Adidas Essentials Shorts',
        'Nike Women Training Shorts',
        'Zara Tailored Culottes',
        'H&M Stretch Skinny Jeans',
        'Uniqlo Relaxed Fit Jeans',
        'Zara Denim Shorts',
        'H&M Linen Blend Pants',
        'Adidas Tech Fleece Pants'
    ],
    'Outerwear' => [
        'Zara Quilted Bomber Jacket',
        'H&M Faux Leather Jacket',
        'Uniqlo Hybrid Down Coat',
        'Gucci GG Wool Coat',
        'Louis Vuitton Oversized Trench',
        'Nike Women Windrunner Jacket',
        'Adidas Originals Track Jacket',
        'Zara Double-Breasted Blazer',
        'H&M Padded Anorak',
        'Uniqlo Seamless Down Parka',
        'Gucci Logo Denim Jacket',
        'Louis Vuitton Cropped Leather Jacket',
        'Nike Women Fleece Hoodie',
        'Adidas Essentials Hoodie',
        'Zara Cropped Puffer Jacket',
        'H&M Sherpa Lined Jacket',
        'Uniqlo Blocktech Coat',
        'Zara Wool Blend Coat',
        'H&M Utility Jacket',
        'Nike Women Training Jacket'
    ],
    'Footwear' => [
        'Nike Women Air Max 90',
        'Adidas Women Gazelle Sneakers',
        'Converse Chuck Taylor Platform',
        'Vans Old Skool Platform',
        'Puma Suede Platform',
        'New Balance 574 Sneaker',
        'Nike Women React Float',
        'Adidas Superstar Platform',
        'Zara Leather Ankle Boots',
        'H&M Strappy Sandals',
        'Gucci Signature Slides',
        'Louis Vuitton Monogram Sneakers',
        'Nike Court Vision Low',
        'Adidas Cloudfoam Shoes',
        'Zara Square Toe Mules',
        'H&M Chunky Trainers',
        'Uniqlo Slip-On Sneakers',
        'Zara Ballet Flats',
        'H&M Platform Sandals',
        'Adidas NMD_R1'
    ],
    'Accessories' => [
        'Gucci GG Leather Belt',
        'Louis Vuitton Monogram Scarf',
        'Zara Chain Necklace',
        'H&M Bucket Hat',
        'Uniqlo Cotton Beanie',
        'Nike Women Sports Cap',
        'Adidas Trefoil Cap',
        'Zara Faux Leather Belt',
        'H&M Gold Hoop Earrings',
        'Gucci Logo Sunglasses',
        'Louis Vuitton Wallet',
        'Zara Minimalist Tote',
        'H&M Leather Cardholder',
        'Uniqlo Headband',
        'Adidas Performance Socks',
        'Nike Everyday Backpack',
        'Zara Pearl Drop Earrings',
        'H&M Hair Scrunchies',
        'Uniqlo UV Protection Hat',
        'Gucci Silk Scarf'
    ]
];

$menGeneratedNames = [
    'Tops' => [
        'Uniqlo Men Supima Cotton Tee',
        'H&M Relaxed Fit T-Shirt',
        'Zara Basic Polo Shirt',
        'Nike Sportswear Club Tee',
        'Adidas Essentials Tee',
        'Uniqlo Dry Stretch Polo',
        'Zara Linen Blend Shirt',
        'H&M Graphic T-Shirt',
        'Nike Dri-FIT Training Tee',
        'Adidas Originals Trefoil Tee',
        'Uniqlo Oxford Shirt',
        'H&M Slim Fit Shirt',
        'Zara Textured Knit Top',
        'Nike Club Fleece Sweatshirt',
        'Adidas 3-Stripes Hoodie',
        'Uniqlo Blocktech Shirt',
        'H&M Long Sleeve Henley',
        'Zara Workwear Shirt',
        'Nike Dry-FIT Polo',
        'Adidas Essentials Hoodie'
    ],
    'Bottoms' => [
        'Uniqlo Slim Fit Chinos',
        'Levi\'s 501 Original Jeans',
        'Zara Relaxed Fit Jeans',
        'H&M Denim Shorts',
        'Nike Sportswear Sweatpants',
        'Adidas Essentials Track Pants',
        'Uniqlo Easy Cargo Pants',
        'Zara Tailored Trousers',
        'H&M Straight Leg Jeans',
        'Nike Dri-FIT Shorts',
        'Adidas Tiro 23 Pants',
        'Uniqlo Smart Ankle Pants',
        'Zara Pleated Chinos',
        'H&M Jogger Pants',
        'Nike Flex Shorts',
        'Adidas Essentials Shorts',
        'Uniqlo Workwear Pants',
        'Zara Denim Pants',
        'H&M Linen Blend Pants',
        'Nike Court Shorts'
    ],
    'Outerwear' => [
        'Nike Windrunner Jacket',
        'Adidas Essentials Track Jacket',
        'Uniqlo Ultra Light Down Jacket',
        'Zara Biker Jacket',
        'H&M Padded Bomber Jacket',
        'Levi\'s Trucker Jacket',
        'Nike Therma-FIT Hoodie',
        'Adidas Originals Coach Jacket',
        'Uniqlo Seamless Down Parka',
        'Zara Double Breasted Blazer',
        'H&M Utility Jacket',
        'Nike Club Fleece Jacket',
        'Adidas Badge of Sport Hoodie',
        'Uniqlo Denim Jacket',
        'Zara Wool Blend Coat',
        'H&M Faux Leather Jacket',
        'Nike Dri-FIT Training Jacket',
        'Adidas Essentials Hoodie',
        'Uniqlo Blocktech Coat',
        'Zara Varsity Jacket'
    ],
    'Footwear' => [
        'Nike Air Force 1 Low',
        'Adidas Stan Smith',
        'Converse Chuck Taylor Classic',
        'Vans Old Skool',
        'Puma Suede Classic',
        'New Balance 574',
        'Nike Jordan 1 Low',
        'Adidas Superstar',
        'Vans Sk8-Hi',
        'Nike Court Vision',
        'Adidas Gazelle',
        'Converse Run Star',
        'Puma Cali Sneakers',
        'Nike Air Max 90',
        'Adidas Cloudfoam',
        'Vans Era',
        'New Balance 327',
        'Nike SB Charge',
        'Adidas NMD_R1',
        'Converse One Star'
    ],
    'Accessories' => [
        'Nike Heritage Cap',
        'Adidas Trefoil Cap',
        'Uniqlo Cotton Cap',
        'H&M Leather Belt',
        'Zara Chain Wallet',
        'Gucci Logo Belt',
        'Louis Vuitton Card Holder',
        'Nike Sports Socks',
        'Adidas Performance Socks',
        'Uniqlo Knit Beanie',
        'H&M Wool Scarf',
        'Zara Leather Gloves',
        'Nike Duffel Bag',
        'Adidas Training Backpack',
        'Uniqlo Headband',
        'Zara Baseball Cap',
        'H&M Sunglasses',
        'Nike Phone Pouch',
        'Adidas Wristband',
        'Uniqlo Travel Pouch'
    ]
];

$imageMapping = [
    'Uniqlo Basic Tee White' => 'tshirt1.jpg',
    'Uniqlo Basic Tee Black' => 'tshirt2.jpg',
    'Nike Sportswear Essential Tee' => 'tshirt3.jpg',
    'Adidas Originals Trefoil Black' => 'tshirt4.jpg',
    'Vintage Champion Grey Heather' => 'tshirt5.jpg',
    'Stussy 8-Ball Graphic Tee' => 'tshirt6.jpg',
    'Polo Ralph Lauren Classic Fit' => 'tshirt7.jpg',
    'Vintage Harley Davidson Eagle' => 'tshirt8.jpg',
    'H&M Relaxed Fit Cotton Tee' => 'tshirt9.jpg',
    'Supreme Motion Logo White' => 'tshirt10.jpg',
    'Levi\'s 501 Original Blue Jeans' => 'pants1.jpg',
    'Dickies 874 Khaki Work Pants' => 'pants2.jpg',
    'Vintage Carhartt Double Knee' => 'pants3.jpg',
    'Wrangler Cowboy Cut Denim' => 'pants4.jpg',
    'Uniqlo Selvedge Slim Fit' => 'pants5.jpg',
    'Zara Straight Fit Chinos' => 'pants6.jpg',
    'Vintage Military Cargo Pants' => 'pants7.jpg',
    'H&M Corduroy Loose Fit' => 'pants8.jpg',
    'Lee Rider Straight Jeans' => 'pants9.jpg',
    'Gap Standard Fit Khakis' => 'pants10.jpg',
    'Vintage Denim Jacket' => 'jacket1.jpg',
    'Nike Hoodie Black' => 'jacket2.jpg',
    'Vintage Levi\'s Trucker Jacket' => 'jacket3.jpg',
    'The North Face Nuptse 1996' => 'jacket4.jpg',
    'Vintage Carhartt Detroit Jacket' => 'jacket5.jpg',
    'Bomber Jacket Black' => 'jacket6.jpg',
    'Windbreaker Sport Jacket' => 'jacket7.jpg',
    'Vintage Varsity Jacket' => 'jacket8.jpg',
    'Harrington Casual Jacket' => 'jacket9.jpg',
    'Puffer Jacket Navy' => 'jacket10.jpg',
    'Nike Air Force 1 Low White' => 'shoes1.jpg',
    'Adidas Stan Smith Classic' => 'shoes2.jpg',
    'Converse Chuck Taylor High' => 'shoes3.jpg',
    'Vans Old Skool Black/White' => 'shoes4.jpg',
    'New Balance 574 Heritage' => 'shoes5.jpg',
    'Nike Jordan 1 Retro' => 'shoes6.jpg',
    'Adidas Superstar Original' => 'shoes7.jpg',
    'Puma Suede Classic' => 'shoes8.jpg',
    'Reebok Club C 85' => 'shoes9.jpg',
    'Vans Sk8-Hi Classic' => 'shoes10.jpg'
];

$tagColors = ['red', 'blue', 'green', 'yellow'];

// Clear existing items and related data
$db->exec("DELETE FROM reservations");
$db->exec("DELETE FROM sale_items");
$db->exec("DELETE FROM sales");
$db->exec("DELETE FROM items");
$db->exec("ALTER TABLE items AUTO_INCREMENT = 1");

// Prepare insert statement
$insert = $db->prepare("INSERT INTO items (name, category, gender, price, tag_color, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)");

// Generate women's items (120)
$womenCount = 0;
foreach ($womenGeneratedNames as $category => $names) {
    foreach ($names as $name) {
        if ($womenCount >= 120) break 2;
        $price = mt_rand(50, 800);
        $tagColor = $tagColors[array_rand($tagColors)];
        
        // Check if we have a mapped image, otherwise use picsum
        if (isset($imageMapping[$name])) {
            $imageUrl = 'images/' . $imageMapping[$name];
        } else {
            $imageUrl = "https://picsum.photos/seed/" . urlencode(strtolower($name)) . "/400/400";
        }
        
        $insert->execute([$name, $category, 'women', $price, $tagColor, $imageUrl, 'available']);
        $womenCount++;
    }
}

echo "Added $womenCount women's items\n";

// Generate men's items (100)
$menCount = 0;
foreach ($menGeneratedNames as $category => $names) {
    foreach ($names as $name) {
        if ($menCount >= 100) break 2;
        $price = mt_rand(50, 800);
        $tagColor = $tagColors[array_rand($tagColors)];
        
        if (isset($imageMapping[$name])) {
            $imageUrl = 'images/' . $imageMapping[$name];
        } else {
            $imageUrl = "https://picsum.photos/seed/" . urlencode(strtolower($name)) . "/400/400";
        }
        
        $insert->execute([$name, $category, 'men', $price, $tagColor, $imageUrl, 'available']);
        $menCount++;
    }
}

echo "Added $menCount men's items\n";
echo "Total items: " . ($womenCount + $menCount) . "\n";
