<?php

namespace App\Application\Alerts;

use App\Mail\PharmacovigilanceAlertMail;
use App\Models\Alert;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendPharmacovigilanceAlertUseCase
{
    /**
     * Sends pharmacovigilance alerts to the customers corresponding to one or more orders.
     * Also records the alerts in the database (Audit).
     *
     * @param array $orderIds
     * @param string $lotNumber
     * @return array Summary of the operation (successes, failures)
     */
    public function execute(array $orderIds, string $lotNumber): array
    {
        $orders = Order::with(['customer', 'orderItems.medication'])
            ->whereIn('id', $orderIds)
            ->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($orders as $order) {
            try {
                // Find the affected medication within the order
                $affectedItem = $order->orderItems->first(function ($item) use ($lotNumber) {
                    return $item->medication && $item->medication->lot_number === $lotNumber;
                });

                if (!$affectedItem || !$order->customer) {
                    $failedCount++;
                    continue;
                }

                // We use a transaction to ensure consistency between the sending (or queuing) and the audit
                DB::transaction(function () use ($order, $affectedItem, $lotNumber) {
                    
                    // 1. Send/Queue the email
                    Mail::to($order->customer->email)->send(
                        new PharmacovigilanceAlertMail(
                            $order->customer,
                            $order,
                            $affectedItem->medication->name,
                            $lotNumber
                        )
                    );

                    // 2. Trigger SMS notification (Simulation using Log for local environment)
                    if ($order->customer->phone) {
                        \Illuminate\Support\Facades\Log::info("SMS SENT to {$order->customer->phone}: URGENT Pharmacovigilance Recall for {$affectedItem->medication->name} (Lot: {$lotNumber}). Please check your email.");
                    }

                    // 3. Record in the audit table (Alerts)
                    Alert::create([
                        'customer_id' => $order->customer->id,
                        'order_id' => $order->id,
                        'sent_at' => Carbon::now(),
                    ]);
                });

                $successCount++;
            } catch (Exception $e) {
                // In a real production environment, we would report the error to Sentry/Datadog here.
                $failedCount++;
            }
        }

        return [
            'total_processed' => count($orderIds),
            'success' => $successCount,
            'failed' => $failedCount,
        ];
    }
}
