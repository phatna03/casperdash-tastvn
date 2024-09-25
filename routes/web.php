<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\laravel_example\UserManagement;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\dashboard\Crm;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\layouts\CollapsedMenu;
use App\Http\Controllers\layouts\ContentNavbar;
use App\Http\Controllers\layouts\ContentNavSidebar;
use App\Http\Controllers\layouts\Horizontal;
use App\Http\Controllers\layouts\Vertical;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\front_pages\Landing;
use App\Http\Controllers\front_pages\Pricing;
use App\Http\Controllers\front_pages\Payment;
use App\Http\Controllers\front_pages\Checkout;
use App\Http\Controllers\front_pages\HelpCenter;
use App\Http\Controllers\front_pages\HelpCenterArticle;
use App\Http\Controllers\apps\Email;
use App\Http\Controllers\apps\Chat;
use App\Http\Controllers\apps\Calendar;
use App\Http\Controllers\apps\Kanban;
use App\Http\Controllers\apps\EcommerceDashboard;
use App\Http\Controllers\apps\EcommerceProductList;
use App\Http\Controllers\apps\EcommerceProductAdd;
use App\Http\Controllers\apps\EcommerceProductCategory;
use App\Http\Controllers\apps\EcommerceOrderList;
use App\Http\Controllers\apps\EcommerceOrderDetails;
use App\Http\Controllers\apps\EcommerceCustomerAll;
use App\Http\Controllers\apps\EcommerceCustomerDetailsOverview;
use App\Http\Controllers\apps\EcommerceCustomerDetailsSecurity;
use App\Http\Controllers\apps\EcommerceCustomerDetailsBilling;
use App\Http\Controllers\apps\EcommerceCustomerDetailsNotifications;
use App\Http\Controllers\apps\EcommerceManageReviews;
use App\Http\Controllers\apps\EcommerceReferrals;
use App\Http\Controllers\apps\EcommerceSettingsDetails;
use App\Http\Controllers\apps\EcommerceSettingsPayments;
use App\Http\Controllers\apps\EcommerceSettingsCheckout;
use App\Http\Controllers\apps\EcommerceSettingsShipping;
use App\Http\Controllers\apps\EcommerceSettingsLocations;
use App\Http\Controllers\apps\EcommerceSettingsNotifications;
use App\Http\Controllers\apps\AcademyDashboard;
use App\Http\Controllers\apps\AcademyCourse;
use App\Http\Controllers\apps\AcademyCourseDetails;
use App\Http\Controllers\apps\LogisticsDashboard;
use App\Http\Controllers\apps\LogisticsFleet;
use App\Http\Controllers\apps\InvoiceList;
use App\Http\Controllers\apps\InvoicePreview;
use App\Http\Controllers\apps\InvoicePrint;
use App\Http\Controllers\apps\InvoiceEdit;
use App\Http\Controllers\apps\InvoiceAdd;
use App\Http\Controllers\apps\UserList;
use App\Http\Controllers\apps\UserViewAccount;
use App\Http\Controllers\apps\UserViewSecurity;
use App\Http\Controllers\apps\UserViewBilling;
use App\Http\Controllers\apps\UserViewNotifications;
use App\Http\Controllers\apps\UserViewConnections;
use App\Http\Controllers\apps\AccessRoles;
use App\Http\Controllers\apps\AccessPermission;
use App\Http\Controllers\pages\UserProfile;
use App\Http\Controllers\pages\UserTeams;
use App\Http\Controllers\pages\UserProjects;
use App\Http\Controllers\pages\UserConnections;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsSecurity;
use App\Http\Controllers\pages\AccountSettingsBilling;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\Faq;
use App\Http\Controllers\pages\Pricing as PagesPricing;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\pages\MiscComingSoon;
use App\Http\Controllers\pages\MiscNotAuthorized;
use App\Http\Controllers\pages\MiscServerError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\LoginCover;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\RegisterCover;
use App\Http\Controllers\authentications\RegisterMultiSteps;
use App\Http\Controllers\authentications\VerifyEmailBasic;
use App\Http\Controllers\authentications\VerifyEmailCover;
use App\Http\Controllers\authentications\ResetPasswordBasic;
use App\Http\Controllers\authentications\ResetPasswordCover;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\authentications\ForgotPasswordCover;
use App\Http\Controllers\authentications\TwoStepsBasic;
use App\Http\Controllers\authentications\TwoStepsCover;
use App\Http\Controllers\wizard_example\Checkout as WizardCheckout;
use App\Http\Controllers\wizard_example\PropertyListing;
use App\Http\Controllers\wizard_example\CreateDeal;
use App\Http\Controllers\modal\ModalExample;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\cards\CardAdvance;
use App\Http\Controllers\cards\CardStatistics;
use App\Http\Controllers\cards\CardAnalytics;
use App\Http\Controllers\cards\CardGamifications;
use App\Http\Controllers\cards\CardActions;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\Avatar;
use App\Http\Controllers\extended_ui\BlockUI;
use App\Http\Controllers\extended_ui\DragAndDrop;
use App\Http\Controllers\extended_ui\MediaPlayer;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\StarRatings;
use App\Http\Controllers\extended_ui\SweetAlert;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\extended_ui\TimelineBasic;
use App\Http\Controllers\extended_ui\TimelineFullscreen;
use App\Http\Controllers\extended_ui\Tour;
use App\Http\Controllers\extended_ui\Treeview;
use App\Http\Controllers\extended_ui\Misc;
use App\Http\Controllers\icons\MdiIcons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_elements\CustomOptions;
use App\Http\Controllers\form_elements\Editors;
use App\Http\Controllers\form_elements\FileUpload;
use App\Http\Controllers\form_elements\Picker;
use App\Http\Controllers\form_elements\Selects;
use App\Http\Controllers\form_elements\Sliders;
use App\Http\Controllers\form_elements\Switches;
use App\Http\Controllers\form_elements\Extras;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\form_layouts\StickyActions;
use App\Http\Controllers\form_wizard\Numbered as FormWizardNumbered;
use App\Http\Controllers\form_wizard\Icons as FormWizardIcons;
use App\Http\Controllers\form_validation\Validation;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Controllers\tables\DatatableBasic;
use App\Http\Controllers\tables\DatatableAdvanced;
use App\Http\Controllers\tables\DatatableExtensions;
use App\Http\Controllers\charts\ApexCharts;
use App\Http\Controllers\charts\ChartJs;
use App\Http\Controllers\maps\Leaflet;

//======================================================================================================================
//cache
Route::get('/cc', function () {
  Artisan::call('cache:clear');
  Artisan::call('view:clear');
  Artisan::call('route:clear');
  Artisan::call('config:cache');
  // return what you want
  die('cache clear ok...');
});

//======================================================================================================================
//tastevn
use Illuminate\Http\Request;
use App\Api\SysApp;
use App\Http\Controllers\tastevn\ApiController;
use App\Http\Controllers\tastevn\LoginController;
use App\Http\Controllers\tastevn\GuideController;
use App\Http\Controllers\tastevn\PrinterController;
use App\Http\Controllers\tastevn\ErrorController;
use App\Http\Controllers\tastevn\TesterController;
use App\Http\Controllers\tastevn\auth\RestaurantController;
use App\Http\Controllers\tastevn\auth\SensorController;
use App\Http\Controllers\tastevn\auth\NotificationController;
use App\Http\Controllers\tastevn\auth\LogController;
use App\Http\Controllers\tastevn\auth\TextController;
use App\Http\Controllers\tastevn\auth\SettingController;
use App\Http\Controllers\tastevn\auth\UserController;
use App\Http\Controllers\tastevn\auth\IngredientController;
use App\Http\Controllers\tastevn\auth\FoodCategoryController;
use App\Http\Controllers\tastevn\auth\PhotoController;
use App\Http\Controllers\tastevn\auth\CommentController;
use App\Http\Controllers\tastevn\auth\FoodController;
use App\Http\Controllers\tastevn\auth\RoboflowController;
use App\Http\Controllers\tastevn\auth\ReportController;
use App\Http\Controllers\tastevn\auth\KasController;

//apix
Route::get('/export/food/ingredients', [ApiController::class, 'food_ingredient']);
Route::get('/food/datas', [ApiController::class, 'food_datas']);
//auth
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/auth/login', [LoginController::class, 'login_auth']);
Route::post('/auth/send-code', [LoginController::class, 'send_code']);
Route::post('/auth/update-pwd', [LoginController::class, 'update_pwd']);
Route::post('/auth/logout', [LoginController::class, 'logout'])->name('logout');
//tester
Route::get('/tester', [TesterController::class, 'index']);
Route::post('/tester/post', [TesterController::class, 'tester_post']);
Route::post('/tester/photo/check', [TesterController::class, 'tester_photo_check']);
//guide
Route::get('/guide/printer', [GuideController::class, 'printer']);
Route::get('/guide/speaker', [GuideController::class, 'speaker']);
//printer
Route::get('/printer', [PrinterController::class, 'index']);
Route::get('/printer/test', [PrinterController::class, 'test']);
//error
Route::get('/error/404', [ErrorController::class, 'index']);
Route::get('/error/photo/check', [ErrorController::class, 'photo_check']);
Route::post('/error/photo/scan', [ErrorController::class, 'photo_rescan']);
//admin
Route::get('/', [SensorController::class, 'index']);
Route::get('/admin', [SensorController::class, 'index']);
//restaurant (restaurant_parent) //add later
Route::get('/admin/restaurants', [RestaurantController::class, 'index']);
Route::post('/admin/restaurant/store', [RestaurantController::class, 'store']);
Route::post('/admin/restaurant/update', [RestaurantController::class, 'update']);
Route::post('/admin/restaurant/delete', [RestaurantController::class, 'delete']);
Route::post('/admin/restaurant/restore', [RestaurantController::class, 'restore']);
Route::post('/admin/restaurant/selectize', [RestaurantController::class, 'selectize']);
Route::post('/admin/restaurant/info', [RestaurantController::class, 'info']);
Route::get('/admin/restaurant/foods', [RestaurantController::class, 'foods']);
Route::post('/admin/restaurant/food/sync', [RestaurantController::class, 'food_sync']);
Route::post('/admin/restaurant/food/serve', [RestaurantController::class, 'food_serve']);
Route::post('/admin/restaurant/food/add', [RestaurantController::class, 'food_add']);
Route::post('/admin/restaurant/food/get', [RestaurantController::class, 'food_get']);
Route::post('/admin/restaurant/food/remove', [RestaurantController::class, 'food_remove']);
Route::post('/admin/restaurant/food/core', [RestaurantController::class, 'food_core']);
Route::post('/admin/restaurant/food/confidence', [RestaurantController::class, 'food_confidence']);
Route::post('/admin/restaurant/food/photo', [RestaurantController::class, 'food_photo']);
Route::post('/admin/restaurant/food/update', [RestaurantController::class, 'food_update']);
Route::post('/admin/restaurant/food/ingredient/get', [RestaurantController::class, 'food_ingredient_get']);
Route::post('/admin/restaurant/food/ingredient/update', [RestaurantController::class, 'food_ingredient_update']);

//sensor (restaurant)
Route::get('/admin/sensors', [SensorController::class, 'index']);
Route::post('/admin/sensor/store', [SensorController::class, 'store']);
Route::post('/admin/sensor/update', [SensorController::class, 'update']);
Route::post('/admin/sensor/delete', [SensorController::class, 'delete']);
Route::post('/admin/sensor/restore', [SensorController::class, 'restore']);
Route::post('/admin/sensor/selectize', [SensorController::class, 'selectize']);
Route::get('/admin/sensor/info/{id}', [SensorController::class, 'show']);
Route::post('/admin/sensor/stats', [SensorController::class, 'stats']);
Route::post('/admin/sensor/food/scan/get', [SensorController::class, 'food_scan_get']);
Route::post('/admin/sensor/food/scan/update', [SensorController::class, 'food_scan_update']);
Route::post('/admin/sensor/food/scan/delete', [SensorController::class, 'food_scan_delete']);
Route::post('/admin/sensor/food/scan/api', [SensorController::class, 'food_scan_api']);
Route::post('/admin/sensor/food/scan/info', [SensorController::class, 'food_scan_info']);
Route::post('/admin/sensor/food/scan/error', [SensorController::class, 'food_scan_error']);
Route::post('/admin/sensor/food/scan/get/food', [SensorController::class, 'food_scan_get_food']);
Route::post('/admin/sensor/food/scan/resolve', [SensorController::class, 'food_scan_resolve']);
Route::post('/admin/sensor/food/scan/mark', [SensorController::class, 'food_scan_mark']);
Route::post('/admin/sensor/food/scan/view', [SensorController::class, 'food_scan_view']);
Route::get('/admin/kitchen/{id}', [SensorController::class, 'kitchen']);
Route::post('/admin/kitchen/checker', [SensorController::class, 'kitchen_checker']);
Route::post('/admin/kitchen/predict', [SensorController::class, 'kitchen_predict']);
Route::get('/admin/kitchens', [SensorController::class, 'kitchens']);
Route::get('/admin/sse/stream/kitchen/{id}', [SensorController::class, 'sse_stream_kitchen']);
//notify
Route::get('/admin/notifications', [NotificationController::class, 'index']);
Route::post('/admin/notification/read', [NotificationController::class, 'notification_read']);
Route::post('/admin/notification/read/all', [NotificationController::class, 'notification_read_all']);
Route::post('/admin/notification/latest', [NotificationController::class, 'notification_latest']);
Route::post('/admin/notification/newest', [NotificationController::class, 'notification_newest']);
//log
Route::get('/admin/logs', [LogController::class, 'index']);
//text
Route::get('/admin/texts', [TextController::class, 'index']);
Route::post('/admin/text/store', [TextController::class, 'store']);
Route::post('/admin/text/update', [TextController::class, 'update']);
Route::post('/admin/text/create', [TextController::class, 'create']);
Route::post('/admin/text/selectize', [TextController::class, 'selectize']);
//setting
Route::get('/admin/settings', [SettingController::class, 'index']);
Route::post('/admin/setting/update', [SettingController::class, 'update']);
//user
Route::get('/admin/profile', [UserController::class, 'profile']);
Route::post('/admin/profile/update', [UserController::class, 'profile_update']);
Route::post('/admin/profile/pwd/code', [UserController::class, 'profile_pwd_code']);
Route::post('/admin/profile/pwd/update', [UserController::class, 'profile_pwd_update']);
Route::get('/admin/profile/setting', [UserController::class, 'profile_setting']);
Route::post('/admin/profile/setting/update', [UserController::class, 'profile_setting_update']);
Route::post('/admin/profile/setting/notify', [UserController::class, 'profile_setting_notify']);
Route::get('/admin/users', [UserController::class, 'index']);
Route::post('/admin/user/store', [UserController::class, 'store']);
Route::post('/admin/user/update', [UserController::class, 'update']);
Route::post('/admin/user/delete', [UserController::class, 'delete']);
Route::post('/admin/user/restore', [UserController::class, 'restore']);
Route::post('/admin/user/selectize', [UserController::class, 'selectize']);
Route::post('/admin/user/zalo/user/update', [UserController::class, 'zalo_user_update']);
Route::post('/admin/user/zalo/message/send', [UserController::class, 'zalo_message_send']);
//ingredient
Route::get('/admin/ingredients', [IngredientController::class, 'index']);
Route::post('/admin/ingredient/store', [IngredientController::class, 'store']);
Route::post('/admin/ingredient/update', [IngredientController::class, 'update']);
Route::post('/admin/ingredient/create', [IngredientController::class, 'create']);
Route::post('/admin/ingredient/selectize', [IngredientController::class, 'selectize']);
//category
Route::get('/admin/food-categories', [FoodCategoryController::class, 'index']);
Route::post('/admin/food-category/store', [FoodCategoryController::class, 'store']);
Route::post('/admin/food-category/update', [FoodCategoryController::class, 'update']);
Route::post('/admin/food-category/create', [FoodCategoryController::class, 'create']);
Route::post('/admin/food-category/selectize', [FoodCategoryController::class, 'selectize']);
//photo
Route::get('/admin/photos', [PhotoController::class, 'index']);
Route::post('/admin/photo/get', [PhotoController::class, 'get']);
Route::post('/admin/photo/view', [PhotoController::class, 'view']);
Route::post('/admin/photo/note/get', [PhotoController::class, 'note_get']);
//comment
Route::post('/admin/comment/note', [CommentController::class, 'note']);
//kas
Route::get('/admin/kas/foods', [KasController::class, 'index']);
Route::post('/admin/kas/food/item', [KasController::class, 'food_item']);
Route::post('/admin/kas/food/get', [KasController::class, 'food_get']);
Route::get('/admin/kas/checker', [KasController::class, 'checker']);
Route::post('/admin/kas/date/check', [KasController::class, 'date_check']);
Route::post('/admin/kas/date/check/month', [KasController::class, 'date_check_month']);
Route::post('/admin/kas/date/check/restaurant', [KasController::class, 'date_check_restaurant']);
Route::post('/admin/kas/date/check/restaurant/photo', [KasController::class, 'date_check_restaurant_photo']);
Route::post('/admin/kas/date/check/restaurant/hour', [KasController::class, 'date_check_restaurant_hour']);
//food
Route::get('/admin/foods', [FoodController::class, 'index']);
Route::post('/admin/food/get', [FoodController::class, 'get']);
Route::post('/admin/food/get/info', [FoodController::class, 'get_info']);
Route::post('/admin/food/get/ingredient', [FoodController::class, 'get_ingredient']);
Route::post('/admin/food/get/recipe', [FoodController::class, 'get_recipe']);
Route::post('/admin/food/ingredient/html', [FoodController::class, 'ingredient_html']);
Route::post('/admin/food/recipe/html', [FoodController::class, 'recipe_html']);
Route::post('/admin/food/store', [FoodController::class, 'store']);
Route::post('/admin/food/update', [FoodController::class, 'update']);
Route::post('/admin/food/update/ingredient', [FoodController::class, 'update_ingredient']);
Route::post('/admin/food/update/recipe', [FoodController::class, 'update_recipe']);
Route::post('/admin/food/import', [FoodController::class, 'import']);
Route::post('/admin/food/import/recipe', [FoodController::class, 'import_recipe']);
Route::post('/admin/food/selectize', [FoodController::class, 'selectize']);
//roboflow
Route::post('/admin/roboflow/retraining', [RoboflowController::class, 'retraining']);
Route::get('/admin/roboflow', [RoboflowController::class, 'index']);
Route::post('/admin/roboflow/detect', [RoboflowController::class, 'detect']);
Route::post('/admin/roboflow/restaurant/food/get', [RoboflowController::class, 'restaurant_food_get']);
Route::post('/admin/roboflow/food/get/info', [RoboflowController::class, 'food_get_info']);
//report
Route::get('/admin/reports', [ReportController::class, 'index']);
Route::post('/admin/report/store', [ReportController::class, 'store']);
Route::post('/admin/report/update', [ReportController::class, 'update']);
Route::post('/admin/report/delete', [ReportController::class, 'delete']);
Route::get('/admin/report/info/{id}', [ReportController::class, 'show']);
Route::post('/admin/report/table', [ReportController::class, 'table']);
Route::post('/admin/report/start', [ReportController::class, 'start']);
Route::post('/admin/report/photo/not-found', [ReportController::class, 'photo_not_found']);
Route::post('/admin/report/photo/update', [ReportController::class, 'photo_update']);
Route::post('/admin/report/photo/clear', [ReportController::class, 'photo_clear']);
Route::post('/admin/report/photo/food', [ReportController::class, 'photo_food']);
Route::post('/admin/report/photo/rfs', [ReportController::class, 'photo_rfs']);
//datatable
Route::get('/datatable/report', function (Request $request) {
  $values = $request->all();

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $user = \Illuminate\Support\Facades\Auth::user();

  $select = App\Models\Report::query("reports")
    ->select("reports.id", "reports.name", "reports.status",
      "reports.date_from", "reports.date_to", "reports.total_photos", "reports.total_points", "reports.total_foods",
      "reports.restaurant_parent_id", "restaurant_parents.name as restaurant_name",
    )
    ->leftJoin('restaurant_parents', 'restaurant_parents.id', '=', 'reports.restaurant_parent_id')
    ->where('reports.deleted', 0);

  if ($order_default) {
    $select->orderBy('reports.updated_at', 'desc')
      ->orderBy('reports.id', 'desc');
  }

  if (count($values)) {
    if (isset($values['name']) && !empty($values['name'])) {
      $select->where('reports.name', 'LIKE', '%' . $values['name'] . '%');
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/restaurant', function (Request $request) {
  $values = $request->all();

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $user = \Illuminate\Support\Facades\Auth::user();

  $select = App\Models\RestaurantParent::query("restaurant_parents")
    ->select("restaurant_parents.id", "restaurant_parents.name",
      "restaurant_parents.model_name", "restaurant_parents.model_version", "restaurant_parents.model_scan",
      "restaurant_parents.count_sensors", "restaurant_parents.count_foods",
      "restaurant_parents.updated_at")
    ->where('restaurant_parents.deleted', 0);

  if ($order_default) {
    $select->orderBy('restaurant_parents.updated_at', 'desc')
      ->orderBy('restaurant_parents.id', 'desc');
  }

  $user_roles = ['user', 'moderator'];
  if ($user && in_array($user->role, $user_roles) && !$user->access_full) {
    $select->leftJoin('restaurant_access', 'restaurant_access.restaurant_parent_id', '=', 'restaurant_parents.id')
      ->where('restaurant_parents.deleted', 0)
      ->where('restaurant_access.user_id', $user->id);
  }

  if (count($values)) {
    if (isset($values['name']) && !empty($values['name'])) {
      $select->where('restaurant_parents.name', 'LIKE', '%' . $values['name'] . '%');
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/sensor', function (Request $request) {
  $values = $request->all();

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $user = \Illuminate\Support\Facades\Auth::user();

  $select = App\Models\Restaurant::query("restaurants")
    ->select("restaurants.id", "restaurants.name", "restaurants.restaurant_parent_id",
      "restaurants.s3_bucket_name", "restaurants.s3_bucket_address", "restaurants.rbf_scan",
      "restaurants.updated_at")
    ->where('restaurants.deleted', 0);

  if ($order_default) {
    $select->orderBy('restaurants.updated_at', 'desc')
      ->orderBy('restaurants.id', 'desc');
  }

  $user_roles = ['user', 'moderator'];
  if ($user && in_array($user->role, $user_roles) && !$user->access_full) {
    $select->leftJoin('restaurant_access', 'restaurant_access.restaurant_parent_id', '=', 'restaurants.restaurant_parent_id')
      ->where('restaurants.deleted', 0)
      ->where('restaurant_access.user_id', $user->id);
  }

  if (count($values)) {
    if (isset($values['name']) && !empty($values['name'])) {
      $select->where('restaurants.name', 'LIKE', '%' . $values['name'] . '%');
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/sensor-food-scans', function (Request $request) {
  $values = $request->all();
  $sys_app = new SysApp();
//echo '<pre>';var_dump($values);die;

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $user = \Illuminate\Support\Facades\Auth::user();

  $restaurant = isset($values['restaurant']) ? (int)$values['restaurant'] : 0;
  $statuses = isset($values['statuses']) && !empty($values['statuses']) ? $values['statuses'] : NULL;
  $missing = isset($values['missing']) && !empty($values['missing']) ? $values['missing'] : NULL;
  $resolved = isset($values['resolved']) && !empty($values['resolved']) ? $values['resolved'] : NULL;
  $marked = isset($values['marked']) && !empty($values['marked']) ? $values['marked'] : NULL;
  $customer_requested = isset($values['customer_requested']) && !empty($values['customer_requested']) ? $values['customer_requested'] : NULL;
  $food_multi = isset($values['food_multi']) && !empty($values['food_multi']) ? $values['food_multi'] : NULL;
  $noted = isset($values['noted']) && !empty($values['noted']) ? $values['noted'] : NULL;
  $food_catetories = isset($values['categories']) ? (array)$values['categories'] : [];
  $foods = isset($values['foods']) ? (array)$values['foods'] : [];
  $users = isset($values['users']) ? (array)$values['users'] : [];
  $time_upload = isset($values['time_upload']) && !empty($values['time_upload']) ? $values['time_upload'] : NULL;
  $time_scan = isset($values['time_scan']) && !empty($values['time_scan']) ? $values['time_scan'] : NULL;

  $select = App\Models\RestaurantFoodScan::query("restaurant_food_scans")
    ->select("restaurant_food_scans.id", "restaurant_food_scans.photo_url", "restaurant_food_scans.photo_name", "restaurant_food_scans.local_storage",
      "restaurant_food_scans.time_scan", "restaurant_food_scans.time_photo", "restaurant_food_scans.time_end",
      "restaurant_food_scans.missing_texts", "restaurant_food_scans.text_texts",
      "restaurant_food_scans.status", "restaurant_food_scans.found_by", "restaurant_food_scans.confidence",
      "restaurant_food_scans.note", "restaurant_food_scans.note_kitchen",
      "restaurant_food_scans.food_id", "restaurant_food_scans.food_category_id", "restaurant_food_scans.rbf_retrain",
      "foods.name as food_name", "food_categories.name as category_name",
      "restaurant_food_scans.is_resolved", "restaurant_food_scans.is_marked", "restaurant_food_scans.rbf_error",
      "restaurant_food_scans.customer_requested", "restaurant_food_scans.count_foods",
    )
    ->leftJoin("foods", "restaurant_food_scans.food_id", "=", "foods.id")
    ->leftJoin("food_categories", "restaurant_food_scans.food_category_id", "=", "food_categories.id")
    ->where("restaurant_food_scans.deleted", 0);

  if ($user->is_dev()) {

  } else {
    $select->whereIn("restaurant_food_scans.status", [
      'checked', 'edited', 'failed',
    ]);
  }

  if ($order_default) {
    $select
      ->orderBy('restaurant_food_scans.time_photo', 'desc')
      ->orderBy('restaurant_food_scans.id', 'desc');
  }

  if ($restaurant) {
    $select->where("restaurant_food_scans.restaurant_id", $restaurant);

    //super-confidence only
    $sensor = App\Models\Restaurant::find($restaurant);
    $restaurant_parent = $sensor->get_parent();

    $select->where('status', '<>', 'duplicated');

    if (!empty($statuses)) {
      switch ($statuses) {
        case 'group_1':
          $select->whereIn("restaurant_food_scans.food_id", $restaurant_parent->get_foods([
            'live_group' => 1,
            'select_data' => 'food_ids',
          ]));
          break;

        case 'group_2':
          $select->whereIn("restaurant_food_scans.food_id", $restaurant_parent->get_foods([
            'live_group' => 2,
            'select_data' => 'food_ids',
          ]));
          break;

        case 'group_3':

          $select->where(function ($q) use ($restaurant_parent) {
            $q->whereIn("restaurant_food_scans.food_id", $restaurant_parent->get_foods([
              'live_group' => 3,
              'select_data' => 'food_ids',
            ]))
              ->orWhere(function ($q2) {
                $q2->whereIn('status', ['failed', 'checked'])
                  ->where('food_id', 0);
              });
          });
          break;
      }
    }
  }
  if (count($food_catetories)) {
    $select->whereIn("restaurant_food_scans.food_category_id", $food_catetories);
  }
  if (count($foods)) {
    $select->whereIn("restaurant_food_scans.food_id", $foods);
  }
  if (count($users)) {
    $select->whereIn("restaurant_food_scans.id", function ($q) use ($users) {
      $q->select('object_id')
        ->distinct()
        ->from('comments')
        ->where('object_type', 'restaurant_food_scan')
        ->whereIn('user_id', $users);
    });
  }
  if (!empty($time_upload)) {
    $times = $sys_app->parse_date_range($time_upload);
    if (!empty($times['time_from'])) {
      $select->where('restaurant_food_scans.time_photo', '>=', $times['time_from']);
    }
    if (!empty($times['time_to'])) {
      $select->where('restaurant_food_scans.time_photo', '<=', $times['time_to']);
    }
  }
  if (!empty($missing)) {
    switch ($missing) {
      case 'yes':
        $select->where("restaurant_food_scans.missing_ids", '<>', NULL);
        break;

      case 'no':
        $select->where("restaurant_food_scans.missing_ids", NULL);
        break;
    }
  }
  if (!empty($resolved)) {
    switch ($resolved) {
      case 'yes':
        $select->where("restaurant_food_scans.is_resolved", '>', 0);
        break;
    }
  }
  if (!empty($marked)) {
    switch ($marked) {
      case 'yes':
        $select->where("restaurant_food_scans.is_marked", '>', 0);
        break;
    }
  }
  if (!empty($customer_requested)) {
    switch ($customer_requested) {
      case 'yes':
        $select->where("restaurant_food_scans.customer_requested", '>', 0);
        break;
    }
  }
  if (!empty($food_multi)) {
    switch ($food_multi) {
      case 'yes':
        $select->where("restaurant_food_scans.count_foods", '>', 0);
        break;
    }
  }
  if (!empty($noted)) {
    switch ($noted) {
      case 'yes':
        $select->where(function ($q) {
          $q->where('restaurant_food_scans.note', '<>', NULL)
            ->orWhereIn("restaurant_food_scans.id", function ($q1) {
            $q1->select('object_id')
              ->distinct()
              ->from('comments')
              ->where('object_type', 'restaurant_food_scan')
              ->where('user_id', '>', 0);
          });
        });
        break;
    }
  }

//  echo '<pre>';var_dump($sys_app->parse_to_query($select));die;

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/sensor-food-scan-errors', function (Request $request) {
  $values = $request->all();
  $sys_app = new SysApp();
//echo '<pre>';var_dump($values);die;

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $restaurant = isset($values['restaurant']) ? (int)$values['restaurant'] : 0;
  $food_catetories = isset($values['categories']) ? (array)$values['categories'] : [];
  $foods = isset($values['foods']) ? (array)$values['foods'] : [];
  $time_upload = isset($values['time_upload']) && !empty($values['time_upload']) ? $values['time_upload'] : NULL;
  $time_scan = isset($values['time_scan']) && !empty($values['time_scan']) ? $values['time_scan'] : NULL;

  $select = App\Models\RestaurantFoodScan::query('restaurant_food_scans')
    ->leftJoin('foods', 'foods.id', '=', 'restaurant_food_scans.food_id')
    ->leftJoin('food_categories', 'food_categories.id', '=', 'restaurant_food_scans.food_category_id')
    ->select('restaurant_food_scans.food_id', 'restaurant_food_scans.missing_ids', 'restaurant_food_scans.missing_texts', 'foods.name as food_name', 'food_categories.name as food_category_name')
    ->selectRaw('COUNT(restaurant_food_scans.id) as total_error')
    ->where('restaurant_food_scans.deleted', 0)
    ->where('restaurant_food_scans.food_id', '>', 0)
    ->where('restaurant_food_scans.missing_ids', '<>', NULL)
    ->whereIn("restaurant_food_scans.status", [
      'checked', 'edited', 'failed',
    ])
    ->groupBy([
      'restaurant_food_scans.food_id',
      'restaurant_food_scans.missing_ids', 'restaurant_food_scans.missing_texts',
      'foods.name', 'food_categories.name'
    ]);

  if ($order_default) {
    $select->orderBy('total_error', 'desc');
  }

  if ($restaurant) {
    $select->where("restaurant_food_scans.restaurant_id", $restaurant);
  }
  if (count($food_catetories)) {
    $select->whereIn("restaurant_food_scans.food_category_id", $food_catetories);
  }
  if (count($foods)) {
    $select->whereIn("restaurant_food_scans.food_id", $foods);
  }

  if (!empty($time_upload)) {
    $times = $sys_app->parse_date_range($time_upload);
    if (!empty($times['time_from'])) {
      $select->where('restaurant_food_scans.time_photo', '>=', $times['time_from']);
    }
    if (!empty($times['time_to'])) {
      $select->where('restaurant_food_scans.time_photo', '<=', $times['time_to']);
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/logs', function (Request $request) {
  $values = $request->all();
  $sys_app = new SysApp();

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $users = isset($values['users']) ? (array)$values['users'] : [];
  $types = isset($values['types']) ? (array)$values['types'] : [];
  $restaurants = isset($values['restaurants']) ? (array)$values['restaurants'] : [];
  $items = isset($values['items']) ? (array)$values['items'] : [];
  $time_created = isset($values['time_created']) && !empty($values['time_created']) ? $values['time_created'] : NULL;

  $select = App\Models\Log::query();

  if ($order_default) {
    $select->orderBy('id', 'desc');
  }

  if (count($users)) {
    $select->whereIn("user_id", $users);
  }
  if (count($types)) {
    $select->whereIn("type", $types);
  }
  if (count($restaurants)) {
    $select->whereIn("restaurant_id", $restaurants);
  }
  if (count($items)) {
    $select->whereIn("item_type", $items);
  }
  if (!empty($time_created)) {
    $times = $sys_app->parse_date_range($time_created);
    if (!empty($times['time_from'])) {
      $select->where('created_at', '>=', $times['time_from']);
    }
    if (!empty($times['time_to'])) {
      $select->where('created_at', '<=', $times['time_to']);
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/texts', function (Request $request) {
  $values = $request->all();

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $select = App\Models\Text::query();

  if ($order_default) {
    $select->orderBy('updated_at', 'desc')
      ->orderBy('id', 'desc');
  }

  if (count($values)) {
    if (isset($values['name']) && !empty($values['name'])) {
      $select->where('name', 'LIKE', '%' . $values['name'] . '%');
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/ingredients', function (Request $request) {
  $values = $request->all();

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $select = App\Models\Ingredient::query();

  if ($order_default) {
    $select->orderBy('updated_at', 'desc')
      ->orderBy('id', 'desc');
  }

  if (count($values)) {
    if (isset($values['name']) && !empty($values['name'])) {
      $select->where(function ($q) use ($values) {
        $q->where('name', 'LIKE', '%' . $values['name'] . '%')
          ->orWhere('name_vi', 'LIKE', '%' . $values['name'] . '%');
      });
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/food-categories', function (Request $request) {
  $values = $request->all();

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $select = App\Models\FoodCategory::query();

  if ($order_default) {
    $select->orderBy('updated_at', 'desc')
      ->orderBy('id', 'desc');
  }

  if (count($values)) {
    if (isset($values['name']) && !empty($values['name'])) {
      $select->where('name', 'LIKE', '%' . $values['name'] . '%');
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/user', function (Request $request) {
  $values = $request->all();

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $select = App\Models\User::query('users')
    ->select('users.id', 'users.name', 'users.email', 'users.phone', 'users.status',
      'users.role', 'users.note', 'users.updated_at',
      'users.access_full', 'users.access_ids', 'users.access_texts',
      'zalo_users.id as zalo_id', 'zalo_users.zalo_user_id',
    )
    ->leftJoin('zalo_users', 'zalo_users.user_id', '=', 'users.id')
    ->where('users.deleted', 0)
    ->where('users.role', '<>', 'superadmin') //superadmin
  ;

  if ($order_default) {
    $select->orderBy('users.updated_at', 'desc')
      ->orderBy('users.id', 'desc');
  }

  if (count($values)) {
    if (isset($values['name']) && !empty($values['name'])) {
      $select->where('users.name', 'LIKE', '%' . $values['name'] . '%');
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/foods', function (Request $request) {
  $values = $request->all();

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $select = App\Models\Food::query()
    ->where('deleted', 0);

  if ($order_default) {
    $select->orderBy('updated_at', 'desc')
      ->orderBy('id', 'desc');
  }

  if (count($values)) {
    if (isset($values['name']) && !empty($values['name'])) {
      $select->where('name', 'LIKE', '%' . $values['name'] . '%');
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});
Route::get('/datatable/kas/foods', function (Request $request) {
  $values = $request->all();

  $order_default = true;
  if (isset($values['order']) && count($values['order']) && isset($values['order'][0])) {
    if (isset($values['order'][0]['column']) && (int)$values['order'][0]['column']) {
      $order_default = false;
    }
  }

  $select = App\Models\KasItem::query();

  if ($order_default) {
    $select
      ->orderBy('food_id', 'desc')
      ->orderBy('updated_at', 'desc')
      ->orderBy('id', 'desc');
  }

  if (count($values)) {
    if (isset($values['name']) && !empty($values['name'])) {
      $select->where('item_name', 'LIKE', '%' . $values['name'] . '%');
    }
  }

  return DataTables::of($select)->addIndexColumn()->toJson();
});

//======================================================================================================================
//======================================================================================================================
//theme material ui
// Main Page Route
//custome
//Route::get('/', [Analytics::class, 'index'])->name('dashboard-analytics');
Route::get('/dashboard/analytics', [Analytics::class, 'index'])->name('dashboard-analytics');
Route::get('/dashboard/crm', [Crm::class, 'index'])->name('dashboard-crm');
// locale
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// layout
Route::get('/layouts/collapsed-menu', [CollapsedMenu::class, 'index'])->name('layouts-collapsed-menu');
Route::get('/layouts/content-navbar', [ContentNavbar::class, 'index'])->name('layouts-content-navbar');
Route::get('/layouts/content-nav-sidebar', [ContentNavSidebar::class, 'index'])->name('layouts-content-nav-sidebar');
Route::get('/layouts/horizontal', [Horizontal::class, 'index'])->name('dashboard-analytics');
Route::get('/layouts/vertical', [Vertical::class, 'index'])->name('dashboard-analytics');
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// Front Pages
Route::get('/front-pages/landing', [Landing::class, 'index'])->name('front-pages-landing');
Route::get('/front-pages/pricing', [Pricing::class, 'index'])->name('front-pages-pricing');
Route::get('/front-pages/payment', [Payment::class, 'index'])->name('front-pages-payment');
Route::get('/front-pages/checkout', [Checkout::class, 'index'])->name('front-pages-checkout');
Route::get('/front-pages/help-center', [HelpCenter::class, 'index'])->name('front-pages-help-center');
Route::get('/front-pages/help-center-article', [HelpCenterArticle::class, 'index'])->name('front-pages-help-center-article');

// apps
Route::get('/app/email', [Email::class, 'index'])->name('app-email');
Route::get('/app/chat', [Chat::class, 'index'])->name('app-chat');
Route::get('/app/calendar', [Calendar::class, 'index'])->name('app-calendar');
Route::get('/app/kanban', [Kanban::class, 'index'])->name('app-kanban');
Route::get('/app/ecommerce/dashboard', [EcommerceDashboard::class, 'index'])->name('app-ecommerce-dashboard');
Route::get('/app/ecommerce/product/list', [EcommerceProductList::class, 'index'])->name('app-ecommerce-product-list');
Route::get('/app/ecommerce/product/add', [EcommerceProductAdd::class, 'index'])->name('app-ecommerce-product-add');
Route::get('/app/ecommerce/product/category', [EcommerceProductCategory::class, 'index'])->name('app-ecommerce-product-category');
Route::get('/app/ecommerce/order/list', [EcommerceOrderList::class, 'index'])->name('app-ecommerce-order-list');
Route::get('app/ecommerce/order/details', [EcommerceOrderDetails::class, 'index'])->name('app-ecommerce-order-details');
Route::get('/app/ecommerce/customer/all', [EcommerceCustomerAll::class, 'index'])->name('app-ecommerce-customer-all');
Route::get('app/ecommerce/customer/details/overview', [EcommerceCustomerDetailsOverview::class, 'index'])->name('app-ecommerce-customer-details-overview');
Route::get('app/ecommerce/customer/details/security', [EcommerceCustomerDetailsSecurity::class, 'index'])->name('app-ecommerce-customer-details-security');
Route::get('app/ecommerce/customer/details/billing', [EcommerceCustomerDetailsBilling::class, 'index'])->name('app-ecommerce-customer-details-billing');
Route::get('app/ecommerce/customer/details/notifications', [EcommerceCustomerDetailsNotifications::class, 'index'])->name('app-ecommerce-customer-details-notifications');
Route::get('/app/ecommerce/manage/reviews', [EcommerceManageReviews::class, 'index'])->name('app-ecommerce-manage-reviews');
Route::get('/app/ecommerce/referrals', [EcommerceReferrals::class, 'index'])->name('app-ecommerce-referrals');
Route::get('/app/ecommerce/settings/details', [EcommerceSettingsDetails::class, 'index'])->name('app-ecommerce-settings-details');
Route::get('/app/ecommerce/settings/payments', [EcommerceSettingsPayments::class, 'index'])->name('app-ecommerce-settings-payments');
Route::get('/app/ecommerce/settings/checkout', [EcommerceSettingsCheckout::class, 'index'])->name('app-ecommerce-settings-checkout');
Route::get('/app/ecommerce/settings/shipping', [EcommerceSettingsShipping::class, 'index'])->name('app-ecommerce-settings-shipping');
Route::get('/app/ecommerce/settings/locations', [EcommerceSettingsLocations::class, 'index'])->name('app-ecommerce-settings-locations');
Route::get('/app/ecommerce/settings/notifications', [EcommerceSettingsNotifications::class, 'index'])->name('app-ecommerce-settings-notifications');
Route::get('/app/academy/dashboard', [AcademyDashboard::class, 'index'])->name('app-academy-dashboard');
Route::get('/app/academy/course', [AcademyCourse::class, 'index'])->name('app-academy-course');
Route::get('/app/academy/course-details', [AcademyCourseDetails::class, 'index'])->name('app-academy-course-details');
Route::get('/app/logistics/dashboard', [LogisticsDashboard::class, 'index'])->name('app-logistics-dashboard');
Route::get('/app/logistics/fleet', [LogisticsFleet::class, 'index'])->name('app-logistics-fleet');
Route::get('/app/invoice/list', [InvoiceList::class, 'index'])->name('app-invoice-list');
Route::get('/app/invoice/preview', [InvoicePreview::class, 'index'])->name('app-invoice-preview');
Route::get('/app/invoice/print', [InvoicePrint::class, 'index'])->name('app-invoice-print');
Route::get('/app/invoice/edit', [InvoiceEdit::class, 'index'])->name('app-invoice-edit');
Route::get('/app/invoice/add', [InvoiceAdd::class, 'index'])->name('app-invoice-add');
Route::get('/app/user/list', [UserList::class, 'index'])->name('app-user-list');
Route::get('/app/user/view/account', [UserViewAccount::class, 'index'])->name('app-user-view-account');
Route::get('/app/user/view/security', [UserViewSecurity::class, 'index'])->name('app-user-view-security');
Route::get('/app/user/view/billing', [UserViewBilling::class, 'index'])->name('app-user-view-billing');
Route::get('/app/user/view/notifications', [UserViewNotifications::class, 'index'])->name('app-user-view-notifications');
Route::get('/app/user/view/connections', [UserViewConnections::class, 'index'])->name('app-user-view-connections');
Route::get('/app/access-roles', [AccessRoles::class, 'index'])->name('app-access-roles');
Route::get('/app/access-permission', [AccessPermission::class, 'index'])->name('app-access-permission');

// pages
Route::get('/pages/profile-user', [UserProfile::class, 'index'])->name('pages-profile-user');
Route::get('/pages/profile-teams', [UserTeams::class, 'index'])->name('pages-profile-teams');
Route::get('/pages/profile-projects', [UserProjects::class, 'index'])->name('pages-profile-projects');
Route::get('/pages/profile-connections', [UserConnections::class, 'index'])->name('pages-profile-connections');
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-security', [AccountSettingsSecurity::class, 'index'])->name('pages-account-settings-security');
Route::get('/pages/account-settings-billing', [AccountSettingsBilling::class, 'index'])->name('pages-account-settings-billing');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/faq', [Faq::class, 'index'])->name('pages-faq');
Route::get('/pages/pricing', [PagesPricing::class, 'index'])->name('pages-pricing');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');
Route::get('/pages/misc-comingsoon', [MiscComingSoon::class, 'index'])->name('pages-misc-comingsoon');
Route::get('/pages/misc-not-authorized', [MiscNotAuthorized::class, 'index'])->name('pages-misc-not-authorized');
Route::get('/pages/misc-server-error', [MiscServerError::class, 'index'])->name('pages-misc-server-error');

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/login-cover', [LoginCover::class, 'index'])->name('auth-login-cover');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/register-cover', [RegisterCover::class, 'index'])->name('auth-register-cover');
Route::get('/auth/register-multisteps', [RegisterMultiSteps::class, 'index'])->name('auth-register-multisteps');
Route::get('/auth/verify-email-basic', [VerifyEmailBasic::class, 'index'])->name('auth-verify-email-basic');
Route::get('/auth/verify-email-cover', [VerifyEmailCover::class, 'index'])->name('auth-verify-email-cover');
Route::get('/auth/reset-password-basic', [ResetPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
Route::get('/auth/reset-password-cover', [ResetPasswordCover::class, 'index'])->name('auth-reset-password-cover');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
Route::get('/auth/forgot-password-cover', [ForgotPasswordCover::class, 'index'])->name('auth-forgot-password-cover');
Route::get('/auth/two-steps-basic', [TwoStepsBasic::class, 'index'])->name('auth-two-steps-basic');
Route::get('/auth/two-steps-cover', [TwoStepsCover::class, 'index'])->name('auth-two-steps-cover');

// wizard example
Route::get('/wizard/ex-checkout', [WizardCheckout::class, 'index'])->name('wizard-ex-checkout');
Route::get('/wizard/ex-property-listing', [PropertyListing::class, 'index'])->name('wizard-ex-property-listing');
Route::get('/wizard/ex-create-deal', [CreateDeal::class, 'index'])->name('wizard-ex-create-deal');

// modal
Route::get('/modal-examples', [ModalExample::class, 'index'])->name('modal-examples');

// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');
Route::get('/cards/advance', [CardAdvance::class, 'index'])->name('cards-advance');
Route::get('/cards/statistics', [CardStatistics::class, 'index'])->name('cards-statistics');
Route::get('/cards/analytics', [CardAnalytics::class, 'index'])->name('cards-analytics');
Route::get('/cards/gamifications', [CardGamifications::class, 'index'])->name('cards-gamifications');
Route::get('/cards/actions', [CardActions::class, 'index'])->name('cards-actions');

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-avatar', [Avatar::class, 'index'])->name('extended-ui-avatar');
Route::get('/extended/ui-blockui', [BlockUI::class, 'index'])->name('extended-ui-blockui');
Route::get('/extended/ui-drag-and-drop', [DragAndDrop::class, 'index'])->name('extended-ui-drag-and-drop');
Route::get('/extended/ui-media-player', [MediaPlayer::class, 'index'])->name('extended-ui-media-player');
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-star-ratings', [StarRatings::class, 'index'])->name('extended-ui-star-ratings');
Route::get('/extended/ui-sweetalert2', [SweetAlert::class, 'index'])->name('extended-ui-sweetalert2');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');
Route::get('/extended/ui-timeline-basic', [TimelineBasic::class, 'index'])->name('extended-ui-timeline-basic');
Route::get('/extended/ui-timeline-fullscreen', [TimelineFullscreen::class, 'index'])->name('extended-ui-timeline-fullscreen');
Route::get('/extended/ui-tour', [Tour::class, 'index'])->name('extended-ui-tour');
Route::get('/extended/ui-treeview', [Treeview::class, 'index'])->name('extended-ui-treeview');
Route::get('/extended/ui-misc', [Misc::class, 'index'])->name('extended-ui-misc');

// icons
Route::get('/icons/icons-mdi', [MdiIcons::class, 'index'])->name('icons-mdi');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');
Route::get('/forms/custom-options', [CustomOptions::class, 'index'])->name('forms-custom-options');
Route::get('/forms/editors', [Editors::class, 'index'])->name('forms-editors');
Route::get('/forms/file-upload', [FileUpload::class, 'index'])->name('forms-file-upload');
Route::get('/forms/pickers', [Picker::class, 'index'])->name('forms-pickers');
Route::get('/forms/selects', [Selects::class, 'index'])->name('forms-selects');
Route::get('/forms/sliders', [Sliders::class, 'index'])->name('forms-sliders');
Route::get('/forms/switches', [Switches::class, 'index'])->name('forms-switches');
Route::get('/forms/extras', [Extras::class, 'index'])->name('forms-extras');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');
Route::get('/form/layouts-sticky', [StickyActions::class, 'index'])->name('form-layouts-sticky');

// form wizards
Route::get('/form/wizard-numbered', [FormWizardNumbered::class, 'index'])->name('form-wizard-numbered');
Route::get('/form/wizard-icons', [FormWizardIcons::class, 'index'])->name('form-wizard-icons');
Route::get('/form/validation', [Validation::class, 'index'])->name('form-validation');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');
Route::get('/tables/datatables-basic', [DatatableBasic::class, 'index'])->name('tables-datatables-basic');
Route::get('/tables/datatables-advanced', [DatatableAdvanced::class, 'index'])->name('tables-datatables-advanced');
Route::get('/tables/datatables-extensions', [DatatableExtensions::class, 'index'])->name('tables-datatables-extensions');

// charts
Route::get('/charts/apex', [ApexCharts::class, 'index'])->name('charts-apex');
Route::get('/charts/chartjs', [ChartJs::class, 'index'])->name('charts-chartjs');

// maps
Route::get('/maps/leaflet', [Leaflet::class, 'index'])->name('maps-leaflet');

// laravel example
Route::get('/laravel/user-management', [UserManagement::class, 'UserManagement'])->name('laravel-example-user-management');
Route::resource('/user-list', UserManagement::class);
