<?php

namespace App\Mail\Web;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactUsForm extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $appName = config('app.name', env('APP_NAME', 'InCloud'));
        $subject = $this->data['subject'] ?? 'Fale Conosco';

        $fromEmail = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'nao-responda@incloudsistemas.com.br'));
        $fromName = config('mail.from.name', env('MAIL_FROM_NAME', $appName));

        return $this->subject("[$appName] $subject")
            ->from($fromEmail, $fromName)
            ->replyTo($this->data['email'], $this->data['name'])
            ->markdown('web.emails.contact-us-form');
    }
}
