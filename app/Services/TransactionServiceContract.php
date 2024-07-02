<?php

namespace App\Services;

interface TransactionServiceContract {
    public function create(string $orderId, int $amount): TransactionResponse;
    public function verify(): TransactionResponse;
}
