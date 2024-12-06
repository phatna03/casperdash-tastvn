<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Support\HtmlString;
use App\Models\RestaurantFoodScan;

class IngredientMissingMail extends Notification implements ShouldQueue
{
  use Queueable;

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
    return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail($notifiable)
  {
    $row = RestaurantFoodScan::findOrFail($this->vars['restaurant_food_scan_id']);
    $user = $notifiable;

    $subject = config('tastevn.email_subject_ingredient_missing') . ': ' . $row->get_restaurant()->name;
    $greeting = 'Hello ' . $user->name . '!';

    return (new MailMessage)
      ->subject($subject)
      ->greeting($greeting)
      ->line('The system indicates that an ingredient is missing from a dish served at the restaurant that you manage.')
      ->line('+ Predicted Dish: ' . $this->getPredictedDish($row))
      ->line('+ Ingredients Missing:')
      ->line(new HtmlString($this->getHtmlIngredientsMissing($row)))
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
    $textFood = $row->get_food() ? $row->get_food()->name : 'Unknown dish information';
    if ($row->get_food() && $row->confidence) {
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
