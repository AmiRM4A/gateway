<?php

namespace App\Services\IdPay;

use App\Services\TransactionService as BaseTransactionService;
use App\Models\Transaction;
use Illuminate\Http\Response;
use App\Services\TransactionResponse;
use Illuminate\Support\Facades\Http;

class TransactionService extends BaseTransactionService {
    protected const API_KEY       = '6a7f99eb-7c20-4412-a972-6dfb7cd253a4';
    protected const BASE_API_URL  = 'https://api.idpay.ir/v1.1';
    protected const CALL_BACK_URL = 'http://127.0.0.1:8000/api/verify';
    protected const SANDBOX_URL   = '';

    protected static function getEndpoint(string $method, bool $sand_box = false): string {
        return $sand_box ? static::SANDBOX_URL : static::BASE_API_URL . '/' . trim($method, '/');
    }

    public static function post(string $url, array $data = [], ?array $headers = null) {
        return HTTP::withHeaders($headers ?? [
            'X-API-KEY' => self::API_KEY,
            'X-SANDBOX' => 1,
            'Content-Type' => 'application/json'
        ])->post($url, $data);
    }

    public static function transaction($orderId, $amount): TransactionResponse {
        $response = static::post(static::getEndpoint('payment'), [
            'order_id' => $orderId,
            'amount' => $amount,
            'callback' => static::CALL_BACK_URL
        ]);

        $statusCode = $response->status();
        $data = $response->json();
        $errorCode = $data['error_code'] ?? null;

        if ($statusCode === Response::HTTP_CREATED && !$errorCode) {
            Transaction::create([
                'order_id' => $orderId,
                'transaction_id' => $data['id'],
                'transaction_amount' => $amount,
                'transaction_link' => $data['link'],
                'is_verified' => '0'
            ]);

            return TransactionResponse::successful(Response::HTTP_CREATED, $this->getStatus(201), $data);
        }

        return TransactionResponse::failure($response->status(), $this->getStatus(-1), $data);
    }

    public static function verify($transactionId, $orderId): TransactionResponse {
        if (request()?->status() || request()?->status() !== '100') {
            return TransactionResponse::failure(Response::HTTP_BAD_REQUEST, $this->getStatus(102));
        }

        if (Transaction::isVerified($transactionId)) {
            return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, $this->getStatus(102));
        }

        $transaction = Transaction::whereTransactionId($transactionId)->first();
        if (!$transaction || $transaction->order_id !== $orderId) {
            return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, $this->getStatus(102));
        }

        $response = static::post(static::getEndpoint('payment/verify'), [
            'id' => $transactionId,
            'order_id' => $orderId
        ]);

        $statusCode = $response->status();
        $data = $response->json();
        $errorCode = $data['error_code'] ?? null;

        if ($statusCode === Response::HTTP_OK && !$errorCode) {
            if ($data['payment']['amount'] !== $transaction->transaction_amount) {
                return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, $this->getStatus(102));
            }

            $transaction->is_verified = 1;
            $transaction->update();
            return TransactionResponse::successful(Response::HTTP_OK, $this->getStatus(102), $data);
        }

        return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, $this->getStatus(102));
    }

    public function getTransactionRules(): array {
        return [
            'name' => ['string'],
            'phone' => ['string', 'max:11', 'regex:/^(98|0)?9/'],
            'mail' => ['string', 'email', 'max:255'],
            'desc' => ['string', 'max:255'],
        ];
    }

    public function getVerifyRules() {
        return [
            'id' => ['required', 'string']
        ];
    }

    public function getInquiryRules() {
    protected function status(): array {
        return [
            1 => 'پرداخت انجام نشده است',
            2 => 'پرداخت ناموفق بوده است',
            3 => 'خطا رخ داده است',
            4 => 'بلوکه شده',
            5 => 'برگشت به پرداخت کننده',
            6 => 'برگشت خورده سیستمی',
            7 => 'انصراف از پرداخت',
            8 => 'به درگاه پرداخت منتقل شد',
            10 => 'در انتظار تایید پرداخت',
            100 => 'پرداخت تایید شده است',
            101 => 'پرداخت قبلا تایید شده است',
            102 => 'پرداخت تایید نشد', // Custom
            103 => 'استعلام انجام نشد', // Custom
            200 => 'به دریافت کننده واریز شد',
            201 => 'تراکنش با موفقیت ایجاد شد', // Custom
            202 => 'استعلام با موفقیت انجام شد', // Custom
        ];
    }
}
