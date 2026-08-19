<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create admin user for pharmacovigilance login
        // Admin User
        User::factory()->create([
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret'),
            'role' => 'admin',
        ]);

        // Viewer User
        User::factory()->create([
            'username' => 'viewer',
            'name' => 'Viewer User',
            'email' => 'viewer@example.com',
            'password' => Hash::make('secret'),
            'role' => 'viewer',
        ]);

        // 2. Create target medication for alerts and some others
        $targetMedication = Medication::create([
            'name' => 'Amoxicillin 500mg',
            'lot_number' => '951357',
        ]);

        $otherMedication1 = Medication::create([
            'name' => 'Ibuprofen 400mg',
            'lot_number' => '123456',
        ]);

        $otherMedication2 = Medication::create([
            'name' => 'Lisinopril 10mg',
            'lot_number' => '654321',
        ]);

        // 3. Create customers
        $customer1 = Customer::create(['name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '555-0101']);
        $customer2 = Customer::create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '555-0102']);
        $customer3 = Customer::create(['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'phone' => '555-0103']);
        $customer4 = Customer::create(['name' => 'Bob Brown', 'email' => 'bob@example.com', 'phone' => '555-0104']);

        // 4. Create recent orders (last month) with affected medication
        // Customer 1 bought affected medication 10 days ago
        $order1 = Order::create([
            'customer_id' => $customer1->id,
            'purchase_date' => Carbon::now()->subDays(10),
        ]);
        OrderItem::create(['order_id' => $order1->id, 'medication_id' => $targetMedication->id]);
        OrderItem::create(['order_id' => $order1->id, 'medication_id' => $otherMedication1->id]);

        // Customer 2 bought affected medication 20 days ago
        $order2 = Order::create([
            'customer_id' => $customer2->id,
            'purchase_date' => Carbon::now()->subDays(20),
        ]);
        OrderItem::create(['order_id' => $order2->id, 'medication_id' => $targetMedication->id]);

        // 5. Create older order (outside last month) with affected medication
        // Customer 3 bought affected medication 2 months ago
        $order3 = Order::create([
            'customer_id' => $customer3->id,
            'purchase_date' => Carbon::now()->subMonths(2),
        ]);
        OrderItem::create(['order_id' => $order3->id, 'medication_id' => $targetMedication->id]);

        // 6. Create recent orders with other medications (not affected)
        // Customer 4 bought 5 days ago but other medications
        $order4 = Order::create([
            'customer_id' => $customer4->id,
            'purchase_date' => Carbon::now()->subDays(5),
        ]);
        OrderItem::create(['order_id' => $order4->id, 'medication_id' => $otherMedication2->id]);
    }
}
