<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
    {
        $data = Task::join('products as p', 'p.id', '=', 'tasks.product_id')
            ->join('modules as m', 'm.id', '=', 'tasks.module_id')
            ->select('tasks.*', 'p.product_name', 'm.module_name')
            ->where('tasks.status', 1)
            ->get();

        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function taskList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = Task::join('products as p', 'p.id', '=', 'tasks.product_id')
            ->join('modules as m', 'm.id', '=', 'tasks.module_id')
            ->select('tasks.*', 'p.product_name', 'm.module_name')
            ->where('tasks.status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('p.product_name', 'like', "%{$search}%")
                    ->orWhere('m.module_name', 'like', "%{$search}%")
                    ->orWhere('tasks.task_name', 'like', "%{$search}%")
                    ->orWhere('tasks.task_name_ll', 'like', "%{$search}%");
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
            'product_id' => 'required',
            'module_id' => 'required',
            'task_name' => 'required|string|max:255',
            'task_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => true, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = new Task();
        $data->product_id = $request->product_id;
        $data->module_id = $request->module_id;
        $data->task_name = $request->task_name;
        $data->task_name_ll = $request->task_name_ll;
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
            'module_id' => 'required',
            'task_name' => 'required|string|max:255',
            'task_name_ll' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => true, 'message' => "Validation Error", 'code' => 500]);
        }

        $data = Task::find($id);
        $data->product_id = $request->product_id;
        $data->module_id = $request->module_id;
        $data->task_name = $request->task_name;
        $data->task_name_ll = $request->task_name_ll;
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
        $data = Task::findOrFail($id);
        $data->status = 0;
        $result = $data->save();

        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Data deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleted data", 'code' => 500]);
        }
    }
}
