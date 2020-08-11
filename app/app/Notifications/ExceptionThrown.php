<?php

namespace App\Notifications;

use App\Console\Commands\ViewApiAuditLogEntries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class ExceptionThrown extends Notification
{
    use Queueable;

    /**
     * @var array
     */
    private $content;

    /**
     * ExceptionThrown constructor.
     *
     * @param array $content
     */
    public function __construct(array $content)
    {
        $this->content = $content;
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage())
            ->error()
            ->content(sprintf(
                "[%s] An exception has been thrown. \n To investigate the causing API request, \n please access the endpoint `/apiauditlog?ip_trace_id=%s` \n or the console command `php artisan %s` with `ip_trace_id` `%s`",
                strtoupper(config('app.env')),
                $this->content['IP Trace Entry ID'],
                'ot:view-api-audit-log-entries',
                $this->content['IP Trace Entry ID'],
            ))
            ->attachment(function ($attachment) {
                    $attachment->title('DETAILS:')
                        ->fields($this->content);
            });
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
