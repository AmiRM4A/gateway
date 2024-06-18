<?php

namespace App\Services;

abstract class TransactionService implements TransactionServiceInterface {
    abstract protected function getEndpoint(string $method, bool $sand_box = false): string;
    abstract protected function post(string $url, array $data = [], ?array $headers = null);
    protected function getSuccessStatus(): array {
        return [
            0 => 'عملیات با موفقیت انجام شد.'
        ];
    }
    protected function getFailureStatus() {
        return [
            -1 => 'عملیات با خطا مواجه شد'
        ];
    }
    protected function getDefaultStatus(): string {
        return 'وضعیت نامشخص';
    }
    protected function getStatus(int $code): ?string {
        return array_merge($this->getSuccessStatus(), $this->getFailureStatus(), $this->status())[$code] ?: $this->getDefaultStatus();
    }
    abstract protected function status(): array;
    abstract protected function getTransactionRules(): array;
    abstract protected function getVerifyRules(): array;
}
