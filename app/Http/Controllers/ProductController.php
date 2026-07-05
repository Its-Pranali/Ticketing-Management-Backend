<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $data = Product::join('priority as p', 'p.id', '=', 'products.priority')
            ->select('products.*', 'p.priority_name')
            ->where('products.status', 1)
            ->get();

        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error While fetching data", 'code' => 500]);
        }
    }

    public function productList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = Product::join('priority as p', 'p.id', '=', 'products.priority')
            ->select('products.*', 'p.priority_name')
            ->where('products.status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('products.product_name', 'like', "%{$search}%")
                    ->orWhere('products.product_name_ll', 'like', "%{$search}%")
                    ->orWhere('p.priority_name', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'per_page' => $data->perPage(), 'last_page' => $data->lastPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'product_name_ll' => 'required|string|max:255',
            'priority' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = new Product();
        $data->product_name = $request->product_name;
        $data->product_name_ll = $request->product_name_ll;
        $data->priority = $request->priority;
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
            'product_name' => 'required|string|max:255',
            'product_name_ll' => 'required|string|max:255',
            'priority' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = Product::find($id);
        $data->product_name = $request->product_name;
        $data->product_name_ll = $request->product_name_ll;
        $data->priority = $request->priority;
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
        $data = Product::findOrFail($id);
        $data->status = 0;
        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Data Deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleting data", 'code' => 500]);
        }
    }

    public function getModuleByProductId(Request $request, $id)
    {
        $data = Product::select('modules.*')
            ->where('modules.product_id', $id)
            ->where('modules.*')
            ->get();

        if ($data) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Data not found", 'code' => 500]);
        }
    }
}
