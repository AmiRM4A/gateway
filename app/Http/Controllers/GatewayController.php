<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Http\Response;
use App\Services\TransactionService;
use App\Http\Requests\GatewayRequest;
use App\Services\TransactionResponse;
use App\Services\TransactionServiceException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GatewayController {
    protected const BASE_SERVICE_PATH = 'App\Services\\';

    protected function getServiceName(string $name): string {
        return self::BASE_SERVICE_PATH . $name . '\TransactionService';
    }

    protected function getService(string $name, ?string $uniqueId = null): TransactionService {
        return resolve($this->getServiceName($name), ['uniqueId' => $uniqueId]);
    }

    public function create(GatewayRequest $request): void {
        $response = resolve(TransactionResponse::class);

        try {
            /**
             * @var $service TransactionService
             */
            $service = $this->getServiceName($request->gateway);
            $request->validate($service::getCreateTransactionRules());
            /**
             * @var $response TransactionResponse
             */
            $response = $service::create($request->order_id, $request->amount);
        } catch (NotFoundHttpException|TransactionServiceException $e) {
            $response->message($e->getMessage());
        } catch (Throwable $e) {
            $response->message($e->getMessage());
        } finally {
            response([
                'success' => $response->getSuccess(),
                'message' => $response->getMessage(),
                'unique_id' => $response->getUniqueId(),
                'link' => $response->getLink(),
                'data' => $response->getData()
            ], $response->getStatus() ?? Response::HTTP_INTERNAL_SERVER_ERROR)->send();
        }
    }

    public function verify(GatewayRequest $request): void {
        $response = resolve(TransactionResponse::class);

        try {
            $service = $this->getService($request->gateway, $request->unique_id);
            $response = $service->verify();
        } catch (NotFoundHttpException|TransactionServiceException $e) {
            $response->message($e->getMessage());
        } catch (Throwable $e) {
            $response->message($e->getMessage());
        } finally {
            response([
                'success' => $response->getSuccess(),
                'message' => $response->getMessage(),
                'unique_id' => $response->getUniqueId(),
                'link' => $response->getLink(),
                'data' => $response->getData()
            ], $response->getStatus() ?? Response::HTTP_INTERNAL_SERVER_ERROR)->send();
        }
    }
}
