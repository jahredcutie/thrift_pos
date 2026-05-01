<?php
require_once __DIR__ . '/../Models/PaymentTransaction.php';

class PaymentController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }
    }

    public function create() {
        $json = json_decode(file_get_contents('php://input'), true);

        $moduleType = $json['module_type'] ?? null;
        $paymentChannel = $json['payment_channel'] ?? null;
        $amount = isset($json['amount']) ? (float)$json['amount'] : 0;
        $items = $json['items'] ?? [];
        $moduleId = isset($json['module_id']) ? (int)$json['module_id'] : null;
        $itemId = isset($json['item_id']) ? (int)$json['item_id'] : null;

        if (!in_array($moduleType, ['checkout', 'reservation'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid module type.']);
        }

        if (!in_array($paymentChannel, ['gcash', 'paymaya', 'card', 'bdo', 'bpi', 'unionbank', 'maribank', 'other_bank'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid payment channel.']);
        }

        if ($amount <= 0) {
            $this->json(['success' => false, 'message' => 'Payment amount must be greater than zero.']);
        }

        $db = getDB();
        $db->beginTransaction();

        try {
            if ($moduleType === 'checkout') {
                if (!is_array($items) || count($items) === 0) {
                    throw new Exception('Cart cannot be empty.');
                }

                $calculatedTotal = 0;
                foreach ($items as $item) {
                    $calculatedTotal += isset($item['final_price']) ? (float)$item['final_price'] : 0;
                }
                if (abs($calculatedTotal - $amount) > 0.01) {
                    throw new Exception('Cart total mismatch.');
                }

                $stmtSale = $db->prepare('INSERT INTO sales (user_id, total_amount, payment_method, status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE id=id');
                $stmtSale->execute([$_SESSION['user_id'], $amount, $paymentChannel, 'pending']);
                $saleId = $db->lastInsertId();

                $stmtItem = $db->prepare('INSERT INTO sale_items (sale_id, item_id, price, discount, final_price) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id=id');
                $stmtUpdateItem = $db->prepare('UPDATE items SET status = ? WHERE id = ?');

                foreach ($items as $item) {
                    $stmtItem->execute([
                        $saleId,
                        $item['id'],
                        $item['price'],
                        $item['discount'],
                        $item['final_price']
                    ]);
                    $stmtUpdateItem->execute(['sold', $item['id']]);
                }

                $moduleId = $saleId;
                $itemId = isset($items[0]['id']) ? (int)$items[0]['id'] : null;
            } else {
                if (!$moduleId) {
                    throw new Exception('Reservation ID is required.');
                }

                $stmtReservation = $db->prepare('SELECT r.*, i.id as item_id FROM reservations r JOIN items i ON r.item_id = i.id WHERE r.id = ? FOR UPDATE');
                $stmtReservation->execute([$moduleId]);
                $reservation = $stmtReservation->fetch();
                if (!$reservation) {
                    throw new Exception('Reservation not found.');
                }
                if (!in_array($reservation['status'], ['reserved', 'pending'], true)) {
                    throw new Exception('Reservation is not available for payment.');
                }

                $itemId = (int)$reservation['item_id'];
                $stmtUpdateReservation = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
                $stmtUpdateReservation->execute(['pending', $moduleId]);
            }

            $transactionModel = new PaymentTransaction();
            $transactionId = uniqid('txn_', true);
            $transactionModel->create([
                'transaction_id' => $transactionId,
                'payment_intent_id' => null,
                'source_id' => null,
                'module_type' => $moduleType,
                'reference_id' => $moduleId,
                'item_id' => $itemId,
                'amount' => $amount,
                'payment_method' => $paymentChannel,
                'status' => 'pending',
                'metadata' => ['payment_channel' => $paymentChannel],
                'processed_by' => $_SESSION['user_id']
            ]);

            $db->commit();

            $qrData = null;
            $checkoutUrl = null;
            if ($paymentChannel === 'card') {
                // For card, no QR, just form
            } else {
                if ($paymentChannel === 'maribank') {
                    $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                    $baseUrlPath = $scriptName === '/' ? '' : $scriptName;
                    $qrData = $baseUrlPath . '/assets/images/maribank-qr.png';
                } else {
                    $qrText = "Payment for {$moduleType} - Amount: {$amount} PHP - Channel: {$paymentChannel}";
                    $qrData = "https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=" . urlencode($qrText);
                }
            }

            $this->json([
                'success' => true,
                'transaction_id' => $transactionId,
                'sale_id' => $saleId ?? null,
                'qr_data' => $qrData,
                'checkout_url' => $checkoutUrl,
                'payment_method' => $paymentChannel,
                'status' => 'pending',
                'message' => 'Payment created. Staff will process manually.',
                'is_card' => $paymentChannel === 'card'
            ]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function markPaid() {
        $json = json_decode(file_get_contents('php://input'), true);
        $transactionId = $json['transaction_id'] ?? null;

        if (!$transactionId) {
            $this->json(['success' => false, 'message' => 'Transaction ID required.']);
        }

        $transactionModel = new PaymentTransaction();
        $transaction = $transactionModel->findByTransactionId($transactionId);
        if (!$transaction) {
            $this->json(['success' => false, 'message' => 'Transaction not found.']);
        }

        if ($transaction['status'] === 'paid') {
            $this->json(['success' => false, 'message' => 'Already marked as paid.']);
        }

        $db = getDB();
        $db->beginTransaction();

        try {
            $transactionModel->updateStatus($transaction['id'], 'paid');

            if ($transaction['module_type'] === 'checkout') {
                $stmt = $db->prepare('UPDATE sales SET status = ?, payment_method = ? WHERE id = ?');
                $stmt->execute(['paid', $transaction['payment_method'], $transaction['reference_id']]);
            } elseif ($transaction['module_type'] === 'reservation') {
                $stmtRes = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
                $stmtRes->execute(['paid', $transaction['reference_id']]);
                if ($transaction['item_id']) {
                    $stmtItem = $db->prepare('UPDATE items SET status = ? WHERE id = ?');
                    $stmtItem->execute(['sold', $transaction['item_id']]);
                }
            }

            $db->commit();
            $this->json(['success' => true, 'message' => 'Payment marked as paid.']);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
