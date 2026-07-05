<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use Illuminate\Support\Facades\Validator;

class BankController extends Controller
{
    public function index()
    {
        $data = Bank::join('districts as d', 'd.id', '=', 'banks.dist_id')
            ->select('banks.*', 'd.dist_name')
            ->where('banks.status', 1)
            ->get();
        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function bankList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = Bank::join('districts as d', 'd.id', '=', 'banks.dist_id')
            ->select('banks.*', 'd.dist_name')
            ->where('banks.status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('banks.bank_name', 'like', "%{$search}%")
                    ->orWhere('banks.bank_name_ll', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);

        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'last_page' => $data->lastPage(), 'per_page' => $data->perPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dist_id' => 'required',
            'bank_name' => 'required|string|max:255',
            'bank_name_ll' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = new Bank();
        $data->dist_id = $request->dist_id;
        $data->bank_name = $request->bank_name;
        $data->bank_name_ll = $request->bank_name_ll;
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
            'bank_name' => 'required|string|max:255',
            'bank_name_ll' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = Bank::find($id);
        $data->dist_id = $request->dist_id;
        $data->bank_name = $request->bank_name;
        $data->bank_name_ll = $request->bank_name_ll;
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
        $data = Bank::findOrFail($id);
        $data->status = 0;
        $result = $data->save();

        if ($result) {
            return response()->json(['status' => true, 'message' => "Data deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }
}
