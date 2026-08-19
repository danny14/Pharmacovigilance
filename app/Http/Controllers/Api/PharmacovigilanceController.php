<?php

namespace App\Http\Controllers\Api;

use App\Application\Orders\SearchOrdersByLotUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchOrdersRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PharmacovigilanceController extends Controller
{
    #[OA\Get(
        path: "/api/medications/search",
        summary: "Search orders by lot number",
        description: "Searches all orders containing a medication with the specified lot number within a date range. Returns paginated results.",
        security: [["bearerAuth" => []]],
        tags: ["Pharmacovigilance"],
        parameters: [
            new OA\Parameter(
                name: "lot",
                description: "Lot number to search",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string", example: "951357")
            ),
            new OA\Parameter(
                name: "start_date",
                description: "Start date (YYYY-MM-DD)",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", format: "date")
            ),
            new OA\Parameter(
                name: "end_date",
                description: "End date (YYYY-MM-DD)",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", format: "date")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of orders found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
                                new OA\Property(property: "total", type: "integer")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Parameter validation error")
        ]
    )]
    public function searchOrdersByLot(SearchOrdersRequest $request, SearchOrdersByLotUseCase $useCase): JsonResponse
    {
        $orders = $useCase->execute(
            $request->validated('lot'),
            $request->validated('start_date'),
            $request->validated('end_date')
        );

        return response()->json([
            'data' => $orders
        ]);
    }

    #[OA\Post(
        path: "/api/alerts/send",
        summary: "Send alert to buyers (Single or Bulk)",
        description: "Sends an alert email to the customers of the specified orders and records the operation in the audit log.",
        security: [["bearerAuth" => []]],
        tags: ["Pharmacovigilance"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["order_ids", "lot"],
                properties: [
                    new OA\Property(property: "order_ids", type: "array", items: new OA\Items(type: "integer"), description: "List of Order IDs", example: [1, 2]),
                    new OA\Property(property: "lot", type: "string", description: "Affected lot number", example: "951357")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Alerts processed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Alert process finished"),
                        new OA\Property(
                            property: "summary",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_processed", type: "integer", example: 2),
                                new OA\Property(property: "success", type: "integer", example: 2),
                                new OA\Property(property: "failed", type: "integer", example: 0)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function sendAlerts(\App\Http\Requests\SendAlertRequest $request, \App\Application\Alerts\SendPharmacovigilanceAlertUseCase $useCase): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Admins only'], 403);
        }

        $result = $useCase->execute(
            $request->validated('order_ids'),
            $request->validated('lot')
        );

        return response()->json([
            'message' => 'Alert process finished',
            'summary' => $result
        ]);
    }

    public function getAlerts(): JsonResponse
    {
        $alerts = \App\Models\Alert::with('order.customer')->latest()->take(20)->get();
        return response()->json($alerts);
    }

    public function exportOrdersByLot(SearchOrdersRequest $request, \App\Application\Orders\ExportOrdersByLotUseCase $useCase)
    {
        $orders = $useCase->execute(
            $request->validated('lot'),
            $request->validated('start_date'),
            $request->validated('end_date')
        );

        $filename = "pharmacovigilance_export_lot_" . $request->validated('lot') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, ['Order ID', 'Purchase Date', 'Customer Name', 'Customer Email', 'Customer Phone']);

            // Rows
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->purchase_date->format('Y-m-d H:i:s'),
                    $order->customer->name,
                    $order->customer->email,
                    $order->customer->phone ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
