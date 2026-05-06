<?php

class RackCategory extends Model {
    
    public function __construct() {
        parent::__construct();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM rack_categories ORDER BY gender, subcategory, name");
        $categories = $stmt->fetchAll();
        foreach ($categories as &$cat) {
            if ($cat['price_tiers']) {
                $cat['price_tiers'] = json_decode($cat['price_tiers'], true);
            }
            if (!isset($cat['stock_total']) || $cat['stock_total'] === null) {
                $cat['stock_total'] = 10;
            }
            if (!isset($cat['stock_available']) || $cat['stock_available'] === null) {
                $cat['stock_available'] = 10;
            }
        }
        return $categories;
    }

    public function getByGender($gender) {
        $stmt = $this->db->prepare("SELECT * FROM rack_categories WHERE gender = ? ORDER BY subcategory, name");
        $stmt->execute([$gender]);
        $categories = $stmt->fetchAll();
        foreach ($categories as &$cat) {
            if ($cat['price_tiers']) {
                $cat['price_tiers'] = json_decode($cat['price_tiers'], true);
            }
            if (!isset($cat['stock_total']) || $cat['stock_total'] === null) {
                $cat['stock_total'] = 10;
            }
            if (!isset($cat['stock_available']) || $cat['stock_available'] === null) {
                $cat['stock_available'] = 10;
            }
        }
        return $categories;
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM rack_categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        if ($category && $category['price_tiers']) {
            $category['price_tiers'] = json_decode($category['price_tiers'], true);
        }
        if ($category) {
            if (!isset($category['stock_total']) || $category['stock_total'] === null) {
                $category['stock_total'] = 10;
            }
            if (!isset($category['stock_available']) || $category['stock_available'] === null) {
                $category['stock_available'] = 10;
            }
        }
        return $category;
    }

    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM rack_categories WHERE name = ?");
        $stmt->execute([$name]);
        $category = $stmt->fetch();
        if ($category && $category['price_tiers']) {
            $category['price_tiers'] = json_decode($category['price_tiers'], true);
        }
        if ($category) {
            if (!isset($category['stock_total']) || $category['stock_total'] === null) {
                $category['stock_total'] = 10;
            }
            if (!isset($category['stock_available']) || $category['stock_available'] === null) {
                $category['stock_available'] = 10;
            }
        }
        return $category;
    }
}
