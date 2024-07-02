<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Gateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TransactionService;
use App\Services\TransactionServiceException;
use App\Http\Requests\StoreTransactionRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransactionController {
    /**
     * Resolves and retrieves an instance of the specified transaction service.
     *
     * @param string $path The path to the service.
     * @param array $params Optional parameters for the service instance.
     *
     * @return TransactionService The resolved instance of TransactionService.
     */
    protected function getService(string $path, array $params = []): TransactionService {
        return resolve($path, $params);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse {
        return response()->json(Transaction::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request) {
        try {
            $gateway = Gateway::find($request->gateway_id);
            $service = $this->getService($gateway->service_path, ['gateway' => $gateway]);
            $request->validate($service->getTransactionRules());
            $response = $service->create($request->order_id, $request->amount);

            return response([
                'success' => $response->getSuccess(),
                'message' => $response->getMessage(),
                'unique_id' => $response->getUniqueId(),
                'link' => $response->getLink(),
                'data' => $response->getData()
            ], $response->getStatus());
        } catch (NotFoundHttpException|TransactionServiceException $e) {
            $exceptionMessage = $e->getMessage();
        } catch (Throwable $e) {
            $exceptionMessage = $e->getMessage();
        }

        return response([
            'success' => false,
            'message' => config('app.debug') ? $exceptionMessage : 'ساخت تراکنش با خطا مواجه شد!',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => 'تراکنش موردنظر پیدا شد.',
            'data' => $transaction->getAttributes()
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse {
        $transaction->update($request->all());
        $transaction->refresh();

        return response()->json([
            'success' => true,
            'message' => 'تراکنش موردنظر با موفقیت آپدیت شد.',
            'data' => $transaction->getAttributes()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): JsonResponse {
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'تراکنش موردنظر با موفقیت حذف شد.',
        ], 204);
    }

    /**
     * Handles the verification of an existing transaction via the specified gateway and unique ID.
     *
     * @param Request $request The incoming HTTP request.
     */
    public function verify(Request $request, Transaction $transaction) {
        try {
            $gateway = $transaction->gateway;
            $service = $this->getService($gateway->service_path, ['uniqueId' => $transaction->unique_id]);
            $request->validate($service->getTransactionRules());
            $response = $service->verify();

            return response([
                'success' => $response->getSuccess(),
                'message' => $response->getMessage(),
                'data' => $response->getData()
            ], $response->getStatus());
        } catch (NotFoundHttpException|TransactionServiceException $e) {
            $exceptionMessage = $e->getMessage();
        } catch (Throwable $e) {
            $exceptionMessage = $e->getMessage();
        }

        return response([
            'success' => false,
            'message' => config('app.debug') ? $exceptionMessage : 'تایید تراکنش با خطا مواجه شد!',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
