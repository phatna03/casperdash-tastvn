<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class IngredientMissing extends Notification
{
  protected $vars;

  /**
   * Create a new notification instance.
   */
  public function __construct(array $vars)
  {
    $this->vars = $vars;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['database'];
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return $this->vars;
  }

}
