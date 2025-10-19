<?php

namespace App\Notifications;

use App\Models\ChairRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FacultyRoleRequestNotification extends Notification
{
    use Queueable;

    protected $chairRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(ChairRequest $chairRequest)
    {
        $this->chairRequest = $chairRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $requester = $this->chairRequest->user;
        $department = $this->chairRequest->department;
        
        return (new MailMessage)
            ->subject('New Faculty Member Request - ' . $department->name)
            ->line('A new faculty member request requires your approval.')
            ->line('**Requester:** ' . $requester->name)
            ->line('**Email:** ' . $requester->email)
            ->line('**Department:** ' . $department->name)
            ->line('**Employee Code:** ' . ($requester->employee_code ?? 'Not provided'))
            ->line('**Designation:** ' . ($requester->designation ?? 'Not provided'))
            ->action('Review Request', url('/faculty/manage-accounts'))
            ->line('Please review and approve/reject this faculty member request.');
    }

    /**
     * Get the array representation of the notification (for database storage).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'chair_request_id' => $this->chairRequest->id,
            'requester_name' => $this->chairRequest->user->name,
            'requester_email' => $this->chairRequest->user->email,
            'department_name' => $this->chairRequest->department->name,
            'requested_role' => $this->chairRequest->requested_role,
            'message' => 'New faculty member request from ' . $this->chairRequest->user->name . ' for ' . $this->chairRequest->department->name,
            //
        ];
    }
}
