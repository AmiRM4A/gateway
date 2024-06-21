<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Http\Response;
use App\Services\TransactionService;
use App\Http\Requests\GatewayRequest;

class GatewayController {
    protected const BASE_SERVICE_PATH = 'App\Services\\';

    protected function getServiceName(string $name): string {
        return self::BASE_SERVICE_PATH . $name . '\TransactionService';
    }

    protected function getService(string $name, ?string $uniqueId = null): TransactionService {
        return resolve($this->getServiceName($name), ['uniqueId' => $uniqueId]);
    }

    public function create(GatewayRequest $request) {
        try {
            $service = $this->getServiceName($request->gateway);
            $request->validate($service::getCreateTransactionRules());
            $response = $service::create($request->order_id, $request->amount);
            return response([
                'success' => $response->getSuccess(),
                'message' => $response->getMessage(),
                'transaction_id' => $response->getTransactionId(),
                'link' => $response->getLink(),
                'data' => $response->getData()
            ], $response->getStatus());
        } catch (Throwable) {
            return response([
                'success' => false,
                'message' => 'عملیات با خطا مواجه شد!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verify(GatewayRequest $request) {
        try {
            $service = $this->getService($request->gateway, $request->unique_id);
            $response = $service->verify();
            return response([
                'success' => $response->getSuccess(),
                'message' => $response->getMessage(),
                'data' => $response->getData()
            ], $response->getStatus());
        } catch (Throwable) {
            return response([
                'success' => false,
                'message' => 'عملیات با خطا مواجه شد!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
