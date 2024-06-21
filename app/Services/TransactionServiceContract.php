<?php

namespace App\Services;

interface TransactionServiceContract {
    public function transaction($orderId, $amount): TransactionResponse;
    public function verify(string $uniqueId): TransactionResponse;
}
