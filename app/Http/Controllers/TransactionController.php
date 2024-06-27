<?php

namespace App\Http\Controllers;

use Throwable;
use App\Services\TransactionService;
use App\Http\Requests\TransactionRequest;
use App\Services\TransactionServiceException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TransactionController
 *
 * @package App\Http\Controllers
 */
class TransactionController {
    /**
     * The base namespace for transaction service classes.
     */
    protected const BASE_SERVICE_PATH = 'App\Services\\';

    /**
     * Constructs the full class name for the specified service.
     *
     * @param string $name The name of the service.
     *
     * @return string The fully qualified class name of the service.
     */
    protected function getServiceName(string $name): string {
        return self::BASE_SERVICE_PATH . $name . '\TransactionService';
    }

    /**
     * Resolves and retrieves an instance of the specified transaction service.
     *
     * @param string $name The name of the service.
     * @param string|null $uniqueId Optional unique identifier for the service instance.
     *
     * @return TransactionService The resolved instance of TransactionService.
     */
    protected function getService(string $name, ?string $uniqueId = null): TransactionService {
        return resolve($this->getServiceName($name), ['uniqueId' => $uniqueId]);
    }

    /**
     * Handles the creation of a new transaction via the specified gateway.
     *
     * @param TransactionRequest $request The incoming HTTP request.
     */
    public function create(TransactionRequest $request) {
        try {
            $service = $this->getService($request->gateway);
            $request->validate($service::getCreateTransactionRules());
            $response = $service::create($request->order_id, $request->amount);

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
     * Handles the verification of an existing transaction via the specified gateway and unique ID.
     *
     * @param TransactionRequest $request The incoming HTTP request.
     */
    public function verify(TransactionRequest $request, $unique_id) {
        try {
            $service = $this->getService($request->gateway, $unique_id);
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
