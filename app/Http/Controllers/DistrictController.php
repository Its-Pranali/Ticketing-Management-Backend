<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\District;
use Illuminate\Support\Facades\Validator;

class DistrictController extends Controller
{
    public function index()
    {
        $data = District::get()->where('status',1);
        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while getting data", 'code' => 500]);
        }
    }

    public function DistrictList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = District::join('states as s', 's.id', '=', 'districts.state_id')
            ->select('districts.*', 's.state')
            ->where('districts.status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('dist_name', 'like', "%{$search}%")
                    ->orWhere('dist_name_ll', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'last_page' => $data->lastPage(), 'per_page' => $data->perPage(), 'total' => $data->total()], 'code' => 500]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while getting data", 'code' => 500]);
        }
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dist_name' => 'required|string|max:255',
            'dist_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => true, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = new District;
        $data->state_id = 1;
        $data->dist_name = $request->dist_name;
        $data->dist_name_ll = $request->dist_name_ll;
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
            'dist_name' => 'required|string|max:255',
            'dist_name_ll' => 'required|string|max:255',
        ]);

        if($validator->fails()){
            return response()->json(['status'=>false,'message'=>"Validation Error",'code'=>500]);
        }
        $data=District::find($id);
        $data->state_id = 1;
        $data->dist_name = $request->dist_name;
        $data->dist_name_ll = $request->dist_name_ll;

        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Data updated successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while updating data", 'code' => 500]);
        }
    }

    public function delete($id)
    {
        $data = District::findOrFail($id);
        $data->status = 0;
        $result = $data->save();

        if ($result) {
            return response()->json(['status' => true, 'message' => "Data deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleting data", 'code' => 500]);
        }
    }
}
