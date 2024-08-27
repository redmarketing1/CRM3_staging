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

class EstimationForClientMail extends Mailable
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
//        dd($template);
        $this->sender = $template->sender;
        $this->sender_name = $template->sender_name;
        $this->subject = $template->subject;
        $this->content = $template->content;
        $this->view = $template->view;
        $this->pdf = $template->pdf;
		$this->progress_pdf = isset($template->progress_pdf) ? $template->progress_pdf : '';
        $this->cc_emails = $template->cc;
        $this->bcc_emails = isset($template->bcc) ? $template->bcc : '';
        $this->additional_files = isset($template->additional_files) ? $template->additional_files : '';
        $this->project_other_files = isset($template->project_other_files) ? $template->project_other_files : ''; 
        $this->additional_format_files_list = isset($template->additional_format_files_list) ? $template->additional_format_files_list : '';
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
		if (isset($this->progress_pdf) && !empty($this->progress_pdf)){
            $email = $email->attach($this->progress_pdf);
        }
        if (isset($this->additional_files) && !empty($this->additional_files)) {
            if (count($this->additional_files)) {
                foreach ($this->additional_files as $row) {
                    $email = $email->attach(public_path('additional_files/' . $row));
                }
            }
        }
        if (isset($this->project_other_files) && !empty($this->project_other_files)) {
            if (count($this->project_other_files)) {
                foreach ($this->project_other_files as $prow) {
                    $email = $email->attach(get_file('uploads/files') . '/' . rawurlencode($prow['file']), array('as' => $prow['file']));
                }
            }
        }
        if (isset($this->additional_format_files_list) && !empty($this->additional_format_files_list)) {
            if (count($this->additional_format_files_list)) {
                foreach ($this->additional_format_files_list as $row) {
                    $email = $email->attach(get_file('uploads/export') . '/' . rawurlencode($row), array('as' => $row));
                }
            }
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
