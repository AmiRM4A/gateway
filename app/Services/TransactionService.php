<?php

namespace App\Services;

abstract class TransactionService {
    abstract protected static function getEndpoint(string $method, bool $sand_box = false): string;
    abstract public static function post(string $url, array $data = [], ?array $headers = null);
    abstract public static function transaction($orderId, $amount): TransactionResponse;
    abstract public static function verify($transactionId, $orderId): TransactionResponse;
    abstract public function getTransactionRules(): array;
}
