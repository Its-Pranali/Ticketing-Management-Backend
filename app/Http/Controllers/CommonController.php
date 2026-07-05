<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Priority;
use App\Models\TicketStatus;

class CommonController extends Controller
{
    public function getPriority()
    {
        $data = Priority::get();
        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }

    public function getStatus()
    {
        $data = TicketStatus::get();
        if (!empty($data)) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while fetching data", 'code' => 500]);
        }
    }
}
