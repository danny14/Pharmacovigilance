<?php

namespace Tests\Feature;

use App\Application\Alerts\SendPharmacovigilanceAlertUseCase;
use App\Mail\PharmacovigilanceAlertMail;
use App\Models\Alert;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendPharmacovigilanceAlertUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_emails_and_logs_audit_records()
    {
        Mail::fake();
        
        // Assert that the SMS logic logs an info message exactly 2 times
        \Illuminate\Support\Facades\Log::shouldReceive('info')
            ->times(2)
            ->withArgs(function ($message) {
                return str_contains($message, 'SMS SENT');
            });

        // 1. Setup Data
        $medication = \App\Models\Medication::factory()->create(['lot_number' => '951357']);

        $customer1 = Customer::factory()->create(['email' => 'test1@example.com']);
        $order1 = Order::factory()->create(['customer_id' => $customer1->id]);
        \App\Models\OrderItem::factory()->create(['order_id' => $order1->id, 'medication_id' => $medication->id]);

        $customer2 = Customer::factory()->create(['email' => 'test2@example.com']);
        $order2 = Order::factory()->create(['customer_id' => $customer2->id]);
        \App\Models\OrderItem::factory()->create(['order_id' => $order2->id, 'medication_id' => $medication->id]);

        $useCase = new SendPharmacovigilanceAlertUseCase();

        // 2. Execute Use Case for 2 orders
        $result = $useCase->execute([$order1->id, $order2->id], '951357');

        // 3. Assertions
        
        // Assert return summary
        $this->assertEquals(2, $result['total_processed']);
        $this->assertEquals(2, $result['success']);
        $this->assertEquals(0, $result['failed']);

        // Assert Emails were queued (since Mailable implements ShouldQueue)
        Mail::assertQueued(PharmacovigilanceAlertMail::class, 2);
        
        Mail::assertQueued(PharmacovigilanceAlertMail::class, function ($mail) use ($customer1) {
            return $mail->hasTo($customer1->email) && $mail->lotNumber === '951357';
        });

        // Assert Audit Logs were created in DB
        $this->assertDatabaseCount('alerts', 2);
        
        $this->assertDatabaseHas('alerts', [
            'order_id' => $order1->id,
            'customer_id' => $customer1->id
        ]);
    }

    public function test_it_handles_missing_orders_gracefully()
    {
        Mail::fake();

        $useCase = new SendPharmacovigilanceAlertUseCase();

        // Pass a non-existent order ID
        $result = $useCase->execute([9999], '951357');

        $this->assertEquals(1, $result['total_processed']);
        $this->assertEquals(0, $result['success']);
        $this->assertEquals(0, $result['failed']);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('alerts', 0);
    }
}
