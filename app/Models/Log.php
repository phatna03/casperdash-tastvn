<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use App\Api\SysApp;

class Log extends Model
{
  use HasFactory;

  public $table = 'logs';

  protected $fillable = [
    'user_id',
    'restaurant_id',
    'type',
    'item_id',
    'item_type',
    'params',
    'text',
    'deleted',

    'created_at',
  ];

  public function owner()
  {
    return $this->belongsTo('App\Models\User', 'user_id');
  }

  public function restaurant()
  {
    //sensor
    return Restaurant::find($this->restaurant_id);
  }

  public function item()
  {
    $sys_app = new SysApp();

    return $sys_app->get_item($this->item_id, $this->item_type);
  }

  public function set_text()
  {
    $text = 'User <b>' . $this->owner->name . '</b>';
    $params = (array)json_decode($this->params, true);

    switch ($this->type) {
      case 'login':
        $text .= ' has logged into the system';
        break;
      case 'logout':
        $text .= ' has logged out of the system';
        break;
      case 'view_profile_info':
        $text .= ' accessed the profile information page';
        break;
      case 'view_profile_setting':
        $text .= ' accessed the profile setting page';
        break;
      case 'view_dashboard':
        $text .= ' accessed the dashboard page';
        break;
      case 'view_listing_notification':
        $text .= ' accessed the listing notification page';
        break;
      case 'view_listing_restaurant':
        $text .= ' accessed the restaurant management page';
        break;
      case 'view_listing_sensor':
        $text .= ' accessed the sensor management page';
        break;
      case 'view_listing_user':
        $text .= ' accessed the user management page ';
        break;
      case 'view_listing_food_category':
        $text .= ' accessed the category management page';
        break;
      case 'view_listing_ingredient':
        $text .= ' accessed the ingredient management page';
        break;
      case 'view_listing_food':
        $text .= ' accessed the dish management page';
        break;
      case 'view_listing_photo':
        $text .= ' accessed the album photo page';
        break;
      case 'view_listing_text':
        $text .= ' accessed the text note management page';
        break;
      case 'view_listing_setting':
        $text .= ' accessed the system setting page';
        break;
      case 'view_listing_log':
        $text .= ' accessed the system log page';
        break;
      case 'view_modal_testing':
        $text .= ' accessed the modal testing page';
        break;
      case 'view_item_restaurant':
        $text .= ' viewed the restaurant sensor information page for <b>' . $this->restaurant()->name . '</b>';
        break;
      case 'view_item_food':
        $text .= ' viewed the recipe for <b>' . $this->item()->name . '</b> dish';
        break;
      case 'view_item_photo':
        $text .= ' viewed the photo with <b>ID: ' . $this->item()->id . '</b> of sensor name: <b>' . $this->restaurant()->name . '</b>';
        break;
      case 'view_item_restaurant_food_scan':
        $text .= ' viewed the photo scan result with <b>ID: ' . $this->item()->id . '</b> of sensor name: <b>' . $this->restaurant()->name . '</b>';
        break;
      case 'edit_profile_info':
        $text .= ' updated their contact information: <br />';

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
          }
        }
        break;
      case 'edit_pwd':
        $text .= ' changed their password';
        break;
      case 'edit_profile_setting':
        $text .= ' updated their profile settings';
        break;
      case 'edit_profile_notification':
        $text .= ' updated their profile notifications';
        break;
      case 'add_restaurant_parent':
        $text .= ' added a new restaurant named: <b>' . $this->item()->name . '</b>';
        break;
      case 'edit_restaurant_parent':
        $text .= ' updated the information of restaurant named: <b>' . $this->item()->name . '</b> <br />';

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
          }
        }

        break;
      case 'delete_restaurant_parent':
        $text .= ' deleted the restaurant named: <b>' . $this->item()->name . '</b>';
        break;
      case 'add_restaurant':
        $text .= ' added a new restaurant sensor named: <b>' . $this->restaurant()->name . '</b>';
        break;
      case 'edit_restaurant':
        $text .= ' updated the information of restaurant sensor named: <b>' . $this->restaurant()->name . '</b> <br />';

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
          }
        }

        break;
      case 'delete_restaurant':
        $text .= ' deleted the restaurant sensor named: <b>' . $this->restaurant()->name . '</b>';
        break;
      case 'edit_result':
        $text .= ' updated the photo scan result with <b>ID: ' . $this->item()->id . '</b> of sensor named: <b>' . $this->restaurant()->name . '</b> <br />';

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            if (is_numeric($k)) {
              $text .= '<div class="acm-ml-px-10">- ' . $v . '</div>';
            } else {
              $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
            }
          }
        }
        break;
      case 'add_restaurant_dish':
        $text .= ' added dishes to restaurant sensor named: <b>' . $this->restaurant()->name . '</b>';

        if (isset($params['category']) && (int)$params['category']) {
          $food_category = FoodCategory::find((int)$params['category']);
          if ($food_category) {
            $text .= '<div>+ Category: ' . $food_category->name . '</div>';
          }
        }
        if (isset($params['foods']) && count($params['foods'])) {
          $text .= '<div>+ Dishes:</div>';
          foreach ($params['foods'] as $f) {
            $food = Food::find((int)$f);
            if ($food) {
              $text .= '<div class="acm-ml-px-10">- ' . $food->name . '</div>';
            }
          }
        }
        break;
      case 'delete_restaurant_dish':
        $text .= ' deleted dishes from restaurant sensor named: <b>' . $this->restaurant()->name . '</b>';
        break;
      case 'add_food_category':
        $text .= ' added a new category named: <b>' . $this->item()->name . '</b>';
        break;
      case 'edit_food_category':
        $text .= ' updated the information of category named: <b>' . $this->item()->name . '</b> <br />';

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
          }
        }
        break;
      case 'add_ingredient':
        $text .= ' added a new ingredient named: <b>' . $this->item()->name . '</b>';
        break;
      case 'edit_ingredient':
        $text .= ' updated the information of ingredient named: <b>' . $this->item()->name . '</b> <br />';

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
          }
        }
        break;
      case 'add_text':
        $text .= ' added a new text note named: <b>' . $this->item()->name . '</b>';
        break;
      case 'edit_text':
        $text .= ' updated the information of text note: <b>' . $this->item()->name . '</b> <br />';

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
          }
        }
        break;
      case 'add_photo_note':
        $text .= ' added new note for photo with <b>ID: ' . $this->item()->id . '</b> of sensor named: <b>' . $this->restaurant()->name . '</b>';
        break;
      case 'edit_photo_note':
        $text .= ' updated note for photo with <b>ID: ' . $this->item()->id . '</b> of sensor named: <b>' . $this->restaurant()->name . '</b>';

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
          }
        }
        break;
      case 'add_user':
        $text .= ' added a new user named: <b>' . $this->item()->name . '</b>';
        break;
      case 'edit_user':
        $text .= ' updated the information of user named: <b>' . $this->item()->name . '</b>';

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            if (is_numeric($k)) {
              $text .= '<div class="acm-ml-px-10">- ' . $v . '</div>';
            } else {
              $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
            }
          }
        }
        break;
      case 'delete_user':
        $text .= ' deleted the user named: <b>' . $this->item()->name . '</b>';
        break;
      case 'add_food':
        $text .= ' added a new dish named: <b>' . $this->item()->name . '</b>';
        break;
      case 'edit_food':
        $text .= ' updated the information of dish named: <b>' . $this->item()->name . '</b>';

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            if (is_numeric($k)) {
              $text .= '<div class="acm-ml-px-10">- ' . $v . '</div>';
            } else {
              $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
            }
          }
        }
        break;
      case 'edit_food_ingredient':
        $restaurant_parent_id = isset($params['before']['restaurant_parent_id']) ? (int)$params['before']['restaurant_parent_id'] : 0;
        $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

        if ($restaurant_parent) {
          $text .= ' updated the Roboflow ingredient of dish named: <b>' . $this->item()->name . '</b>' . ' at restaurant named: <b>' . $restaurant_parent->name . '</b>';
        } else {
          $text .= ' updated the Roboflow ingredient of dish named: <b>' . $this->item()->name . '</b>';
        }

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            if (is_numeric($k)) {
              $text .= '<div class="acm-ml-px-10">- ' . $v . '</div>';
            } else {
              $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
            }
          }
        }
        break;
      case 'edit_food_recipe':
        $restaurant_parent_id = isset($params['before']['restaurant_parent_id']) ? (int)$params['before']['restaurant_parent_id'] : 0;
        $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

        if ($restaurant_parent) {
          $text .= ' updated the Recipe ingredient of dish named: <b>' . $this->item()->name . '</b>' . ' at restaurant named: <b>' . $restaurant_parent->name . '</b>';
        } else {
          $text .= ' updated the Recipe ingredient of dish named: <b>' . $this->item()->name . '</b>';
        }

        $rs = $this->compare_update($params);
        if (count($rs)) {
          foreach ($rs as $k => $v) {
            if (is_numeric($k)) {
              $text .= '<div class="acm-ml-px-10">- ' . $v . '</div>';
            } else {
              $text .= '<div>+ ' . $this->get_str($k) . ': ' . $v . '</div>';
            }
          }
        }
        break;
      case 'import_food':
        $text .= ' imported a new dish named: <b>' . $this->item()->name . '</b>';
        break;
    }

    $this->update([
      'text' => $text,
    ]);
  }

  public function compare_update($pars = [])
  {
    $arr = [];

    switch ($this->type) {
      case 'edit_profile_info':
        $arr = $this->compare_array($pars['before'], $pars['after'], [
          'name', 'email', 'phone'
        ]);
        break;
      case 'edit_restaurant':
        $arr1 = [];

        if (isset($pars['before']['restaurant_parent_id']) && isset($pars['after']['restaurant_parent_id'])
          && (int)$pars['before']['restaurant_parent_id'] != (int)$pars['after']['restaurant_parent_id']) {
          $res1 = RestaurantParent::find($pars['before']['restaurant_parent_id']);
          $res2 = RestaurantParent::find($pars['after']['restaurant_parent_id']);

          $arr1['restaurant_parent'] = '<span class="acm-text-line-through">' . $res1->name . '</span> ---> <b>' . $res2->name . '</b>';
        }

        $arr2 = $this->compare_array($pars['before'], $pars['after'], [
          'name', 's3_bucket_name', 's3_bucket_address'
        ]);

        $arr = array_merge($arr1, $arr2);
        break;
      case 'edit_result':
        //food
        $arr1 = [];
        $arr1_dish = [];
        $arr1_missing = [];
        if ($pars['before']['food_id'] == $pars['after']['food_id']) {
          $food = Food::find($pars['after']['food_id']);
          $arr1_dish['dish'] = $food ? '<b>' . $food->name . '</b>' : 'Unknown';
        } else {
          $food1 = Food::find($pars['before']['food_id']);
          $food2 = Food::find($pars['after']['food_id']);
          if ($food1 && $food2) {
            $arr1_dish['dish'] = '<span class="acm-text-line-through">' . $food1->name . '</span> ---> <b>' . $food2->name . '</b>';
          } else {
            if ($food1) {
              $arr1_dish['dish'] = '<span class="acm-text-line-through">' . $food1->name . '</span> ---> <b>Unknown</b>';
            }
            if ($food2) {
              $arr1_dish['dish'] = '<span class="acm-text-line-through">Unknown</span> ---> <b>' . $food2->name . '</b>';
            }
          }
        }
        if (json_encode($pars['before']['missings']) != json_encode($pars['after']['missings'])) {
          $arr1_missing['missings'] = '';
          if (count($pars['before']['missings']) && count($pars['after']['missings'])) {

            $arr_after_id = array_column($pars['after']['missings'], 'id');

            foreach ($pars['before']['missings'] as $v) {
              $ingredient = Ingredient::find((int)$v['id']);
              if ($ingredient && !in_array($ingredient->id, $arr_after_id)) {
                $arr1_missing[] = '<span class="acm-text-line-through"><b class="acm-mr-px-5">' . (int)$v['quantity'] . '</b>' . $ingredient->name . '</span>';
              }
            }
            foreach ($pars['after']['missings'] as $v) {
              $ingredient = Ingredient::find((int)$v['id']);
              if ($ingredient) {
                $arr1_missing[] = '<b class="acm-mr-px-5">' . (int)$v['quantity'] . '</b>' . $ingredient->name;
              }
            }
          } else {
            if (count($pars['before']['missings'])) {
              foreach ($pars['before']['missings'] as $v) {
                $ingredient = Ingredient::find((int)$v['id']);
                if ($ingredient) {
                  $arr1_missing[] = '<span class="acm-text-line-through"><b class="acm-mr-px-5">' . (int)$v['quantity'] . '</b>' . $ingredient->name . '</span>';
                }
              }
            }
            if (count($pars['after']['missings'])) {
              foreach ($pars['after']['missings'] as $v) {
                $ingredient = Ingredient::find((int)$v['id']);
                if ($ingredient) {
                  $arr1_missing[] = '<b class="acm-mr-px-5">' . (int)$v['quantity'] . '</b>' . $ingredient->name;
                }
              }
            }
          }
        }
        $arr1 = array_merge($arr1_dish, $arr1_missing);

        //note
        $arr2 = [];
        $arr2_text = [];
        $arr2_note = [];
        if (json_encode($pars['before']['texts']) != json_encode($pars['after']['texts'])) {
          $arr2_text['texts'] = '';
          if (count($pars['before']['texts']) && count($pars['after']['texts'])) {
            foreach ($pars['before']['texts'] as $t) {
              $text = Text::find((int)$t);
              if ($text && !in_array($text->id, $pars['after']['texts'])) {
                $arr2_text[] = '<span class="acm-text-line-through">' . $text->name . '</span>';
              }
            }
            foreach ($pars['after']['texts'] as $t) {
              $text = Text::find((int)$t);
              if ($text) {
                $arr2_text[] = $text->name;
              }
            }
          } else {
            if (count($pars['before']['texts'])) {
              foreach ($pars['before']['texts'] as $t) {
                $text = Text::find((int)$t);
                if ($text) {
                  $arr2_text[] = '<span class="acm-text-line-through">' . $text->name . '</span>';
                }
              }
            }
            if (count($pars['after']['texts'])) {
              foreach ($pars['after']['texts'] as $t) {
                $text = Text::find((int)$t);
                if ($text) {
                  $arr2_text[] = $text->name;
                }
              }
            }
          }
        }
        $arr2_note = $this->compare_array($pars['before'], $pars['after'], [
          'note',
        ]);
        $arr2 = array_merge($arr2_text, $arr2_note);

        $arr = array_merge($arr1, $arr2);
        break;
      case 'edit_food_category':
        $arr = $this->compare_array($pars['before'], $pars['after'], [
          'name',
        ]);
        break;
      case 'edit_ingredient':
        $arr = $this->compare_array($pars['before'], $pars['after'], [
          'name', 'name_vi',
        ]);
        break;
      case 'edit_text':
        $arr = $this->compare_array($pars['before'], $pars['after'], [
          'name',
        ]);
        break;
      case 'edit_photo_note':
        $arr = $this->compare_array($pars['before'], $pars['after'], [
          'content',
        ]);
        break;
      case 'edit_user':
        $arr1 = $this->compare_array($pars['before'], $pars['after'], [
          'name', 'email', 'phone', 'status', 'role', 'note'
        ]);

        $arr2 = [];
        if (json_encode($pars['before']['access_full']) != json_encode($pars['after']['access_full'])) {
          $arr2['access_to_restaurant'] = '';
          if ((int)$pars['before']['access_full']) {
            $arr2[] = '<b>Full</b> restaurant accessed';
          }
          if ((int)$pars['after']['access_full']) {
            $arr2[] = '<b>No</b> restaurant accessed';
          }
        } else {
          if (!empty($pars['before']['access_ids']) && !empty($pars['after']['access_ids'])
            && json_encode($pars['before']['access_ids']) != json_encode($pars['after']['access_ids'])
          ) {

            $arr2['access_to_restaurant'] = '';
            $restaurant1s = json_decode($pars['before']['access_ids'], true);
            $restaurant2s = json_decode($pars['after']['access_ids'], true);

            foreach ($restaurant1s as $r) {
              $text = Restaurant::find((int)$r);
              if ($text && !in_array($text->id, $restaurant2s)) {
                $arr2[] = '<span class="acm-text-line-through">' . $text->name . '</span>';
              }
            }
            foreach ($restaurant2s as $r) {
              $text = Restaurant::find((int)$r);
              if ($text) {
                $arr2[] = '<b>' . $text->name . '</b>';
              }
            }
          } else {
            if (!empty($pars['before']['access_ids'])) {
              $arr2['access_to_restaurant'] = '';
              $restaurants = json_decode($pars['before']['access_ids'], true);
              foreach ($restaurants as $r) {
                $text = Restaurant::find((int)$r);
                if ($text) {
                  $arr2[] = '<span class="acm-text-line-through">' . $text->name . '</span>';
                }
              }
            }
            if (!empty($pars['after']['access_ids'])) {
              $arr2['access_to_restaurant'] = '';
              $restaurants = json_decode($pars['after']['access_ids'], true);
              foreach ($restaurants as $r) {
                $text = Restaurant::find((int)$r);
                if ($text) {
                  $arr2[] = '<b>' . $text->name . '</b>';
                }
              }
            }
          }
        }

        $arr = array_merge($arr1, $arr2);
        break;
      case 'edit_food':
        $arr = $this->compare_array($pars['before'], $pars['after'], [
          'name',
        ]);
        break;
      case 'edit_food_ingredient':
      case 'edit_food_recipe':
        $arr1 = [];

        $arr2 = [];
        if (json_encode($pars['before']['ingredients']) != json_encode($pars['after']['ingredients'])) {
          $arr2['ingredients'] = '';
          if (count($pars['before']['ingredients']) && count($pars['after']['ingredients'])) {

            $arr_after_id = array_column($pars['after']['ingredients'], 'id');

            foreach ($pars['before']['ingredients'] as $v) {
              $ingredient = Ingredient::find((int)$v['id']);
              if ($ingredient && !in_array($ingredient->id, $arr_after_id)) {
                $arr2[] = '<span class="acm-text-line-through"><b class="acm-mr-px-5">' . (int)$v['quantity'] . '</b>' . $ingredient->name . '</span>';
              }
            }
            foreach ($pars['after']['ingredients'] as $v) {
              $ingredient = Ingredient::find((int)$v['id']);
              if ($ingredient) {
                $arr2[] = '<b class="acm-mr-px-5">' . (int)$v['quantity'] . '</b>' . $ingredient->name;
              }
            }
          } else {
            if (count($pars['before']['ingredients'])) {
              foreach ($pars['before']['ingredients'] as $v) {
                $ingredient = Ingredient::find((int)$v['id']);
                if ($ingredient) {
                  $arr2[] = '<span class="acm-text-line-through"><b class="acm-mr-px-5">' . (int)$v['quantity'] . '</b>' . $ingredient->name . '</span>';
                }
              }
            }
            if (count($pars['after']['ingredients'])) {
              foreach ($pars['after']['ingredients'] as $v) {
                $ingredient = Ingredient::find((int)$v['id']);
                if ($ingredient) {
                  $arr2[] = '<b class="acm-mr-px-5">' . (int)$v['quantity'] . '</b>' . $ingredient->name;
                }
              }
            }
          }
        }

        $arr = array_merge($arr1, $arr2);
        break;
    }

    return $arr;
  }

  protected function compare_array($a1, $a2, $keys = [])
  {
    $arr = [];

    if (count($keys)) {
      foreach ($keys as $key) {
        if ($a1[$key] != $a2[$key]) {
          $arr[$key] = $a1[$key] . ' ---> ' . $a2[$key];
        }
      }
    }

    return $arr;
  }

  protected function get_str($key)
  {
    $text = ucfirst($key);

    switch ($key) {
      case 'restaurant_parent':
        $text = 'Restaurant';
        break;
      case 's3_bucket_name':
        $text = 'S3 bucket name';
        break;
      case 's3_bucket_address':
        $text = 'S3 bucket address';
        break;
      case 'name_vi':
        $text = 'Name VN';
        break;
      case 'missings':
        $text = 'Ingredients missing';
        break;
      case 'access_to_restaurant':
        $text = 'List of sensors accessed';
        break;
    }

    return $text;
  }
}
