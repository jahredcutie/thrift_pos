<?php
$data = json_encode([
    'module_type' => 'checkout',
    'module_id' => null,
    'payment_channel' => 'gcash',
    'amount' => 150.00,
    'items' => [
        ['id' => 1, 'price' => 150, 'discount' => 0, 'final_price' => 150]
    ]
]);
$options = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $data,
        'ignore_errors' => true,
    ],
];
$context = stream_context_create($options);
$result = file_get_contents('http://localhost/thrift_pos/api/paymongo/create', false, $context);
echo $result === false ? 'REQUEST_FAILED' : $result;
