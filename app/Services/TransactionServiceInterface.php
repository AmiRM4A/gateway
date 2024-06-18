<?php

namespace App\Services;

interface TransactionServiceInterface {
    public function transaction($orderId, $amount): TransactionResponse;
    public function verify($transactionId, $orderId): TransactionResponse;
}
