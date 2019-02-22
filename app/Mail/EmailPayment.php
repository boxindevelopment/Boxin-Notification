<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Order;

class EmailPayment extends Mailable
{
    use Queueable, SerializesModels;

    protected $id;
    protected $sub;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($id, $sub)
    {
        $this->id  = $id;
        $this->sub = $sub;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order = Order::find($this->id);
        // return $this->view('view.name');
        return $this->markdown('email.invoice')
                    ->subject($this->sub)
                    ->with('order', $order);
    }
}
