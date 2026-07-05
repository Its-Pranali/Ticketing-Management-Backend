<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketMaster;

class ReportController extends Controller
{
    public function getDistrictWiseReport(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));
        $district = $request->input('district');
        $taluka = $request->input('taluka');
        $socity = $request->input('socity');
        $status = $request->input('status');

        $query = TicketMaster::join('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
            ->join('products as p', 'p.id', '=', 'ticket_masters.product_id')
            ->join('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
            ->join('pacs as pc', 'pc.id', '=', 'ticket_masters.socity_id')
            ->select('ticket_masters.*', 'u.name as user_name', 'p.product_name', 'ts.sts_name', 'pc.pacs_name');


        if (!empty($district)) {
            $districts = is_array($district) ? $district : explode(',', $district);
            $districts = array_filter(array_map('trim', $districts));
            if (!empty($districts)) {
                $query->whereIn('pc.dist_id', $districts);
            }
        }

        if (!empty($taluka)) {
            $talukas = is_array($taluka) ? $taluka : explode(',', $taluka);
            $talukas = array_filter(array_map('trim', $talukas));
            if (!empty($talukas)) {
                $query->whereIn('pc.taluka_id', $talukas);
            }
        }

        if (!empty($socity)) {
            $socities = is_array($socity) ? $socity : explode(',', $socity);
            $socities = array_filter(array_map('trim', $socities));
            if (!empty($socities)) {
                $query->whereIn('ticket_masters.socity_id', $socities);
            }
        }

        if (!empty($status)) {
            $query->where('ticket_masters.status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_masters.ticket_id', 'like', "%{$search}%")
                    ->orWhere('pc.pacs_name', 'like', "%{$search}%")
                    ->orWhere('p.product_name', 'like', "%{$search}%");
            });
        }

        $query->orderBy('ticket_masters.id', 'desc');

        $data = $query->paginate($limit, ['*'], 'page', $page);
        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'per_page' => $data->perPage(), 'last_page' => $data->lastPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => true, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function getProductwiseReport(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $product = $request->input('product');
        $module = $request->input('module');
        $task = $request->input('task');
        $status = $request->input('status');

        $query = TicketMaster::join('products as p', 'p.id', '=', 'ticket_masters.product_id')
            ->join('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
            ->join('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
            ->join('pacs as pc', 'pc.id', '=', 'ticket_masters.socity_id')
            ->select('ticket_masters.*', 'u.name as user_name', 'p.product_name', 'ts.sts_name', 'pc.pacs_name');

        if (!empty($product)) {
            $products = is_array($product) ? $product : explode(',', $product);
            $products = array_filter(array_map('trim', $products));
            if (!empty($products)) {
                $query->whereIn('ticket_masters.product_id', $products);
            }
        }

        if (!empty($module)) {
            $modules = is_array($module) ? $module : explode(',', $module);
            $modules = array_filter(array_map('trim', $modules));
            if (!empty($modules)) {
                $query->whereIn('ticket_masters.module_id', $modules);
            }
        }

        if (!empty($task)) {
            $tasks = is_array($task) ? $task : explode(',', $task);
            $tasks = array_filter(array_map('trim', $tasks));
            if (!empty($tasks)) {
                $query->whereIn('ticket_masters.task_id', $tasks);
            }
        }

        if (!empty($status)) {
            $query->where('ticket_masters.status', $status);
        }


        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_masters.ticket_id', 'like', "%{$search}%")
                    ->orWhere('pc.pacs_name', 'like', "%{$search}%")
                    ->orWhere('p.product_name', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);

        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'last_page' => $data->lastPage(), 'per_page' => $data->perPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }
}
