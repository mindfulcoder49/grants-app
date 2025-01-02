<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendAlertEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $grants;

    /**
     * Create a new message instance.
     *
     * @param mixed $user The user receiving the email
     * @param mixed $grants The grants to include in the email
     */
    public function __construct($user, $grants)
    {
        $this->user = $user;
        $this->grants = $grants;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Saved Grant Alerts',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.alerts',
            with: [
                'user' => $this->user,
                'grants' => $this->grants,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     *  return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
