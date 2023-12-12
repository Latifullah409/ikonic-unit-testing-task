<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->orderService->processOrder($request->all());

            return response()->json(['message' => 'Custom success message after order processing']);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'An error occurred while processing the order: ' . $e->getMessage()], 500);
        }
    }
}
