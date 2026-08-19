<?php

namespace Tests\Feature;

use App\Application\Orders\SearchOrdersByLotUseCase;
use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchOrdersByLotUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_orders_that_contain_the_specified_lot_number()
    {
        // 1. Setup data
        $customer = Customer::factory()->create();
        
        $targetMedication = Medication::factory()->create(['lot_number' => '951357']);
        $otherMedication = Medication::factory()->create(['lot_number' => '111222']);

        // Order 1: Has the target lot
        $order1 = Order::factory()->create([
            'customer_id' => $customer->id,
            'purchase_date' => Carbon::now()->subDays(5)
        ]);
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'medication_id' => $targetMedication->id
        ]);

        // Order 2: Does NOT have the target lot
        $order2 = Order::factory()->create([
            'customer_id' => $customer->id,
            'purchase_date' => Carbon::now()->subDays(5)
        ]);
        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'medication_id' => $otherMedication->id
        ]);

        // 2. Execute Use Case
        $useCase = new SearchOrdersByLotUseCase();
        $result = $useCase->execute('951357');

        // 3. Assertions
        $this->assertCount(1, $result->items());
        $this->assertEquals($order1->id, $result->items()[0]->id);
    }

    public function test_it_filters_orders_by_date_range()
    {
        $customer = Customer::factory()->create();
        $medication = Medication::factory()->create(['lot_number' => '951357']);

        // Order placed exactly 20 days ago
        $orderOld = Order::factory()->create([
            'customer_id' => $customer->id,
            'purchase_date' => Carbon::now()->subDays(20)
        ]);
        OrderItem::factory()->create(['order_id' => $orderOld->id, 'medication_id' => $medication->id]);

        // Order placed 2 days ago
        $orderNew = Order::factory()->create([
            'customer_id' => $customer->id,
            'purchase_date' => Carbon::now()->subDays(2)
        ]);
        OrderItem::factory()->create(['order_id' => $orderNew->id, 'medication_id' => $medication->id]);

        // Search only for orders in the last 10 days
        $startDate = Carbon::now()->subDays(10)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $useCase = new SearchOrdersByLotUseCase();
        $result = $useCase->execute('951357', $startDate, $endDate);

        $this->assertCount(1, $result->items());
        $this->assertEquals($orderNew->id, $result->items()[0]->id);
    }
}
