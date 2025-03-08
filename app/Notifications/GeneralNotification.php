<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    use Queueable;

    protected $templateName;
    protected $variables;

    public function __construct($templateName, $variables = [])
    {
        $this->templateName = $templateName;
        $this->variables = $variables;
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

        $subject = $this->replaceVariables($template->subject);
        $body = $this->replaceVariables($template->body);

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.general', ['content' => $body]);
    }

    protected function replaceVariables($content)
    {
        foreach ($this->variables as $key => $value) {
            $content = str_replace("{" . $key . "}", $value, $content);
        }
        return $content;
    }
}
