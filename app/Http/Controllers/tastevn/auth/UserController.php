<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
//lib
use Validator;
use App\Notifications\ForgotPassword;
use App\Api\SysApp;
use App\Api\SysZalo;
//model
use App\Models\User;
use App\Models\RestaurantAccess;
use App\Models\PasswordResetToken;
use App\Models\ZaloUser;

class UserController extends Controller
{
  protected $_viewer = null;
  protected $_sys_app = null;

  public function __construct()
  {
    $this->_sys_app = new SysApp();

    $this->middleware(function ($request, $next) {

      $this->_viewer = Auth::user();

      return $next($request);
    });

    $this->middleware('auth');
  }

  public const _assigned_roles = ['moderator', 'user'];

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    if (in_array($this->_viewer->role, $this::_assigned_roles)) {
      return redirect('error/404');
    }

    //zalo
    $zalos = ZaloUser::select('id', 'display_name', 'user_alias', 'user_phone', 'avatar')
      ->where('display_name', '<>', NULL)
      ->orderByRaw('TRIM(LOWER(display_name))')
      ->get();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'zalos' => $zalos,
    ];

    $this->_viewer->add_log([
      'type' => 'view_listing_user',
    ]);

    return view('tastevn.pages.users', ['pageConfigs' => $pageConfigs]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'name' => 'required|string',
      'email' => 'required|email',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //restore
    $row = User::where('email', $values['email'])
      ->first();
    if ($row) {
      if ($row->deleted) {
        return response()->json([
          'type' => 'can_restored',
          'error' => 'Item deleted'
        ], 422);
      }
      //existed
      return response()->json([
        'error' => 'Email existed'
      ], 422);
    }

    $row = User::create([
      'name' => trim($values['name']),
      'email' => trim($values['email']),
      'password' => Hash::make('tastevietnam'),
      'phone' => $values['phone'],
      'status' => $values['status'],
      'role' => $values['role'],
      'note' => $values['note'],
      'creator_id' => $viewer->id,
      'access_full' => $values['role'] == 'admin' ? 1 : (int)$values['access_full'],
    ]);

    if (count($values['access_restaurants']) && in_array($values['role'], $this::_assigned_roles)) {
      foreach ($values['access_restaurants'] as $restaurant_parent_id) {
        RestaurantAccess::create([
          'user_id' => $row->id,
          'restaurant_parent_id' => (int)$restaurant_parent_id,
        ]);
      }

      $row->access_restaurants();
    }

    $row->add_log([
      'type' => 'add_' . $row->get_type(),
      'user_id' => $viewer->id,
      'item_id' => (int)$row->id,
      'item_type' => $row->get_type(),
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'name' => 'required|string',
      'email' => 'required|email',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = User::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }
    //restore
    $row1 = User::where('email', $values['email'])
      ->first();
    if ($row1) {
      if ($row1->deleted) {
        return response()->json([
          'type' => 'can_restored',
          'error' => 'Item deleted'
        ], 422);
      }
      //existed
      if ($row1->id != $row->id) {
        return response()->json([
          'error' => 'Email existed'
        ], 422);
      }
    }

    $diffs['before'] = $row->get_log();

    $row->update([
      'name' => trim($values['name']),
      'email' => trim($values['email']),
      'phone' => $values['phone'],
      'status' => $values['status'],
      'role' => $values['role'],
      'note' => $values['note'],
      'access_full' => $values['role'] == 'admin' ? 1 : (int)$values['access_full'],
    ]);

    RestaurantAccess::where('user_id', $row->id)
      ->delete();

    if (count($values['access_restaurants']) && in_array($values['role'], $this::_assigned_roles)) {
      foreach ($values['access_restaurants'] as $restaurant_parent_id) {
        RestaurantAccess::create([
          'user_id' => $row->id,
          'restaurant_parent_id' => (int)$restaurant_parent_id,
        ]);
      }
    }

    $row->access_restaurants();

    $row = User::find($row->id);
    $diffs['after'] = $row->get_log();
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $row->add_log([
        'type' => 'edit_' . $row->get_type(),
        'user_id' => $viewer->id,
        'item_id' => (int)$row->id,
        'item_type' => $row->get_type(),
        'params' => json_encode($diffs),
      ]);
    }

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }

  public function delete(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = User::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => $viewer->id,
    ]);

    $row->add_log([
      'type' => 'delete_' . $row->get_type(),
      'user_id' => $viewer->id,
      'item_id' => (int)$row->id,
      'item_type' => $row->get_type(),
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function restore(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = User::where('email', $values['item'])
      ->first();
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => 0,
      'status' => 'active',
    ]);

    $row->add_log([
      'type' => 'restore_' . $row->get_type(),
      'user_id' => $viewer->id,
      'item_id' => (int)$row->id,
      'item_type' => $row->get_type(),
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function selectize(Request $request)
  {
    $values = $request->all();
    $keyword = isset($values['keyword']) && !empty($values['keyword']) ? $values['keyword'] : NULL;

    $select = User::select('id', 'name')
      ->where('deleted', 0);
    if (!empty($keyword)) {
      $select->where('name', 'LIKE', "%{$keyword}%");
    }

    return response()->json([
      'items' => $select->get()->toArray()
    ]);
  }

  public function profile(Request $request)
  {
    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

    ];

    $this->_viewer->add_log([
      'type' => 'view_profile_info',
    ]);

    return view('tastevn.pages.profile', ['pageConfigs' => $pageConfigs]);
  }

  public function profile_update(Request $request)
  {
    $values = $request->all();
    //required
    $validator = Validator::make($values, [
//      'item' => 'required',
      'name' => 'required|string',
      'email' => 'required|email',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    //restore
    $row1 = User::where('email', trim($values['email']))
      ->where('id', '<>', $this->_viewer->id)
      ->first();
    if ($row1) {
      return response()->json([
        'error' => 'Email existed'
      ], 422);
    }

    $diffs = [
      'before' => [
        'name' => $this->_viewer->name,
        'email' => $this->_viewer->email,
        'phone' => $this->_viewer->phone,
      ]
    ];

    $this->_viewer->update([
      'name' => trim($values['name']),
      'email' => trim($values['email']),
      'phone' => $values['phone'],
    ]);

    $diffs['after'] = [
      'name' => $this->_viewer->name,
      'email' => $this->_viewer->email,
      'phone' => $this->_viewer->phone,
    ];

    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $this->_viewer->add_log([
        'type' => 'edit_profile_info',
        'params' => json_encode($diffs),
      ]);
    }

    return response()->json([
      'status' => true,
      'item' => $this->_viewer->name,
    ], 200);
  }

  public function profile_pwd_code(Request $request)
  {
    $values = $request->all();

    //token
    $token = strtoupper($this->_sys_app->str_rand(8));

    $row = PasswordResetToken::where('email', $this->_viewer->email)
      ->first();
    if ($row) {
      $row->update([
        'token' => $token,
      ]);
    } else {
      PasswordResetToken::create([
        'email' => $this->_viewer->email,
        'token' => $token,
      ]);
    }

    //mail
    Notification::send($this->_viewer, new ForgotPassword([
      'email' => $this->_viewer->email,
      'code' => $token,
    ]));

    return response()->json([
      'status' => true,
      'user' => $token,
    ], 200);
  }

  public function profile_pwd_update(Request $request)
  {
    $values = $request->all();

    $validator = Validator::make($values, [
//      'code' => 'required',
      'password' => 'min:8|required_with:password_confirmation|same:password_confirmation',
      'password_confirmation' => 'min:8'
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $this->_viewer->update([
      'password' => Hash::make($values['password']),
    ]);

    PasswordResetToken::where('email', $this->_viewer->email)
      ->delete();

    $this->_viewer->add_log([
      'type' => 'edit_pwd'
    ]);

    return response()->json([
      'status' => true,
      'user' => $this->_viewer->name,
    ], 200);
  }

  public function profile_setting(Request $request)
  {
    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

    ];

    $this->_sys_app->aws_s3_polly([
      'tester' => 1,
    ]);

    $this->_viewer->add_log([
      'type' => 'view_profile_setting',
    ]);

    return view('tastevn.pages.profile_setting', ['pageConfigs' => $pageConfigs]);
  }

  public function profile_setting_update(Request $request)
  {
    $values = $request->post();
//    echo '<pre>';var_dump($values);die;
    $settings = isset($values['settings']) && count($values['settings']) ? $values['settings'] : [];

    $diffs = [];
    $arr = [

    ];
    foreach ($arr as $k => $v) {
      $diffs['before'][$v] = (int)$this->_viewer->get_setting($v);
    }

    if (count($settings)) {
      foreach ($settings as $key => $val) {
        $this->_viewer->set_setting($key, $val);
      }
    }

    foreach ($arr as $k => $v) {
      $diffs['after'][$v] = (int)$this->_viewer->get_setting($v);
    }
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $this->_viewer->add_log([
        'type' => 'edit_profile_setting',
        'params' => json_encode($diffs),
      ]);
    }

    return response()->json([
      'status' => true,
      'item' => $this->_viewer->name,
    ], 200);
  }

  public function profile_setting_notify(Request $request)
  {
    $values = $request->post();
//    echo '<pre>';var_dump($values);die;
    $notifications = isset($values['notifications']) && count($values['notifications']) ? $values['notifications'] : [];

    $diffs = [];
    $arr = [
      'missing_ingredient_receive', 'missing_ingredient_alert_speaker', 'missing_ingredient_alert_printer', 'missing_ingredient_alert_email',
      'photo_comment_alert_email',
    ];
    foreach ($arr as $k => $v) {
      $diffs['before'][$v] = (int)$this->_viewer->get_setting($v);
    }

    if (count($notifications)) {
      foreach ($notifications as $notification) {
        if (in_array($notification['key'], $arr)) {
          $this->_viewer->set_setting($notification['key'], $notification['val']);
        }
      }
    }

    foreach ($arr as $k => $v) {
      $diffs['after'][$v] = (int)$this->_viewer->get_setting($v);
    }
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $this->_viewer->add_log([
        'type' => 'edit_profile_notification',
        'params' => json_encode($diffs),
      ]);
    }

    return response()->json([
      'status' => true,
      'item' => $this->_viewer->name,
    ], 200);
  }

  //zalo
  public function zalo_user_update(Request $request)
  {
    $values = $request->post();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = User::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $zalo = isset($values['zalo']) ? (int)$values['zalo'] : 0;
    $zalo_user = ZaloUser::find($zalo);

    ZaloUser::where('user_id', $row->id)
      ->update([
        'user_id' => 0,
      ]);

    if ($zalo_user) {
      $zalo_user->update([
        'user_id' => $row->id,
      ]);
    }

    return response()->json([
      'status' => true,
    ], 200);
  }

  public function zalo_message_send(Request $request)
  {
    $values = $request->post();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = User::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $type = isset($values['type']) ? $values['type'] : 'request';
    $message = isset($values['message']) ? trim($values['message']) : NULL;

    $zalo = $row->get_zalo();
    if (!$zalo) {
      return response()->json([
        'error' => 'Invalid zalo'
      ], 422);
    }

    $datas = NULL;

    switch ($type) {
      case 'request':

        $datas = SysZalo::send_request_info($row);

        break;

      case 'custom':

        $datas = SysZalo::send_text_only($row, $message);

        break;
    }

    return response()->json([
      'status' => true,
      'datas' => $datas,
    ], 200);
  }

}
