<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Gateway;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreGatewayRequest;
use App\Http\Requests\UpdateGatewayRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Exceptions\GatewayControllerException;

class GatewayController {
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse {
        return response()->json(Gateway::all(), Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGatewayRequest $request): JsonResponse {
        try {
            $gateway = Gateway::create([
                'service_path' => $request->service_path,
                'api_key' => $request->api_key,
                'description' => $request->description,
            ]);

            if (!($gateway instanceof Gateway)) {
                throw new GatewayControllerException('ساخت درگاه جدید با خطا مواجه شد!');
            }

            return response()->json([
                'success' => true,
                'message' => 'ساخت درگاه جدید با موفقیت انجام شد.',
                'gateway_id' => $gateway->id
            ], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'ساخت درگاه جدید با خطا مواجه شد!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Gateway $gateway): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => 'درگاه موردنظر پیدا شد.',
            'data' => $gateway->getAttributes()
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGatewayRequest $request, Gateway $gateway): JsonResponse {
        try {
            $gateway->update($request->validated());
            $gateway->refresh();

            return response()->json([
                'success' => true,
                'message' => 'درگاه موردنظر با موفقیت آپدیت شد.',
                'data' => [
                    'service_path' => $gateway->service_path,
                    'api_key' => $gateway->api_key,
                    'description' => $gateway->description,
                    'added_at' => $gateway->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $gateway->updated_at->format('Y-m-d H:i:s')
                ]
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'آپدیت درگاه موردنظر با خطا مواجه شد.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gateway $gateway): JsonResponse {
        try {
            $gateway->delete();

            return response()->json([
                'success' => true,
                'message' => 'درگاه موردنظر با موفقیت حذف شد.',
            ], Response::HTTP_NO_CONTENT);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'حذف درگاه موردنظر با خطا مواجه شد.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
