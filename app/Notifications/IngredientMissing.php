<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Support\HtmlString;
use App\Models\RestaurantFoodScan;

class IngredientMissing extends Notification implements ShouldQueue
{
  use Queueable;

  protected $_vars;

  /**
   * Create a new notification instance.
   */
  public function __construct(array $vars)
  {
    $this->_vars = $vars;
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
  public function toMail(object $notifiable): MailMessage
  {
    $user = $this->_vars['user'];
    $row = RestaurantFoodScan::find($this->_vars['restaurant_food_scan_id']);

    $url_redirect = url('admin/notifications?rid=' . $row->id);

    $text_food = $row->get_food()->name;
    if ($row->confidence) {
      $text_food = $row->confidence . '% ' . $row->get_food()->name;
    }

    $html_photo = '<div style="max-width: 300px; position: relative; text-align: center; margin: 0 auto; border: 1px solid #efefef; border-radius: 3px;"><img src="' . $row->photo_url . '" style="width: 100%;" /></div>';
    $html_ingredients_missing = '';

    $texts = array_filter(explode('&nbsp', $row->missing_texts));
    if(!empty($row->missing_texts) && count($texts)) {
      foreach ($texts as $text) {
        if(!empty(trim($text))) {
          $html_ingredients_missing .= '<div style="margin-left: 20px;">- ' . $text . '</div>';
        }
      }
    }

    if (!(int)$user->get_setting('missing_ingredient_alert_email')) {
      return false;
    }

    return (new MailMessage)
      ->subject(config('tastevn.email_subject_ingredient_missing') . ': ' . $row->get_restaurant()->name)
      ->greeting('Hello ' . $user->name . '!')
      ->line('The system indicates that an ingredient is missing from a dish served at the restaurant that you manage.')
      ->line('+ Predicted Dish: ' . $text_food)
      ->line('+ Ingredients Missing:')
      ->line(new HtmlString($html_ingredients_missing))
      ->line('+ Photo: ')
      ->line(new HtmlString($html_photo))
      ->action('For more detailed information, please visit the website.', $url_redirect)
      ->line('Thank you for using our application!');
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return $this->_vars;
  }
}
