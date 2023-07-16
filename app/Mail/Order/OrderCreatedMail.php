<?php

namespace App\Mail\Order;

use App\Models\Order;
use App\Services\Order\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;
    protected $file;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, $file)
    {
        $this->order = $order;
        $this->file = $file;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Уведомление о создании заказа от 10 июля 2023 г.',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $service = new Service;
        return new Content(
            markdown: 'mail.order.order_created',
            with: [
                'order' => $this->order,
                'order_products' => $service->get_order_products($this->order, false)['products'],
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [
            Attachment::fromPath($this->file)
                ->as('заказ-'.($this->order->id+1234).'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
