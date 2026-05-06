<?php

class Item extends Model {
    
    private $womenGeneratedNames = [
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

    private $menGeneratedNames = [
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

    public function __construct() {
        parent::__construct();
    }

    public function getGeneratedNamesForGender($gender) {
        return $gender === 'women' ? $this->womenGeneratedNames : $this->menGeneratedNames;
    }

    private function ensureGenderColumn() {
        $stmt = $this->db->query("SHOW COLUMNS FROM items LIKE 'gender'");
        if (!$stmt->fetch()) {
            $this->db->exec("ALTER TABLE items ADD COLUMN gender ENUM('women','men','unisex') NOT NULL DEFAULT 'unisex' AFTER category");
        }
    }

    private function ensureItemClassification() {
        $stmt = $this->db->query("SELECT * FROM items");
        $items = $stmt->fetchAll();
        $update = $this->db->prepare("UPDATE items SET category = ?, gender = ? WHERE id = ?");

        foreach ($items as $item) {
            $computedCategory = $this->determineCategory($item['name']);
            $computedGender = $this->determineGender($item['name'], $computedCategory);

            if ($item['category'] !== $computedCategory || !isset($item['gender']) || $item['gender'] !== $computedGender) {
                $update->execute([$computedCategory, $computedGender, $item['id']]);
            }
        }
    }

    private function ensureWholeNumberPrices() {
        $stmt = $this->db->query("SELECT id, name, price FROM items");
        $items = $stmt->fetchAll();
        $update = $this->db->prepare("UPDATE items SET price = ? WHERE id = ?");

        foreach ($items as $item) {
            $price = (int) round($item['price']);
            if ($price < 50) {
                $price = mt_rand(50, 800);
            }
            if ($price > 800) {
                $price = 800;
            }

            if ($price === 50 && $this->isGeneratedItemName($item['name'])) {
                $price = mt_rand(50, 800);
            }

            if ((int) $item['price'] !== $price) {
                $update->execute([$price, $item['id']]);
            }
        }
    }

    private function isGeneratedItemName($name) {
        $allNames = array_merge(
            ...array_values($this->womenGeneratedNames),
            ...array_values($this->menGeneratedNames)
        );
        return in_array($name, $allNames, true);
    }

    private function ensureRealisticGeneratedNames() {
        $stmt = $this->db->query("SELECT id, name, category, gender FROM items WHERE name LIKE 'Auto %'");
        $items = $stmt->fetchAll();
        $update = $this->db->prepare("UPDATE items SET name = ? WHERE id = ?");

        foreach ($items as $item) {
            $name = $this->generateRealisticName($item['category'], $item['gender']);
            if ($name) {
                $update->execute([$name, $item['id']]);
            }
        }
    }

    private function ensureMinimumItemsPerCategory() {
        $categories = ['Tops', 'Dresses', 'Bottoms', 'Outerwear', 'Footwear', 'Accessories'];
        $tagColors = ['red', 'blue', 'green', 'yellow'];
        $insert = $this->db->prepare("INSERT INTO items (name, category, gender, price, tag_color, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($categories as $category) {
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM items WHERE category = ?");
            $countStmt->execute([$category]);
            $currentCount = (int) $countStmt->fetchColumn();

            for ($i = $currentCount; $i < 20; $i++) {
                $gender = $this->selectGenderForCategory($category, $i);
                $name = $this->generateRealisticName($category, $gender, $i);
                $price = mt_rand(50, 800);
                $tagColor = $tagColors[array_rand($tagColors)];
                $imageUrl = "https://picsum.photos/seed/" . urlencode(strtolower($name)) . "/400/400";

                $insert->execute([
                    $name,
                    $category,
                    $gender,
                    $price,
                    $tagColor,
                    $imageUrl,
                    'available'
                ]);
            }
        }
    }

    private function selectGenderForCategory($category, $index) {
        if ($category === 'Dresses') {
            return 'women';
        }

        if ($category === 'Tops') {
            return ($index % 2 === 0) ? 'women' : 'men';
        }

        if ($category === 'Bottoms') {
            return ($index % 2 === 0) ? 'women' : 'men';
        }

        if ($category === 'Outerwear') {
            return ($index % 2 === 0) ? 'women' : 'men';
        }

        if ($category === 'Footwear' || $category === 'Accessories') {
            return 'unisex';
        }

        return 'unisex';
    }

    private function generateRealisticName($category, $gender, $index = 0) {
        if ($gender === 'women' && isset($this->womenGeneratedNames[$category])) {
            $names = $this->womenGeneratedNames[$category];
            return $names[$index % count($names)];
        }

        if ($gender === 'men' && isset($this->menGeneratedNames[$category])) {
            $names = $this->menGeneratedNames[$category];
            return $names[$index % count($names)];
        }

        if (isset($this->womenGeneratedNames[$category])) {
            $names = array_merge($this->womenGeneratedNames[$category], $this->menGeneratedNames[$category] ?? []);
            return $names[$index % count($names)];
        }

        return "Brand Classic {$category}";
    }

    private function determineCategory($name) {
        $name = strtolower($name);

        if (preg_match('/\b(dress|skirt|blouse|crop|off[- ]shoulder|sundress|wrap|bodycon|maxi|mini|evening)\b/', $name)) {
            return 'Dresses';
        }

        if (preg_match('/\b(hoodie|jacket|sweater|cardigan|blazer|coat|windbreaker|puffer|trucker|bomber|varsity)\b/', $name)) {
            return 'Outerwear';
        }

        if (preg_match('/\b(jean|jeans|pant|pants|trouser|trousers|chino|chinos|jogger|joggers|short|shorts|skirt)\b/', $name)) {
            return 'Bottoms';
        }

        if (preg_match('/\b(sneaker|loafers?|dress shoes|sandals|boots?|heel|flat|flats|shoe|shoes|kicks)\b/', $name)) {
            return 'Footwear';
        }

        if (preg_match('/\b(bag|handbag|belt|scarf|hat|cap|wallet|watch|sunglasses)\b/', $name)) {
            return 'Accessories';
        }

        if (preg_match('/\b(tee|t-shirt|tshirt|polo|shirt|top|tank|henley|blouse|crop)\b/', $name)) {
            return 'Tops';
        }

        return 'Others';
    }

    private function determineGender($name, $category) {
        $name = strtolower($name);

        if (preg_match('/\b(women|women\'s|female|ladies|girls?)\b/', $name)) {
            return 'women';
        }

        if ($category === 'Dresses' || preg_match('/\b(dress|skirt|blouse|crop|off[- ]shoulder|sundress|wrap|bodycon|maxi|mini|evening)\b/', $name)) {
            return 'women';
        }

        if (preg_match('/\b(henley|button[- ]down|jogger|chino|chinos|watch|wallet|sunglasses|belt|cap|loafers?|dress shoes|cardigan|blazer|hoodie|jacket|sweater|boots?)\b/', $name)) {
            return 'unisex';
        }

        return 'unisex';
    }

    private function getImageUrl(array $item) {
        $categoryList = ['Tops', 'Bottoms', 'Outerwear', 'Footwear', 'Accessories'];

        if (in_array($item['category'], $categoryList, true) && isset($this->imageMapping[$item['name']])) {
            return 'images/' . $this->imageMapping[$item['name']];
        }

        return $item['image_url'] ?? '';
    }

    public function getAll($section = null, $category = null, $search = null) {
        $query = "SELECT * FROM items WHERE 1=1";
        $params = [];

        if ($section === 'women') {
            $query .= " AND (gender = 'women' OR gender = 'unisex')";
        } elseif ($section === 'men') {
            $query .= " AND (gender = 'men' OR gender = 'unisex')";
        }

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        if ($search) {
            $query .= " AND name LIKE ?";
            $params[] = "%$search%";
        }

        $query .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        foreach ($items as &$item) {
            $item['image_url'] = $this->getImageUrl($item);
        }
        
        return $items;
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if ($item) {
            $item['image_url'] = $this->getImageUrl($item);
        }
        
        return $item;
    }

    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM items");
        $dbCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $defaultCategories = ['Tops', 'Bottoms', 'Outerwear', 'Footwear', 'Accessories', 'Dresses', 'Others'];
        
        return array_unique(array_merge($defaultCategories, $dbCategories));
    }
}
