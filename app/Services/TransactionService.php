<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Http\Client\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class TransactionService implements TransactionServiceContract {
    protected Transaction $transaction;

    public function __construct(?string $uniqueId = null) {
        if (!is_null($uniqueId)) {
            $this->transaction = Transaction::whereUniqueId($uniqueId)->firstOr(function () {
                throw new NotFoundHttpException('تراکنش یافت نشد!');
            });
        }
    }

    abstract protected static function getMainEndpoint(?string $method = null): string;

    abstract protected static function getSandboxEndpoint(?string $method = null): string;

    abstract protected static function post(string $method, array $data = [], bool $sand_box = false, ?array $headers = null): Response;

    protected static function getSuccessStatus(): array {
        return [
            0 => 'عملیات با موفقیت انجام شد.'
        ];
    }

    protected static function getFailureStatus(): array {
        return [
            -1 => 'عملیات با خطا مواجه شد'
        ];
    }

    protected static function getDefaultStatus(): string {
        return 'وضعیت نامشخص';
    }

    protected static function getStatus(int $code): string {
        return (static::status() + static::getFailureStatus() + static::getSuccessStatus())[$code] ?: static::getDefaultStatus();
    }

    abstract protected static function status(): array;

    abstract protected static function getCreateTransactionRules(): array;
}
