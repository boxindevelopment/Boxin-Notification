<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailPayment extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;
    protected $sub;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order, $sub)
    {
        $this->order  = $order;
        $this->sub = $sub;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->view('view.name');
        return $this->markdown('email.invoice')
                    ->subject($sub)
                    ->with('order', $this->order);
    }
}
