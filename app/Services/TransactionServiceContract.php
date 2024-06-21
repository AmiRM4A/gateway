<?php

namespace App\Services;

interface TransactionServiceContract {
    public static function create(string $orderId, int $amount): TransactionResponse;
    public function verify(): TransactionResponse;
}
