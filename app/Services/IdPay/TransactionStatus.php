<?php
namespace App\Services\IdPay;

final class TransactionStatus
{
    // Transaction Enum
    public const UNPAID = 1;
    public const UNSUCCESSFUL = 2;
    public const ERROR_OCCURRED = 3;
    public const BLOCKED = 4;
    public const REFUNDED_TO_PAYER = 5;
    public const SYSTEM_REFUNDED = 6;
    public const PAYMENT_CANCELLED = 7;
    public const TRANSFERRED_TO_GATEWAY = 8;
    public const AWAITING_PAYMENT_CONFIRMATION = 10;
    public const PAYMENT_CONFIRMED = 100;
    public const PAYMENT_PREVIOUSLY_CONFIRMED = 101;
    public const PAYMENT_NOT_CONFIRMED = 102; // Custom
    public const INQUIRY_NOT_DONE = 103; // Custom
    public const TRANSFERRED_TO_RECEIVER = 200;
    public const TRANSACTION_CREATED = 201; // Custom
    public const INQUIRY_DONE = 202; // Custom

    public static function status(int $code): string {
        return match ($code) {
            self::UNPAID => 'پرداخت انجام نشده است',
            self::UNSUCCESSFUL => 'پرداخت ناموفق بوده است',
            self::ERROR_OCCURRED => 'خطا رخ داده است',
            self::BLOCKED => 'بلوکه شده',
            self::REFUNDED_TO_PAYER => 'برگشت به پرداخت کننده',
            self::SYSTEM_REFUNDED => 'برگشت خورده سیستمی',
            self::PAYMENT_CANCELLED => 'انصراف از پرداخت',
            self::TRANSFERRED_TO_GATEWAY => 'به درگاه پرداخت منتقل شد',
            self::AWAITING_PAYMENT_CONFIRMATION => 'در انتظار تایید پرداخت',
            self::PAYMENT_CONFIRMED => 'پرداخت تایید شده است',
            self::PAYMENT_PREVIOUSLY_CONFIRMED => 'پرداخت قبلا تایید شده است',
            self::TRANSFERRED_TO_RECEIVER => 'به دریافت کننده واریز شد',
            self::TRANSACTION_CREATED => 'تراکنش با موفقیت ایجاد شد',
            self::PAYMENT_NOT_CONFIRMED => 'پرداخت تایید نشد',
            self::INQUIRY_NOT_DONE => 'استعلام انجام نشد',
            self::INQUIRY_DONE => 'استعلام با موفقیت انجام شد',
            default => 'وضعیت نامشخص',
        };
    }
}
