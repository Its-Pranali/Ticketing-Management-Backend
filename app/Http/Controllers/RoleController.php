<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index()
    {
        $data = Role::where('status', 1)->get();
        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function roleList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = Role::where('status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('role', 'like', "%{$search}%")
                    ->orWhere('role_ll', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'last_page' => $data->lastPage(), 'per_page' => $data->perPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while getting data", 'code' => 500]);
        }
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|max:255',
            'role_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 501]);
        }

        $data = new Role();
        $data->role = $request->role;
        $data->role_ll = $request->role_ll;
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
            'role' => 'required|string|max:255',
            'role_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 501]);
        }

        $data = Role::find($id);
        $data->role = $request->role;
        $data->role_ll = $request->role_ll;
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
        $data = Role::findOrFail($id);
        $data->status = 0;
        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Data deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleting data", 'code' => 500]);
        }
    }
}
