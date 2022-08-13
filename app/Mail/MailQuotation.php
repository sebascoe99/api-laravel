<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailQuotation extends Mailable
{
    use Queueable, SerializesModels;

    public $productos;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($productos)
    {
        $this->productos = $productos;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Nueva cotización')->view('emails.quotation');
    }
}
