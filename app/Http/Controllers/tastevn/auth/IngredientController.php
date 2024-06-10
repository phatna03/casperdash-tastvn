<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//lib
use Validator;
use App\Api\SysApp;
//model
use App\Models\Ingredient;

class IngredientController extends Controller
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

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $invalid_roles = ['user'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    $this->_viewer->add_log([
      'type' => 'view_listing_ingredient',
    ]);

    return view('tastevn.pages.ingredients', ['pageConfigs' => $pageConfigs]);
  }

  public function create(Request $request)
  {
    $values = $request->all();

    $data = [];
    if (isset($values['name']) && !empty(trim($values['name']))) {
      $row = Ingredient::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['name'])))
        ->first();
      if (!$row) {
        $row = Ingredient::create([
          'name' => strtolower(trim($values['name']))
        ]);

        $this->_viewer->add_log([
          'type' => 'add_' . $row->get_type(),
          'item_id' => (int)$row->id,
          'item_type' => $row->get_type(),
        ]);
      }
    }

    return response()->json([
      'items' => $this->selectize_items()
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $values = $request->all();

    //required
    $validator = Validator::make($values, [
      'name' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //restore
    $row = Ingredient::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['name'])))
      ->first();
    if ($row) {
//      if ($row->deleted) {
//        return response()->json([
//          'type' => 'can_restored',
//          'error' => 'Item deleted'
//        ], 422);
//      }
      //existed
      return response()->json([
        'error' => 'Name existed'
      ], 422);
    }

    $row = Ingredient::create([
      'name' => strtolower(trim($values['name'])),
      'name_vi' => isset($values['name_vi']) ? trim($values['name_vi']) : null,
      'creator_id' => $this->_viewer->id,
    ]);

    $this->_viewer->add_log([
      'type' => 'add_' . $row->get_type(),
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

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'name' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = Ingredient::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }
    //restore
    $row1 = Ingredient::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['name'])))
      ->first();
    if ($row1) {
//      if ($row1->deleted) {
//        return response()->json([
//          'type' => 'can_restored',
//          'error' => 'Item deleted'
//        ], 422);
//      }
      //existed
      if ($row1->id != $row->id) {
        return response()->json([
          'error' => 'Name existed'
        ], 422);
      }
    }

    $diffs['before'] = $row->get_log();

    $row->update([
      'name' => strtolower(trim($values['name'])),
      'name_vi' => isset($values['name_vi']) ? trim($values['name_vi']) : null,
    ]);

    $row->on_update_after();

    $row = Ingredient::find($row->id);
    $diffs['after'] = $row->get_log();
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $this->_viewer->add_log([
        'type' => 'edit_' . $row->get_type(),
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
    //
  }

  public function restore(Request $request)
  {
    //
  }

  public function selectize(Request $request)
  {
    $values = $request->all();

    return response()->json([
      'items' => $this->selectize_items($values)
    ]);
  }

  protected function selectize_items($pars = [])
  {
    $select = Ingredient::select('id', 'name', 'name_vi');

    //dev
    if ($this->_viewer->is_dev()) {

    } else {
      $select->where('deleted', 0);
    }

    $keyword = isset($pars['keyword']) && !empty($pars['keyword']) ? $pars['keyword'] : NULL;
    if (!empty($keyword)) {
      $select->where('name', 'LIKE', "%{$keyword}%");
    }

    $arr = [];
    $rows = $select->get();
    if (count($rows)) {
      foreach ($rows as $row) {
        $arr[] = [
          'id' => $row->id,
          'name' => !empty($row->name_vi) ? $row->name . ' - ' . $row->name_vi : $row->name,
        ];
      }
    }

    return $arr;
  }
}
