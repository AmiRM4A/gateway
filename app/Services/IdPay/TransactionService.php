<?php

namespace App\Services\IdPay;

use App\Services\TransactionServiceException;
use Illuminate\Http\Client\ConnectionException;
use App\Services\TransactionService as BaseTransactionService;
use App\Models\Transaction;
use App\Services\TransactionResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

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
    protected static function getMainEndpoint(string $method = null): string {
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
    protected static function getSandboxEndpoint(string $method = null): string {
        $url = static::SANDBOX_URL;
        return $method ? $url . '/' . trim($method, '/') : $url;
    }

    /**
     * Makes a POST request to the specified URL with the given data and headers.
     *
     * @param string $method The specific API method to call.
     * @param bool $sand_box If true, use the sandbox URL; otherwise, use the main URL.
     * @param array $data The data to include in the POST request.
     * @param array|null $headers Optional headers to include in the request.
     *
     * @return HttpResponse The response from the POST request.
     * @throws ConnectionException If a connection error occurs.
     */
    protected static function post(string $method, array $data = [], bool $sand_box = false, ?array $headers = null): HttpResponse {
        $headers = $headers ?? [
            'X-API-KEY' => self::API_KEY,
            'Content-Type' => 'application/json'
        ];

        if ($sand_box) {
            $headers['X-SANDBOX'] = 1;
            $url = static::getSandboxEndpoint($method);
        } else {
            $url = static::getMainEndpoint();
        }

        return HTTP::withHeaders($headers)->post($url, $data);
    }

    /**
     * Create a new transaction with the given order ID and amount.
     *
     * @param string $orderId The order ID.
     * @param int $amount The transaction amount.
     *
     * @return TransactionResponse The response from the transaction creation.
     * @throws TransactionServiceException If a connection error occurs.
     */
    public static function create(string $orderId, int $amount): TransactionResponse {
        $uniqueId = Transaction::generateUniqueId();
        try {
            $response = static::post('payment', [
                'order_id' => $orderId,
                'amount' => $amount,
                'callback' => static::CALL_BACK_URL . '/' . $uniqueId
            ]);
        } catch (ConnectionException $e) {
            throw new TransactionServiceException('خطا در ساخت تراکنش: ' . $e->getMessage(), $e->getCode());
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
                'unique_id' => $uniqueId
            ]);

            $data = array_merge($data, ['unique_id' => $uniqueId]);
            return TransactionResponse::successful(Response::HTTP_CREATED, static::getStatus(201), $data);
        }

        return TransactionResponse::failure($response->status(), static::getStatus(-1), $data);
    }

    /**
     * Verify the existed transaction.
     *
     * @return TransactionResponse The response from the verification process.
     * @throws TransactionServiceException If a connection error occurs.
     */
    public function verify(): TransactionResponse {
        if (request()?->status() !== '100') {
            return TransactionResponse::failure(Response::HTTP_BAD_REQUEST, static::getStatus(102));
        }

        if ($this->transaction->is_verified === 0) {
            return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, static::getStatus(102));
        }

        try {
            $response = static::post('payment/verify', [
                'id' => $this->transaction->transaction_id,
                'order_id' => $this->transaction->order_id
            ]);
        } catch (ConnectionException $e) {
            throw new TransactionServiceException('خطا در تایید تراکنش: ' . $e->getMessage(), $e->getCode());
        }

        $statusCode = $response->status();
        $data = $response->json();
        $errorCode = $data['error_code'] ?? null;

        if ($statusCode === Response::HTTP_OK && !$errorCode) {
            if ($data['payment']['amount'] !== $this->transaction->amount) {
                return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, static::getStatus(102));
            }

            $this->transaction->is_verified = 1;
            $this->transaction->update();
            return TransactionResponse::successful(Response::HTTP_OK, static::getStatus(102), $data);
        }

        return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, static::getStatus(102));
    }

    /**
     * Get the validation rules for creating a transaction.
     *
     * @return array The validation rules.
     */
    public static function getCreateTransactionRules(): array {
        return [
            'order_id' => ['required', 'string', 'max:50'],
            'name' => ['string'],
            'phone' => ['string', 'max:11', 'regex:/^(98|0)?9\d{9}/'],
            'mail' => ['string', 'email', 'max:255'],
            'desc' => ['string', 'max:255'],
        ];
    }

    /**
     * Get the status messages for different transaction states.
     *
     * @return array The status messages.
     */
    protected static function status(): array {
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
