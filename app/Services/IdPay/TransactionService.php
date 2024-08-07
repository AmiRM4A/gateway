<?php

namespace App\Services\IdPay;

use App\Services\TransactionService as BaseTransactionService;
use App\Models\Transaction;
use App\Services\TransactionResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class TransactionService extends BaseTransactionService {
    protected const BASE_API_URL  = 'https://api.idpay.ir/v1.1';
    protected const CALL_BACK_URL = 'http://127.0.0.1:8000/api/verify';
    protected const SANDBOX_URL   = '';

    protected static function getMainEndpoint(string $method = null): string {
        $url = static::BASE_API_URL;
        return $method ? $url . '/' . trim($method, '/') : $url;
    }

    protected static function getSandboxEndpoint(string $method = null): string {
        $url = static::SANDBOX_URL;
        return $method ? $url . '/' . trim($method, '/') : $url;
    }

    protected function post(string $method, array $data = [], bool $sand_box = false, ?array $headers = null): HttpResponse {
        $headers = $headers ?? [
            'X-API-KEY' => $this->gateway->api_key,
            'Content-Type' => 'application/json'
        ];

        if ($sand_box) {
            $headers['X-SANDBOX'] = 1;
            $url = static::getSandboxEndpoint($method);
        } else {
            $url = static::getMainEndpoint($method);
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
     */
    public function create(string $orderId, int $amount): TransactionResponse {
        $uniqueId = Transaction::generateUniqueId();
        $response = $this->post('payment', [
            'order_id' => $orderId,
            'amount' => $amount,
            'callback' => static::CALL_BACK_URL . '/' . $uniqueId
        ]);

        $data = $response->json();

        if (!isset($data['error_code']) && $response->status() == Response::HTTP_CREATED) {
            Transaction::create([
                'gateway_id' => $this->gateway->id,
                'order_id' => $orderId,
                'transaction_id' => $data['id'],
                'amount' => $amount,
                'link' => $data['link'],
                'unique_id' => $uniqueId
            ]);

            $data['unique_id'] = $uniqueId;
            return TransactionResponse::successful(Response::HTTP_CREATED, static::getStatus(201), $data)
                ->link($data['link'])
                ->uniqueId($uniqueId);
        }

        return TransactionResponse::failure($response->status(), static::getStatus(-1), $data);
    }

    /**
     * Verify the existed transaction.
     *
     * @return TransactionResponse The response from the verification process.
     */
    public function verify(): TransactionResponse {
        if (request()->get('status') != 100) {
            return TransactionResponse::failure(Response::HTTP_BAD_REQUEST, static::getStatus(102));
        }

        if ($this->transaction->is_verified == 1) {
            return TransactionResponse::successful(Response::HTTP_CONFLICT, static::getStatus(101));
        }

        $response = $this->post('payment/verify', [
            'id' => $this->transaction->transaction_id,
            'order_id' => $this->transaction->order_id
        ]);

        $data = $response->json();

        if (!isset($data['error_code']) && $response->status() == Response::HTTP_OK) {
            if ($data['payment']['amount'] != $this->transaction->amount) {
                return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, static::getStatus(102));
            }

            $this->transaction->update([
                'is_verified' => 1,
                'track_id' => $data['track_id'],
                'status_code' => $data['status']
            ]);
            return TransactionResponse::successful(Response::HTTP_OK, static::getStatus(102), $data);
        }

        return TransactionResponse::failure(Response::HTTP_NOT_ACCEPTABLE, static::getStatus(102));
    }

    public function getTransactionRules(): array {
        return [
            'name' => ['string'],
            'phone' => ['string', 'max:11', 'regex:/^(98|0)?9\d{9}/'],
            'mail' => ['string', 'email', 'max:255'],
            'desc' => ['string', 'max:255'],
        ];
    }

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
