<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Gateway;
use App\Http\Requests\StoreGatewayRequest;
use App\Http\Requests\UpdateGatewayRequest;
use Symfony\Component\HttpFoundation\Response;

class GatewayController {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return response()->json([
            'success' => true,
            'data' => Gateway::all()->load('transactions')
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGatewayRequest $request) {
        $gateway = Gateway::create([
            'service_path' => $request->service_path,
            'api_key' => $request->api_key,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ساخت درگاه جدید با موفقیت انجام شد.',
            'gateway_id' => $gateway->id
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Gateway $gateway) {
        return response()->json([
            'success' => true,
            'data' => $gateway->load('transactions')
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGatewayRequest $request, Gateway $gateway) {
        if (empty($request->validated())){
            return response()->json([
                'success' => false,
                'message' => 'لطفا فیلد موردنظر برای آپدیت را وارد کنید'
            ], Response::HTTP_BAD_REQUEST);
        }

        $gateway->update($request->validated());

        return response()->json([
            'success' => true
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gateway $gateway) {
        $gateway->delete();

        return response()->json([
            'success' => true
        ], Response::HTTP_OK);
    }
}
