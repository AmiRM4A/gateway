<?php

namespace App\Services;

use Illuminate\Http\Client\Response;

abstract class TransactionService implements TransactionServiceContract {
    abstract protected function getMainEndpoint(?string $method = null): string;
    abstract protected function getSandboxEndpoint(?string $method = null): string;
    abstract protected function post(string $url, array $data = [], ?array $headers = null): Response;
    protected function getSuccessStatus(): array {
        return [
            0 => 'عملیات با موفقیت انجام شد.'
        ];
    }
    protected function getFailureStatus(): array {
        return [
            -1 => 'عملیات با خطا مواجه شد'
        ];
    }
    protected function getDefaultStatus(): string {
        return 'وضعیت نامشخص';
    }
    protected function getStatus(int $code): string {
        return ($this->status() + $this->getFailureStatus() + $this->getSuccessStatus())[$code] ?: $this->getDefaultStatus();
    }
    abstract protected function status(): array;
    abstract protected function getTransactionRules(): array;
    abstract protected function getVerifyRules(): array;
}
