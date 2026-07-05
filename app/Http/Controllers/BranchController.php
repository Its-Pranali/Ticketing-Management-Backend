<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function index()
    {
        $data = Branch::join('districts as d', 'd.id', '=', 'branches.dist_id')
            ->join('talukas as t', 't.id', '=', 'branches.taluka_id')
            ->join('banks as b', 'b.id', '=', 'branches.bank_id')
            ->select('branches.*', 'd.dist_name', 't.taluka_name', 'b.bank_name')
            ->where('branches.status', 1)
            ->get();

        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function branchList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = Branch::join('districts as d', 'd.id', '=', 'branches.dist_id')
            ->join('talukas as t', 't.id', '=', 'branches.taluka_id')
            ->join('banks as b', 'b.id', '=', 'branches.bank_id')
            ->select('branches.*', 'd.dist_name', 't.taluka_name', 'b.bank_name')
            ->where('branches.status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('t.taluka_name', 'like', "%{$search}%")
                    ->orWhere('b.bank_name', 'like', "%{$search}%")
                    ->orWhere('branches.branch_name', 'like', "%{$search}%")
                    ->orWhere('branches.branch_name_ll', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);

        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'per_page' => $data->perPage(), 'last_page' => $data->lastPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dist_id' => 'required',
            'taluka_id' => 'required',
            'bank_id' => 'required',
            'branch_name' => 'required|string|max:255',
            'branch_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = new Branch();
        $data->dist_id = $request->dist_id;
        $data->taluka_id = $request->taluka_id;
        $data->bank_id = $request->bank_id;
        $data->branch_name = $request->branch_name;
        $data->branch_name_ll = $request->branch_name_ll;
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
            'bank_id' => 'required',
            'branch_name' => 'required|string|max:255',
            'branch_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = Branch::find($id);
        $data->dist_id = $request->dist_id;
        $data->taluka_id = $request->taluka_id;
        $data->bank_id = $request->bank_id;
        $data->branch_name = $request->branch_name;
        $data->branch_name_ll = $request->branch_name_ll;
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
        $data = Branch::findOrFail($id);
        $data->status = 0;
        $result = $data->save();

        if ($result) {
            return response()->json(['status' => true, 'message' => "Data deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleting data", 'code' => 500]);
        }
    }
}
