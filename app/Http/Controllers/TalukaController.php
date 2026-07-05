<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Taluka;
use Illuminate\Support\Facades\Validator;

class TalukaController extends Controller
{

    public function index()
    {
        $data = Taluka::join('states as s', 's.id', '=', 'talukas.state_id')
            ->join('districts as d', 'd.id', '=', 'talukas.dist_id')
            ->select('talukas.*', 'd.dist_name', 's.state')
            ->where('talukas.status', 1)
            ->get();
        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function TalukaList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = Taluka::join('states as s', 's.id', '=', 'talukas.state_id')
            ->join('districts as d', 'd.id', '=', 'talukas.dist_id')
            ->select('talukas.*', 'd.dist_name', 's.state')
            ->where('talukas.status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('talukas.taluka_name', 'like', "%{$search}%")
                    ->orWhere('talukas.taluka_name_ll', 'like', "%{$search}%");
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
            'dist_id' => 'required',
            'taluka_name' => 'required|string|max:255',
            'taluka_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = new Taluka();
        $data->state_id = 1;
        $data->dist_id = $request->dist_id;
        $data->taluka_name = $request->taluka_name;
        $data->taluka_name_ll = $request->taluka_name_ll;
        $data->status = 1;

        $result = $data->save();
        if (!empty($result)) {
            return response()->json(['status' => true, 'message' => "Taluka Saved successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while saving data", 'code' => 500]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'dist_id' => 'required',
            'taluka_name' => 'required|string|max:255',
            'taluka_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = Taluka::find($id);
        if (!$data) {
            return response()->json(['status' => false, 'message' => "Taluka not found", 'code' => 404]);
        }
        $data->state_id = 1;
        $data->dist_id = $request->dist_id;
        $data->taluka_name = $request->taluka_name;
        $data->taluka_name_ll = $request->taluka_name_ll;
        $data->status = 1;

        $result = $data->save();
        if (!empty($result)) {
            return response()->json(['status' => true, 'message' => "Taluka Updated Successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while updating Taluka", 'code' => 500]);
        }
    }

    public function delete($id)
    {
        $data = Taluka::findOrFail($id);
        $data->status = 0;
        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Taluka deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleting taluka", 'code' => 500]);
        }
    }
}
