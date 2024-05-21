<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;

use App\Api\SysCore;
use App\Models\RestaurantFoodScan;
use App\Models\Comment;
use App\Models\User;

class PhotoCommentMail extends Notification implements ShouldQueue
{
  use Queueable;

  protected const _DEBUG = false;
  protected const _DEBUG_LOG_FILE_MAIL = 'public/logs/notify_mail.log';

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
    return ['database', 'mail'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail($notifiable)
  {
    $api_core = new SysCore();

    $user = $notifiable;
    $row = RestaurantFoodScan::findOrFail($this->vars['restaurant_food_scan_id']);
    $comment = Comment::findOrFail($this->vars['comment_id']);
    $owner = User::findOrFail($this->vars['owner_id']);
    $type = $this->vars['typed']; //photo_comment_add - photo_comment_edit

    $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_MAIL, 'TODO_AT_' . date('d_M_Y_H_i_s')) : $api_core->log_failed();
    $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_MAIL, 'TYPE_' . $type) : $api_core->log_failed();

//    if (!(int)$user->get_setting('photo_comment_alert_email')) {
//      return false;
//    }

    $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_MAIL, 'SUBJECT_' . config('tastevn.email_subject_photo_comment')) : $api_core->log_failed();

    $subject = config('tastevn.email_subject_photo_comment') . ': ' . $row->get_restaurant()->name;
    $greeting = 'Hello ' . $user->name . '!';

    $text1 = 'The system indicates that a photo has new note from [' . $owner->name . ']';
    if ($type == 'photo_comment_edit') {
      $text1 = 'The system indicates that [' . $owner->name . '] has updated their note';
    }

    $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_MAIL, 'TEXT1_' . $text1) : $api_core->log_failed();

    return (new MailMessage)
      ->subject($subject)
      ->greeting($greeting)
      ->line($text1)
      ->line('+ Comment: ')
      ->line(new HtmlString($comment->content))
      ->line('+ Photo: ')
      ->line(new HtmlString($this->getHtmlPhoto($row)))
      ->action('For more detailed information, please visit the website.', $this->getUrlRedirect($row))
      ->line('Thank you for using our application!');
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

  protected function getPredictedDish($row)
  {
    $textFood = $row->get_food()->name;
    if ($row->confidence) {
      $textFood = $row->confidence . '% ' . $row->get_food()->name;
    }
    return $textFood;
  }

  protected function getHtmlIngredientsMissing($row)
  {
    $htmlIngredientsMissing = '';
    $texts = array_filter(explode('&nbsp', $row->missing_texts));
    if (!empty($row->missing_texts) && count($texts)) {
      foreach ($texts as $text) {
        if (!empty(trim($text))) {
          $htmlIngredientsMissing .= '<div style="margin-left: 20px;">- ' . $text . '</div>';
        }
      }
    }
    return $htmlIngredientsMissing;
  }

  protected function getHtmlPhoto($row)
  {
    return '<div style="max-width: 300px; position: relative; text-align: center; margin: 0 auto; border: 1px solid #efefef; border-radius: 3px;"><img src="' . $row->photo_url . '" style="width: 100%;" /></div>';
  }

  protected function getUrlRedirect($row)
  {
    return url('admin/notifications?rid=' . $row->id);
  }

}
