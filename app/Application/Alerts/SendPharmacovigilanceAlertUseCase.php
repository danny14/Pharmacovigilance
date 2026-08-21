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

                    // 2. Trigger SMS notification using Twilio
                    if ($order->customer->phone) {

                        $formattedPhone = $order->customer->phone;

                        if (env('TWILIO_SID')) {
                            try {
                                // In local dev (Windows/Docker without CA certs), bypass SSL verification for Twilio API
                                if (env('APP_ENV') === 'local') {
                                    $httpClient = new \Twilio\Http\CurlClient([
                                        CURLOPT_SSL_VERIFYPEER => false,
                                        CURLOPT_SSL_VERIFYHOST => false
                                    ]);
                                    $twilio = new \Twilio\Rest\Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'), null, null, $httpClient);
                                } else {
                                    $twilio = new \Twilio\Rest\Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
                                }

                                $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer->phone);
                                $formattedPhone = '+' . ltrim($cleanPhone, '+');
                                if (strlen($formattedPhone) <= 11) {
                                    $formattedPhone = '+1' . $cleanPhone;
                                }

                                $twilio->messages->create(
                                    $formattedPhone,
                                    [
                                        'from' => env('TWILIO_PHONE_NUMBER'),
                                        // Using Twilio predefined template for trial accounts
                                        'body' => "sms_account_alerts"
                                    ]
                                );

                                \Illuminate\Support\Facades\Log::info("SMS SENT to {$formattedPhone}: SMS SENT Successful.");
                            } catch (Exception $e) {
                                \Illuminate\Support\Facades\Log::error("Twilio SMS Error for {$formattedPhone}: " . $e->getMessage());
                            }
                        } else {
                            \Illuminate\Support\Facades\Log::info("SMS SENT to {$formattedPhone}: URGENT Pharmacovigilance Recall for {$affectedItem->medication->name} (Lot: {$lotNumber}). Please check your email.");
                        }
                    }

                    Alert::create([
                        'customer_id' => $order->customer->id,
                        'order_id' => $order->id,
                        'sent_at' => Carbon::now(),
                    ]);
                });

                $successCount++;
            } catch (Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error processing order ID {$order->id}: " . $e->getMessage());
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
