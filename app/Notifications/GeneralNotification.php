<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class GeneralNotification extends Notification
{
    use Queueable;

    protected $templateName;

    protected $variables;

    protected $attachments = [];

    public function __construct($templateName, $variables = [], $attachments = [])
    {
        $this->templateName = $templateName;
        $this->variables = $variables;
        $this->attachments = $attachments;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $template = EmailTemplate::where('slug', Str::slug($this->templateName))->first();

        if (!$template) {
            throw new \Exception("Email template not found: {$this->templateName}");
        }

        $this->variables = $template->variables;
        $subject = $this->replaceVariables($template->subject);
        $body = $this->replaceVariables($template->body);

        $mail = (new MailMessage)
            ->subject($subject)
            ->markdown('emails.general', ['content' => $body]);

        foreach ($this->attachments as $attachment) {
            if (isset($attachment['path']) && isset($attachment['name'])) {
                $mail->attach($attachment['path'], [
                    'as' => $attachment['name'],
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }

    protected function replaceVariables($content)
    {
        foreach ($this->variables as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return $content;
    }
}
