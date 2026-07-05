<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Village;
use Illuminate\Support\Facades\Validator;

class VillageController extends Controller
{
    public function index()
    {
        $data = Village::join('states as s', 's.id', '=', 'villages.state_id')
            ->join('districts as d', 'd.id', '=', 'villages.dist_id')
            ->join('talukas as t', 't.id', '=', 'villages.taluka_id')
            ->select('villages.*', 't.taluka_name', 's.state', 'd.dist_name')
            ->where('villages.status', 1)
            ->get();

        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function villageList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = Village::join('states as s', 's.id', '=', 'villages.state_id')
            ->join('districts as d', 'd.id', '=', 'villages.dist_id')
            ->join('talukas as t', 't.id', '=', 'villages.taluka_id')
            ->select('villages.*', 't.taluka_name', 's.state', 'd.dist_name')
            ->where('villages.status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('dist_id', 'like', "%{$search}%")
                    ->orWhere('taluka_id', 'like', "%{$search}%")
                    ->orWhere('village_name', 'like', "%{$search}%")
                    ->orWhere('village_name_ll', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);

        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'last_page' => $data->lastPage(), 'per_page' => $data->perPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dist_id' => 'required',
            'taluka_id' => 'required',
            'village_name' => 'required|string|max:255',
            'village_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = new Village();
        $data->state_id = 1;
        $data->dist_id = $request->dist_id;
        $data->taluka_id = $request->taluka_id;
        $data->village_name = $request->village_name;
        $data->village_name_ll = $request->village_name_ll;
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
            'dist_id' => 'required',
            'taluka_id' => 'required',
            'village_name' => 'required|string|max:255',
            'village_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = Village::find($id);
        $data->state_id = 1;
        $data->dist_id = $request->dist_id;
        $data->taluka_id = $request->taluka_id;
        $data->village_name = $request->village_name;
        $data->village_name_ll = $request->village_name_ll;
        $data->status = 1;

        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Data Updated successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while updating data", 'code' => 500]);
        }
    }

    public function delete($id)
    {
        $data = Village::findOrFail($id);
        $data->status = 0;
        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Data deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleting data", 'code' => 500]);
        }
    }
}
