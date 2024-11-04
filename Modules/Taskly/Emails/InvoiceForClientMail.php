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

class InvoiceForClientMail extends Mailable
{
    use Queueable, SerializesModels;

    public $from;
    public $sender;
    public $sender_name;
    public $subject;
    public $content;
    public $pdf;
	public $progress_pdf;
    public $cc_emails;
    public $bcc_emails;
    public $view;
    public $additional_files;
    public $project_other_files;
    public $additional_format_files_list;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template)
    {
    // dd($template);
        $this->sender = $template->sender;
        $this->sender_name = $template->sender_name;
        $this->subject = $template->subject;
        $this->content = $template->content;
        $this->view = $template->view;
        $this->pdf = $template->pdf;
        $this->cc_emails = $template->cc;
        $this->bcc_emails = isset($template->bcc) ? $template->bcc : '';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->from($this->sender, $this->sender_name)->markdown($this->view)->subject($this->subject)->with("content",$this->content);
        if (isset($this->pdf) && !empty($this->pdf)){
            $email = $email->attach($this->pdf);
        }
        if (isset($this->cc_emails) && !empty($this->cc_emails)){
            $email = $email->cc($this->cc_emails);
        }
        if (isset($this->bcc_emails) && !empty($this->bcc_emails)){
            $email = $email->bcc($this->bcc_emails);
        }
        return $email;

    }
}
