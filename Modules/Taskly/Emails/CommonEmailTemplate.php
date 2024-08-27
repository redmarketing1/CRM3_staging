<?php

namespace Modules\Taskly\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CommonEmailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public $from;
    public $sender;
    public $subject;
    public $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template)
    {
        $this->sender = $template->sender;
        $this->sender_name = $template->sender_name;
        $this->subject = $template->subject;
        $this->content = $template->content;
        $this->template = $template->view;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->sender, $this->sender_name)->markdown($this->template)->subject($this->subject)->with('content', $this->content);

    }
}
