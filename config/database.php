<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'thrift_pos');
define('DB_USER', 'root');
define('DB_PASS', '');

function ensureSalesPaymentMethodColumn(PDO $pdo) {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM sales LIKE 'payment_method'");
        $stmt->execute();
        $column = $stmt->fetch();
        if ($column && stripos($column['Type'], 'varchar') === false) {
            $pdo->exec("ALTER TABLE sales MODIFY COLUMN payment_method VARCHAR(50) NOT NULL");
        }
    } catch (PDOException $e) {
        // If table does not exist yet or column cannot be inspected, skip the runtime schema fix.
    }
}

function getDB() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        ensureSalesPaymentMethodColumn($pdo);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
