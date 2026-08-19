<?php

namespace App\Application\Orders;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ExportOrdersByLotUseCase
{
    /**
     * Extracts all orders of a lot in a date range for CSV export.
     * Returns a complete collection (without pagination).
     */
    public function execute(string $lotNumber, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        return Order::with(['customer'])
            ->whereHas('orderItems.medication', function ($query) use ($lotNumber) {
                $query->where('lot_number', $lotNumber);
            })
            ->whereBetween('purchase_date', [$start, $end])
            ->orderBy('purchase_date', 'desc')
            ->get();
    }
}
