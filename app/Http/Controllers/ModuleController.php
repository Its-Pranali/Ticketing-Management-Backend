<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use Illuminate\Support\Facades\Validator;

class ModuleController extends Controller
{
    public function index()
    {
        $data = Module::join('products as p', 'p.id', '=', 'modules.product_id')
            ->select('modules.*', 'p.product_name')
            ->where('modules.status', 1)
            ->get();

        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function moduleList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = Module::join('products as p', 'p.id', '=', 'modules.product_id')
            ->select('modules.*', 'p.product_name')
            ->where('modules.status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('p.product_name', 'like', "%{$search}%")
                    ->orWhere('modules.module_name', 'like', "%{$search}%")
                    ->orWhere('modules.module_name_ll', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'per_page' => $data->perPage(), 'last_page' => $data->lastPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while getting data", 'code' => 500]);
        }
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'module_name' => 'required|string|max:255',
            'module_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = new Module();
        $data->product_id = $request->product_id;
        $data->module_name = $request->module_name;
        $data->module_name_ll = $request->module_name_ll;
        $data->status = 1;

        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Data saved successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while saving data", 'code' => 500]);
        }
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'module_name' => 'required|string|max:255',
            'module_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = Module::find($id);
        $data->product_id = $request->product_id;
        $data->module_name = $request->module_name;
        $data->module_name_ll = $request->module_name_ll;
        $data->status = 1;

        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Data updated successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while updating data", 'code' => 500]);
        }
    }

    public function delete($id)
    {
        $data = Module::findOrFail($id);
        $data->status = 0;
        $result = $data->save();

        if ($result) {
            return response()->json(['status' => true, 'message' => "Data deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleting data", 'code' => 500]);
        }
    }
}
