<?php

function loadEnv($path = null) {
    static $env;
    if ($env !== null) {
        return $env;
    }

    $env = [];
    if ($path === null) {
        $path = __DIR__ . '/../.env';
    }

    if (!file_exists($path)) {
        return $env;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || strpos($trimmed, '#') === 0) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $trimmed, 2) + [null, null]);
        if ($key !== null) {
            $env[$key] = $value;
        }
    }

    return $env;
}

function env($key, $default = null) {
    $env = loadEnv();
    if (array_key_exists($key, $env)) {
        return $env[$key];
    }
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

function getPaymongoSecretKey() {
    $key = env('PAYMONGO_SECRET_KEY');
    if (!$key) {
        throw new Exception('PayMongo secret key is not configured. Set PAYMONGO_SECRET_KEY in .env or environment.');
    }
    return $key;
}

function getPaymongoWebhookSecret() {
    return env('PAYMONGO_WEBHOOK_SECRET');
}

function buildAppBaseUrl() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME']);
    if ($path === '/' || $path === '\\') {
        $path = '';
    }
    return rtrim($scheme . '://' . $host . $path, '/');
}

function paymongoRequest($method, $endpoint, $payload = null) {
    $secretKey = getPaymongoSecretKey();
    $url = 'https://api.paymongo.com/v1' . $endpoint;

    $headers = [
        'Authorization: Basic ' . base64_encode($secretKey . ':'),
        'Content-Type: application/json',
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_USERPWD, $secretKey . ':');
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

    if ($payload !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $response = curl_exec($curl);
    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($response === false) {
        throw new Exception('PayMongo request failed: ' . $error);
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        throw new Exception('Invalid PayMongo response: ' . $response);
    }

    if ($statusCode >= 400) {
        $message = $decoded['errors'][0]['detail'] ?? json_encode($decoded);
        throw new Exception('PayMongo API error: ' . $message);
    }

    return $decoded;
}

function verifyPaymongoWebhook($payload, $signatureHeader) {
    $secret = getPaymongoWebhookSecret();
    if (!$secret || !$signatureHeader) {
        return false;
    }

    $pairs = [];
    foreach (explode(',', $signatureHeader) as $part) {
        [$key, $value] = explode('=', trim($part), 2) + [null, null];
        if ($key && $value) {
            $pairs[$key] = $value;
        }
    }

    if (!isset($pairs['v1']) || !isset($pairs['t'])) {
        return false;
    }

    $expected = hash_hmac('sha256', $pairs['t'] . '.' . $payload, $secret);
    return hash_equals($expected, $pairs['v1']);
}

function formatAmountCentavos($amount) {
    return (int) round($amount * 100);
}

function paymongoCreatePaymentIntent($amount, $description = '', $allowedMethods = ['card', 'gcash', 'paymaya', 'dob']) {
    $payload = [
        'data' => [
            'attributes' => [
                'amount' => formatAmountCentavos($amount),
                'currency' => 'PHP',
                'payment_method_allowed' => $allowedMethods,
                'description' => trim($description),
            ]
        ]
    ];

    $response = paymongoRequest('POST', '/payment_intents', $payload);
    return $response['data'] ?? null;
}

function paymongoCreateSource($paymentIntentId, $type, $amount, $returnUrl, $metadata = [], $extra = []) {
    $attributes = [
        'type' => $type,
        'amount' => formatAmountCentavos($amount),
        'currency' => 'PHP',
        'redirect' => ['return_url' => $returnUrl],
        'metadata' => $metadata,
        'payment_intent' => ['id' => $paymentIntentId],
    ];

    if (!empty($extra)) {
        $attributes = array_merge($attributes, $extra);
    }

    $payload = ['data' => ['attributes' => $attributes]];
    $response = paymongoRequest('POST', '/sources', $payload);
    return $response['data'] ?? null;
}

function paymongoGetPaymentIntent($paymentIntentId) {
    $response = paymongoRequest('GET', '/payment_intents/' . rawurlencode($paymentIntentId));
    return $response['data'] ?? null;
}
