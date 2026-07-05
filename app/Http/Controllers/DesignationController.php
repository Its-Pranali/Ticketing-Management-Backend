<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Designation;
use Illuminate\Support\Facades\Validator;

class DesignationController extends Controller
{
    public function index()
    {
        $data = Designation::get()->where('status', 1);
        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while getting data", 'code' => 500]);
        }
    }

    public function DesignationList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = Designation::where('status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('designation', 'like', "%{$search}%")
                    ->orWhere('designation_ll', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'per_page' => $data->perPage(), 'last_page' => $data->lastPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while getting data", 'code' => 500]);
        }
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'designation' => 'required|string|max:255',
            'designation_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = new Designation();
        $data->designation = $request->designation;
        $data->designation_ll = $request->designation_ll;
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
            'designation' => 'required|string|max:255',
            'designation_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = Designation::find($id);
        $data->designation = $request->designation;
        $data->designation_ll = $request->designation_ll;
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
        $data = Designation::findOrFail($id);
        $data->status = 0;
        $result = $data->save();

        if ($result) {
            return response()->json(['status' => true, 'message' => "Data deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleting data", 'code' => 500]);
        }
    }
}
