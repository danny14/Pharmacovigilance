<?php

namespace App\Application\Orders;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchOrdersByLotUseCase
{
    /**
     * Searches for purchase orders associated with a specific lot number within a date range.
     *
     * @param string $lotNumber
     * @param string|null $startDate (YYYY-MM-DD)
     * @param string|null $endDate (YYYY-MM-DD)
     * @return LengthAwarePaginator
     */
    public function execute(string $lotNumber, ?string $startDate = null, ?string $endDate = null): LengthAwarePaginator
    {
        // If no dates are provided, default to the last 30 days until today
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        return Order::with(['customer', 'orderItems.medication'])
            ->whereHas('orderItems.medication', function ($query) use ($lotNumber) {
                $query->where('lot_number', $lotNumber);
            })
            ->whereBetween('purchase_date', [$start, $end])
            ->orderBy('purchase_date', 'desc')
            ->paginate(15);
    }
}
