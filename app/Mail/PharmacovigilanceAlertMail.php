<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PharmacovigilanceAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Customer $customer;
    public Order $order;
    public string $medicationName;
    public string $lotNumber;

    /**
     * Create a new message instance.
     */
    public function __construct(Customer $customer, Order $order, string $medicationName, string $lotNumber)
    {
        $this->customer = $customer;
        $this->order = $order;
        $this->medicationName = $medicationName;
        $this->lotNumber = $lotNumber;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'URGENT: Medication Recall / Warning Notice - Lot ' . $this->lotNumber,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
