<?php

namespace App\Services\IdPay;

use App\Models\Transaction;
use Illuminate\Http\Response;
use App\Services\TransactionResponse;
use Illuminate\Support\Facades\Http;

class TransactionService {
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

            return TransactionResponse::successful(Response::HTTP_CREATED, TransactionStatus::status(Response::HTTP_CREATED), $data);
        }

        return TransactionResponse::failure($response->status(), TransactionStatus::status($response->status()), $data);
    }

    public static function verify($transactionId, $orderId): TransactionResponse {
        if (request()?->status() || request()?->status() !== '100') {
            return TransactionResponse::failure(Response::HTTP_BAD_REQUEST, TransactionStatus::PAYMENT_NOT_CONFIRMED);
        }

        if (Transaction::isVerified($transactionId)) {
            return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, TransactionStatus::PAYMENT_NOT_CONFIRMED);
        }

        $transaction = Transaction::whereTransactionId($transactionId)->first();
        if (!$transaction || $transaction->order_id !== $orderId) {
            return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, TransactionStatus::status(TransactionStatus::PAYMENT_NOT_CONFIRMED));
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
                return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, TransactionStatus::status(TransactionStatus::PAYMENT_NOT_CONFIRMED));
            }

            $transaction->is_verified = 1;
            $transaction->update();
            return TransactionResponse::successful(Response::HTTP_OK, TransactionStatus::status(TransactionStatus::PAYMENT_CONFIRMED), $data);
        }

        return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, TransactionStatus::status(TransactionStatus::PAYMENT_NOT_CONFIRMED));
    }

    public static function inquiry($transactionId, $orderId): TransactionResponse {
        $transaction = Transaction::whereTransactionId($transactionId)->first();
        if (!$transaction || $transaction->order_id !== $orderId) {
            return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, TransactionStatus::INQUIRY_NOT_DONE);
        }

        $response = static::post(static::getEndpoint('payment/inquiry'), [
            'id' => $transactionId,
            'order_id' => $orderId
        ]);


        $statusCode = $response->status();
        $errorCode = $response->json('error_code');
        $data = $response->json();

        if ($statusCode === Response::HTTP_OK && !$errorCode) {
            return TransactionResponse::successful(Response::HTTP_OK, TransactionStatus::status(TransactionStatus::INQUIRY_DONE), $data);
        }

        return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, TransactionStatus::INQUIRY_NOT_DONE);
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
        return [
            'id' => ['required', 'string']
        ];
    }
}
