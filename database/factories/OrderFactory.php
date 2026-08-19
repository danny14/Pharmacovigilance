<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Customer;
use Carbon\Carbon;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'purchase_date' => Carbon::now()->subDays(rand(1, 30)),
        ];
    }
}
