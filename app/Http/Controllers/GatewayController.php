<?php

namespace App\Http\Controllers;

use App\Http\Requests\GatewayRequest;

class GatewayController {
    protected const BASE_SERVICE_PATH = 'App\Services\\';

    protected function getServiceName(string $name): string {
        return self::BASE_SERVICE_PATH . $name . '\TransactionService';
    }

    protected function getService(string $name) {
        return new ($this->getServiceName($name))();
    }

    public function transaction(GatewayRequest $request) {
        $service = $this->getService($request->gateway);
        $request->validate($service->getTransactionRules());
        $response = $service->transaction($request->order_id, $request->amount);
        return response([
            'success' => $response->getSuccess(),
            'message' => $response->getMessage(),
            'transaction_id' => $response->getTransactionId(),
            'link' => $response->getLink(),
            'data' => $response->getData()
        ], $response->getStatus());
    }

    public function verify(GatewayRequest $request) {
        $service = $this->getService($request->gateway);
        $request->validate($service->getVerifyRules());
        $response = $service->verify($request->id, $request->order_id);
        return response([
            'success' => $response->getSuccess(),
            'message' => $response->getMessage(),
            'data' => $response->getData()
        ], $response->getStatus());
    }
}
