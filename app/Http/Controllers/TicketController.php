<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    private function roleCondition($query)
    {
        $user = Auth::user();
        if ($user) {
            if ($user->role == 1) {
            } elseif ($user->role == 2 || $user->role == 6) {
                $query->where('ticket_masters.created_by', $user->user_id);
            } elseif (in_array($user->role, [3, 4, 5])) {
                $query->whereIn('ticket_masters.dist_id', explode(',', $user->dist_id));
            }
        }
        return $query;
    }

    public function getDashboardTicketCount(Request $request)
    {
        $user = Auth::user();

        $user = Auth::user();
        $month = $request->input('month');
        $year = $request->input('year');

        $cacheKey = 'dashboard_counts_';

        if ($user) {
            if ($user->role == 1) {
                $cacheKey .= 'admin';
            } else if ($user->role == 2 || $user->role == 6) {
                $cacheKey .= 'user_' . $user->user_id;
            } else if (in_array($user->role, [3, 4, 5])) {
                $cacheKey .= 'dist_' . $user->dist_id;
            } else {
                $cacheKey .= 'other_' . $user->user_id;
            }
        } else {
            $cacheKey .= 'guest';
        }

        $cacheKey .= "_m{$month}_y{$year}";

        // Cache the counts for 60 seconds to optimize DB fetching time
        $data = Cache::remember($cacheKey, 60, function () use ($month, $year) {
            $query = TicketMaster::whereNull('ticket_masters.deleted_at');
            $query = $this->roleCondition($query);

            if (!empty($year)) {
                $query->whereYear('ticket_masters.created_at', $year);
            }
            if (!empty($month)) {
                $query->whereMonth('ticket_masters.created_at', $month);
            }

            $counts = $query->selectRaw("
                COUNT(*) as allT,
                SUM(CASE WHEN ticket_masters.status = 1 THEN 1 ELSE 0 END) as openT,
                SUM(CASE WHEN ticket_masters.status = 2 THEN 1 ELSE 0 END) as inprogressT,
                SUM(CASE WHEN ticket_masters.status = 4 THEN 1 ELSE 0 END) as closedT,
                SUM(CASE WHEN ticket_masters.status = 5 THEN 1 ELSE 0 END) as forwardedT,
                SUM(CASE WHEN ticket_masters.status = 7 THEN 1 ELSE 0 END) as forwardedNLPSVT
            ")->first();

            return [
                'openT' => (int)$counts->openT,
                'inprogressT' => (int)$counts->inprogressT,
                'closedT' => (int)$counts->closedT,
                'forwardedT' => (int)$counts->forwardedT,
                'forwardedNLPSVT' => (int)$counts->forwardedNLPSVT,
                'allT' => (int)$counts->allT,
            ];
        });
        return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
    }

    public function getAllTickets(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query =  TicketMaster::leftJoin('districts as d', 'd.id', '=', 'ticket_masters.dist_id')
            ->leftJoin('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
            // ->leftJoin('users as u', 'u.socity_id', '=', 'ticket_masters.created_by')
            ->join('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
            ->leftJoin('ticket_api_data as tad', 'tad.ticket_id', '=', 'ticket_masters.ticket_id')
            ->select('ticket_masters.*', 'd.dist_name', 'ts.sts_name', 'tad.ticket_number', 'u.name as user_name');

        $query = $this->roleCondition($query);


        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');
        $status   = $request->input('status');

        if (!empty($fromDate)) {
            $query->whereDate('ticket_masters.created_at', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('ticket_masters.created_at', '<=', $toDate);
        }
        if (!empty($status)) {
            $query->where('ticket_masters.status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('tad.ticket_number', 'like', "%{$search}%")
                    ->orWhere('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.pacs_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.subject', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.subject', 'like', "%{$search}%")
                    ->orWhere('u.name', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);

        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'per_page' => $data->perPage(), 'last_page' => $data->lastPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while getting data", 'code' => 500]);
        }
    }

    public function getOpenTickets(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = TicketMaster::leftJoin('districts as d', 'd.id', '=', 'ticket_masters.dist_id')
            ->leftJoin('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
            ->join('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
            ->leftJoin('ticket_api_data as tad', 'tad.ticket_id', '=', 'ticket_masters.ticket_id')
            ->select('ticket_masters.*', 'd.dist_name', 'ts.sts_name', 'tad.ticket_number', 'u.name as user_name')
            ->where('ticket_masters.status', 1);

        $query = $this->roleCondition($query);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('tad.ticket_number', 'like', "%{$search}%")
                    ->orWhere('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.ticket_id', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.pacs_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.subject', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.created_at', 'like', "%{$search}%")
                    ->orWhere('ts.sts_name', 'like', "%{$search}%")
                    ->orWhere('u.name', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);

        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'last_page' => $data->lastPage(), 'per_page' => $data->perPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function getTicketById($id)
    {
        $data = TicketMaster::leftJoin('districts as d', 'd.id', '=', 'ticket_masters.dist_id')
            ->leftJoin('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
            ->join('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
            ->leftJoin('ticket_api_data as tad', 'tad.ticket_id', '=', 'ticket_masters.ticket_id')
            ->select('ticket_masters.*', 'd.dist_name', 'ts.sts_name', 'tad.ticket_number', 'u.name as user_name')
            ->where('ticket_masters.id', $id)
            ->first();

        if ($data) {
            $replies = DB::table('ticket_replies')
                ->join('users as u', 'u.user_id', '=', 'ticket_replies.inserted_by')
                ->select('ticket_replies.*', 'u.name as user_name', 'u.role')
                ->where('ticket_replies.ticket_id', $data->ticket_id)
                ->whereNull('ticket_replies.deleted_at')
                ->get();

            $data->replies = $replies;
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        }
        return response()->json(['status' => false, 'message' => "Data not found", 'code' => 404]);
    }


    // public function getTicketById($id)
    // {
    //     $data = TicketMaster::leftJoin('districts as d', 'd.id', '=', 'ticket_masters.dist_id')
    //         ->leftJoin('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
    //         ->join('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
    //         ->leftJoin('ticket_api_data as tad', 'tad.ticket_id', '=', 'ticket_masters.ticket_id')
    //         ->select('ticket_masters.*', 'd.dist_name', 'ts.sts_name', 'tad.ticket_number', 'u.name as user_name')
    //         ->where('ticket_masters.id', $id)
    //         ->first();

    //     if ($data) {
    //         return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
    //     } else {
    //         return response()->json(['status' => false, 'message' => "Ticket Not Found", 'code' => 404]);
    //     }
    // }

    public function getClosedTickets(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = TicketMaster::leftJoin('districts as d', 'd.id', '=', 'ticket_masters.dist_id')
            ->leftJoin('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
            ->join('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
            ->leftJoin('ticket_api_data as tad', 'tad.ticket_id', '=', 'ticket_masters.ticket_id')
            ->select('ticket_masters.*', 'd.dist_name', 'ts.sts_name', 'tad.ticket_number', 'u.name as user_name')
            ->where('ticket_masters.status', 4);

        $query = $this->roleCondition($query);

        //   return response()->json(['status' => false, 'message' =>  $query->toSql(), 'code' => 500]);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('tad.ticket_number', 'like', "%{$search}%")
                    ->orWhere('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.ticket_id', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.pacs_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.subject', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.created_at', 'like', "%{$search}%")
                    ->orWhere('ts.sts_name', 'like', "%{$search}%")
                    ->orWhere('u.name', 'like', "%{$search}%");
            });
        }
        // echo $query->toSql();die;
        $data = $query->paginate($limit, ['*'], 'page', $page);

        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'last_page' => $data->lastPage(), 'per_page' => $data->perPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function getInProgressTickets(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = TicketMaster::leftJoin('districts as d', 'd.id', '=', 'ticket_masters.dist_id')
            ->leftJoin('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
            ->leftJoin('products as p', 'p.id', '=', 'ticket_masters.product_id')
            ->join('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
            ->leftJoin('ticket_api_data as tad', 'tad.ticket_id', '=', 'ticket_masters.ticket_id')
            ->select('ticket_masters.*', 'd.dist_name', 'ts.sts_name', 'tad.ticket_number', 'u.name as user_name', 'p.product_name')
            ->where('ticket_masters.status', 2);

        $query = $this->roleCondition($query);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('tad.ticket_number', 'like', "%{$search}%")
                    ->orWhere('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.ticket_id', 'like', "%{$search}%")
                    ->orWhere('p.product_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.pacs_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.subject', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.created_at', 'like', "%{$search}%")
                    ->orWhere('ts.sts_name', 'like', "%{$search}%")
                    ->orWhere('u.name', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'last_page' => $data->lastPage(), 'per_page' => $data->perPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while getting data", 'code' => 500]);
        }
    }

    public function getForwardedTickets(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = TicketMaster::leftJoin('districts as d', 'd.id', '=', 'ticket_masters.dist_id')
            ->leftJoin('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
            ->leftJoin('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
            ->leftJoin('ticket_api_data as tad', 'tad.ticket_id', '=', 'ticket_masters.ticket_id')
            ->select('ticket_masters.*', 'd.dist_name', 'ts.sts_name', 'tad.ticket_number', 'u.name as user_name')
            ->where('ticket_masters.status', 5);

        $query = $this->roleCondition($query);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('tad.ticket_number', 'like', "%{$search}%")
                    ->orWhere('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.ticket_id', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.pacs_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.subject', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.created_at', 'like', "%{$search}%")
                    ->orWhere('ts.sts_name', 'like', "%{$search}%")
                    ->orWhere('u.name', 'like', "%{$search}%");
            });
        }

        // return response()->json(['status' => false, 'message' => $query->toSql(), 'code' => 500]);

        $data = $query->paginate($limit, ['*'], 'page', $page);
        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'per_page' => $data->perPage(), 'last_page' => $data->lastPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function getForwardNlpsv(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = TicketMaster::leftJoin('districts as d', 'd.id', '=', 'ticket_masters.dist_id')
            ->leftJoin('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
            ->leftJoin('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
            ->leftJoin('products as p', 'p.id', '=', 'ticket_masters.product_id')
            ->leftJoin('ticket_api_data as tad', 'tad.ticket_id', '=', 'ticket_masters.ticket_id')
            ->select('ticket_masters.*', 'd.dist_name', 'ts.sts_name', 'tad.ticket_number', 'u.name as user_name', 'p.product_name')
            ->where('ticket_masters.status', 7);

        $query = $this->roleCondition($query);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('tad.ticket_number', 'like', "%{$search}%")
                    ->orWhere('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.pacs_name', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.subject', 'like', "%{$search}%")
                    ->orWhere('ticket_masters.subject', 'like', "%{$search}%")
                    ->orWhere('u.name', 'like', "%{$search}%")
                    ->orWhere('p.product_name', 'like', "%{$search}%")
                    ->orWhere('ts.sts_name', 'like', "%{$search}%")
                    ->orWhere('u.user_name', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        if ($data) {
            return response()->json(['status' => true, 'message' => $data->items(), 'pagination' => ['current_page' => $data->currentPage(), 'last_page' => $data->lastPage(), 'per_page' => $data->perPage(), 'total' => $data->total()], 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function getRecentActivity(Request $request)
    {
        $limit = $request->input('limit', 5);

        $query = TicketMaster::leftJoin('districts as d', 'd.id', '=', 'ticket_masters.dist_id')
            ->leftJoin('ticket_status as ts', 'ts.id', '=', 'ticket_masters.status')
            ->join('users as u', 'u.user_id', '=', 'ticket_masters.created_by')
            ->leftJoin('ticket_api_data as tad', 'tad.ticket_id', '=', 'ticket_masters.ticket_id')
            ->select(
                'ticket_masters.ticket_id',
                'ticket_masters.subject',
                'ticket_masters.status',
                'ticket_masters.created_at',
                'ticket_masters.updated_at',
                'd.dist_name',
                'ts.sts_name',
                'tad.ticket_number',
                'u.name as user_name'
            )
            ->whereNull('ticket_masters.deleted_at')
            ->orderBy('ticket_masters.updated_at', 'desc')
            ->limit($limit);

        $query = $this->roleCondition($query);

        $data = $query->get()->map(function ($ticket) {
            $statusColorMap = [
                1 => '#0ea5e9',
                2 => '#f59e0b',
                4 => '#10b981',
                5 => '#8b5cf6',
                7 => '#ef4444',
            ];

            $statusActionMap = [
                1 => 'opened ticket',
                2 => 'is working on',
                4 => 'closed ticket',
                5 => 'forwarded ticket',
                7 => 'forwarded to NLPSV',
            ];

            $color  = $statusColorMap[$ticket->status]  ?? '#64748b';
            $action = $statusActionMap[$ticket->status] ?? 'updated ticket';

            $nameParts = explode(' ', trim($ticket->user_name));
            $initials  = strtoupper(
                (isset($nameParts[0]) ? substr($nameParts[0], 0, 1) : '') .
                    (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '')
            );

            return [
                'id'             => $ticket->ticket_id,
                'user'           => $ticket->user_name,
                'action'         => $action,
                'target'         => $ticket->ticket_number ?? $ticket->subject,
                'subject'        => $ticket->subject,
                'status'         => $ticket->status,
                'sts_name'       => $ticket->sts_name,
                'color'          => $color,
                'avatarInitials' => $initials ?: 'U',
                'time'           => $ticket->updated_at,
                'dist_name'      => $ticket->dist_name,
            ];
        });

        return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
    }


    public function getMonthlyTicketCount(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $query = TicketMaster::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        );

        $query = $this->roleCondition($query);

        $monthlyTickets = $query->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        ];
        $finalData = [];
        foreach ($months as $key => $month) {
            $found = $monthlyTickets->firstWhere('month', $key);
            $finalData[] = [
                'month' => $month,
                'count' => $found ? $found->total : 0
            ];
        }

        return response()->json(['status' => true, 'data' => $finalData]);
    }

    public function saveTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'pacs_code' => 'required',
            'pacs_name' => 'required',
            'product_id' => 'required',
            'module_id' => 'nullable',
            'task_id' => 'nullable',
            'subject' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => true, 'message' => "Validation Error", 'code' => 501]);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => true, 'message' => "Unauthorized", 'code' => 401]);
        }

        $loginId = $user->user_id;
        $ticket_id = date('YmdHis') . $loginId;
        $pacs = DB::table('pacs')->where('id', $request->branch_id)->first();

        $data = new TicketMaster();
        $data->ticket_id = $ticket_id;
        // $data->socity_id = $pacs;
        // $data->socity_id = $pacs->id ?? null;
        $data->socity_id = $pacs->id ?? null;
        $data->dist_id   = $pacs->dist_id ?? null;
        $data->taluka_id = $pacs->taluka_id ?? null;
        $data->pacs_code = $request->pacs_code;
        $data->pacs_name = $request->pacs_name;
        $data->product_id = $request->product_id;
        $data->module_id = $request->module_id;
        $data->task_id = $request->task_id;
        $data->subject = $request->subject;
        $data->priority = $request->priority;
        $data->description = $request->description;
        $data->status = 1;

        $data->created_by = $user->user_id;
        $data->created_by_name = $user->name;
        $data->updated_by = $user->user_id;
        $data->seen_by = null;
        $data->closed_by = null;
        $data->closed_by_name = null;

        $result = $data->save();
        if ($result) {
            $TicketReplies = [];
            $files = $request->file('file') ?? $request->file('upload_file');

            if ($files) {
                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $fileKey => $fileVal) {
                    if ($fileVal) {
                        $filePath = public_path('uploads');
                        $file_path = time() . '_' . uniqid() . '_' . $fileVal->getClientOriginalName();
                        $fileVal->move($filePath, $file_path);

                        $TicketReplies[] = [
                            'ticket_id'      => $ticket_id,
                            'ticket_details' => $request->description,
                            'inserted_by'    => $user->user_id,
                            'file_path'      => $file_path,
                            'created_at'     => date('Y-m-d H:i:s'),
                            'updated_at'     => date('Y-m-d H:i:s'),
                        ];
                    }
                }
            }

            if (empty($TicketReplies)) {
                $TicketReplies[] = [
                    'ticket_id'      => $ticket_id,
                    'ticket_details' => $request->description,
                    'inserted_by'    => $user->user_id,
                    'file_path'      => null,
                    'created_at'     => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ];
            }

            DB::table('ticket_replies')->insert($TicketReplies);

            return response()->json(['status' => true, 'message' => "Ticket Created Successfully", 'ticket_id' => $ticket_id, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Failed to save ticket", 'code' => 500]);
        }
    }
    // Forward ticket to another user (L2)
    public function forwardTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'forward_to' => 'required',
            'ticket_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation Error', 'code' => 422]);
        }

        $ticket = TicketMaster::where('ticket_id', $request->ticket_id)->first();

        if (!$ticket) {
            return response()->json(['status' => false, 'message' => 'Ticket not found', 'code' => 404]);
        }


        // Update ticket status to forwarded (5) and assign to the selected user
        $ticket->status = 5;
        $ticket->forward_to = $request->forward_to;
        $ticket->updated_by = Auth::user()->user_id ?? null;
        $ticket->save();

        // Optionally add a reply indicating forwarding
        DB::table('ticket_replies')->insert([
            'ticket_id' => $ticket->ticket_id,
            'ticket_details' => 'Ticket forwarded to user ID ' . $request->forward_to,
            'inserted_by' => Auth::user()->user_id ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => true, 'message' => 'Ticket forwarded successfully', 'code' => 200]);
    }

    public function getTicketReply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'status'      => 'required',
            'ticket_id'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code'    => 422
            ]);
        }

        $data = TicketMaster::where('ticket_id', $request->ticket_id)->first();

        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Ticket not found', 'code' => 404]);
        }
        // $data->description = $request->new_description;
        $data->new_description = $request->description;
        $data->status = $request->status;
        $data->save();

        $filename = null;
        if ($request->hasFile('upload_file')) {
            $file = $request->file('upload_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
        }


        DB::table('ticket_replies')->insert([
            'ticket_id'     => $request->ticket_id,
            'ticket_details' => $request->description,
            'file_path'     => $filename,
            'inserted_by'   => Auth::user()->user_id ?? null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(['status' => true, 'message' => 'Reply submitted successfully', 'code' => 200]);
    }
}
