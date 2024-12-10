<?php

namespace App\Mail\Web;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class WorkWithUsForm extends Mailable
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
        $subject = $this->data['subject'] ?? 'Trabalhe Conosco';

        $fromEmail = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'nao-responda@incloudsistemas.com.br'));
        $fromName = config('mail.from.name', env('MAIL_FROM_NAME', $appName));

        $workWithUs = $this->subject("[$appName] $subject")
            ->from($fromEmail, $fromName)
            ->replyTo($this->data['email'], $this->data['name']);

        if (isset($this->data['file']) && !empty($this->data['file'])) {
            $workWithUs->attach($this->data['file']->getRealPath(), [
                'as'   => Str::slug($this->data['name']) . '-resume.' . $this->data['file']->getClientOriginalExtension(),
                'mime' => $this->data['file']->getMimeType()
            ]);
        }

        return $workWithUs->markdown('web.emails.work-with-us-form');
    }
}
