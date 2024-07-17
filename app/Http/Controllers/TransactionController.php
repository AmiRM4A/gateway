<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\TransactionService;
use App\Http\Requests\StoreTransactionRequest;
use Symfony\Component\HttpFoundation\Response;

class TransactionController {
    protected function getService(string $path, array $params = []): TransactionService {
        return resolve($path, $params);
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
        return response()->json([
            'success' => true,
            'data' => Transaction::all()->load('gateway')
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request) {
        $gateway = Gateway::find($request->gateway_id);
        $service = $this->getService($gateway->service_path, ['gateway' => $gateway]);
        $request->validate($service->getTransactionRules());

        $response = $service->create($request->order_id, $request->amount);

        return response()->json([
            'success' => $response->getSuccess(),
            'message' => $response->getMessage(),
            'unique_id' => $response->getUniqueId(),
            'link' => $response->getLink(),
            'data' => $response->getData()
        ], $response->getStatus());
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction) {
        return response()->json([
            'success' => true,
            'data' => $transaction->load('gateway')
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction) {
        if (empty($request->all())){
            return response()->json([
                'success' => false,
                'message' => 'لطفا فیلد موردنظر برای آپدیت را وارد کنید'
            ], Response::HTTP_BAD_REQUEST);
        }

        $transaction->update($request->all());

        return response()->json([
            'success' => true
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction) {
        $transaction->delete();

        return response()->json([
            'success' => true
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * Handles the verification of an existing transaction via the specified gateway and unique ID.
     */
    public function verify(Transaction $transaction) {
        $gateway = $transaction->gateway;
        $service = $this->getService($gateway->service_path, ['uniqueId' => $transaction->unique_id]);
        $response = $service->verify();

        return response()->json([
            'success' => $response->getSuccess(),
            'message' => $response->getMessage(),
            'data' => $response->getData()
        ], $response->getStatus());
    }
}
