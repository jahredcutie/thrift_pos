<?php

class PaymentTransaction extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureTable();
    }

    private function ensureTable() {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS payment_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                transaction_id VARCHAR(255) DEFAULT NULL,
                payment_intent_id VARCHAR(255) DEFAULT NULL,
                source_id VARCHAR(255) DEFAULT NULL,
                module_type ENUM('checkout','reservation') NOT NULL,
                reference_id INT DEFAULT NULL,
                item_id INT DEFAULT NULL,
                amount DECIMAL(10, 2) NOT NULL,
                payment_method VARCHAR(50) NOT NULL,
                status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
                processed_by INT DEFAULT NULL,
                metadata TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function create(array $data) {
        $stmt = $this->db->prepare(
            'INSERT INTO payment_transactions (transaction_id, payment_intent_id, source_id, module_type, reference_id, item_id, amount, payment_method, status, processed_by, metadata) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['transaction_id'] ?? null,
            $data['payment_intent_id'] ?? null,
            $data['source_id'] ?? null,
            $data['module_type'],
            $data['reference_id'] ?? null,
            $data['item_id'] ?? null,
            $data['amount'],
            $data['payment_method'],
            $data['status'] ?? 'pending',
            $data['processed_by'] ?? null,
            isset($data['metadata']) ? json_encode($data['metadata']) : null,
        ]);
        return $this->db->lastInsertId();
    }

    public function findByPaymentIntentId($paymentIntentId) {
        $stmt = $this->db->prepare('SELECT * FROM payment_transactions WHERE payment_intent_id = ? LIMIT 1');
        $stmt->execute([$paymentIntentId]);
        return $stmt->fetch();
    }

    public function findByTransactionId($transactionId) {
        $stmt = $this->db->prepare('SELECT * FROM payment_transactions WHERE transaction_id = ? LIMIT 1');
        $stmt->execute([$transactionId]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM payment_transactions WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare('UPDATE payment_transactions SET status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function updateSourceAndTransaction($id, $sourceId, $transactionId) {
        $stmt = $this->db->prepare('UPDATE payment_transactions SET source_id = ?, transaction_id = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$sourceId, $transactionId, $id]);
    }

    public function updateReference($id, $referenceId) {
        $stmt = $this->db->prepare('UPDATE payment_transactions SET reference_id = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$referenceId, $id]);
    }
}
