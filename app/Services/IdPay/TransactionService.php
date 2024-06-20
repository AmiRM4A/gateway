<?php

namespace App\Services\IdPay;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use App\Services\TransactionService as BaseTransactionService;
use App\Models\Transaction;
use Illuminate\Http\Response;
use App\Services\TransactionResponse;
use Illuminate\Support\Facades\Http;
use \Illuminate\Http\Client\Response as HttpResponse;

/**
 * TransactionService handles the interactions with the IDPay payment gateway.
 */
class TransactionService extends BaseTransactionService {
    protected const API_KEY       = '6a7f99eb-7c20-4412-a972-6dfb7cd253a4';
    protected const BASE_API_URL  = 'https://api.idpay.ir/v1.1';
    protected const CALL_BACK_URL = 'http://127.0.0.1:8000/api/verify';
    protected const SANDBOX_URL   = '';

    /**
     * Get the main API endpoint.
     *
     * Constructs the full URL for the main API endpoint. If a method is provided,
     * it appends the method to the base URL.
     *
     * @param string|null $method The API method to append to the base URL. Defaults to null.
     *
     * @return string The full URL for the main API endpoint.
     */
    protected function getMainEndpoint(string $method = null): string {
        $url = static::BASE_API_URL;
        return $method ? $url . '/' . trim($method, '/') : $url;
    }

    /**
     * Get the sandbox API endpoint.
     *
     * Constructs the full URL for the sandbox API endpoint. If a method is provided,
     * it appends the method to the sandbox URL.
     *
     * @param string|null $method The API method to append to the sandbox URL. Defaults to null.
     *
     * @return string The full URL for the sandbox API endpoint.
     */
    protected function getSandboxEndpoint(string $method = null): string {
        $url = static::SANDBOX_URL;
        return $method ? $url . '/' . trim($method, '/') : $url;
    }

    /**
     * Make a POST request to the specified URL with the given data and headers.
     *
     * @param string $url The URL to send the POST request to.
     * @param array $data The data to include in the POST request.
     * @param array|null $headers Optional headers to include in the request.
     *
     * @return HttpResponse The response from the POST request.
     * @throws ConnectionException Throws an ConnectionException on connection errors.
     */
    protected function post(string $url, array $data = [], ?array $headers = null): HttpResponse {
        return HTTP::withHeaders($headers ?? [
            'X-API-KEY' => self::API_KEY,
            'X-SANDBOX' => 1,
            'Content-Type' => 'application/json'
        ])->post($url, $data);
    }

    /**
     * Create a new transaction with the given order ID and amount.
     *
     * @param string $orderId The order ID.
     * @param int $amount The transaction amount.
     *
     * @return TransactionResponse The response from the transaction creation.
     */
    public function transaction($orderId, $amount): TransactionResponse {
        $key = Transaction::generateUniqueId();
        try {
            $response = $this->post($this->getMainEndpoint('payment'), [
                'order_id' => $orderId,
                'amount' => $amount,
                'callback' => static::CALL_BACK_URL . '/' . $key
            ]);
        } catch (\Throwable) {
            return TransactionResponse::failure(Response::HTTP_INTERNAL_SERVER_ERROR, $this->getStatus(-1));
        }

        $statusCode = $response->status();
        $data = $response->json();
        $errorCode = $data['error_code'] ?? null;

        if ($statusCode === Response::HTTP_CREATED && !$errorCode) {
            Transaction::create([
                'order_id' => $orderId,
                'transaction_id' => $data['id'],
                'transaction_amount' => $amount,
                'transaction_link' => $data['link'],
                'is_verified' => '0',
                'unique_id' => $key
            ]);

            return TransactionResponse::successful(Response::HTTP_CREATED, $this->getStatus(201), $data);
        }

        return TransactionResponse::failure($response->status(), $this->getStatus(-1), $data);
    }

    /**
     * Verify a transaction with the given transaction ID and order ID.
     *
     * @param string $uniqueId The specific id of created transaction.
     *
     * @return TransactionResponse The response from the verification process.
     */
    public function verify(string $uniqueId): TransactionResponse {
        if (request()?->status() !== '100') {
            return TransactionResponse::failure(Response::HTTP_BAD_REQUEST, $this->getStatus(102));
        }

        if (!Transaction::isVerified($uniqueId)) {
            return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, $this->getStatus(102));
        }

        $transaction = Transaction::whereUniqueId($uniqueId)->first();
        if (!$transaction) {
            return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, $this->getStatus(102));
        }

        try {
            $response = $this->post($this->getMainEndpoint('payment/verify'), [
                'id' => $transaction->transaction_id,
                'order_id' => $transaction->order_id
            ]);
        } catch (Exception) {
            return TransactionResponse::failure(Response::HTTP_INTERNAL_SERVER_ERROR, $this->getStatus(-1));
        }

        $statusCode = $response->status();
        $data = $response->json();
        $errorCode = $data['error_code'] ?? null;

        if ($statusCode === Response::HTTP_OK && !$errorCode) {
            if ($data['payment']['amount'] !== $transaction->amount) {
                return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, $this->getStatus(102));
            }

            $transaction->is_verified = 1;
            $transaction->update();
            return TransactionResponse::successful(Response::HTTP_OK, $this->getStatus(102), $data);
        }

        return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, $this->getStatus(102));
    }

    /**
     * Get the validation rules for creating a transaction.
     *
     * @return array The validation rules.
     */
    public function getTransactionRules(): array {
        return [
            'name' => ['string'],
            'phone' => ['string', 'max:11', 'regex:/^(98|0)?9/'],
            'mail' => ['string', 'email', 'max:255'],
            'desc' => ['string', 'max:255'],
        ];
    }

    /**
     * Get the validation rules for verifying a transaction.
     *
     * @return array The validation rules.
     */
    public function getVerifyRules(): array {
        return [
            'id' => ['required', 'string']
        ];
    }

    /**
     * Get the status messages for different transaction states.
     *
     * @return array The status messages.
     */
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
