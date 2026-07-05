<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\TicketMaster;
use Illuminate\Support\Facades\DB;

class NLPSVController extends Controller
{
    public function NLPSVLoginApi()
    {
        $url       = config('constants.api_url');
        $api_key   = config('constants.api_key');
        $user_name = config('constants.user_name');
        $password  = config('constants.password');

        $nlpsv_login_api = $url . "?api_key=" . $api_key;

        $result = Http::post($nlpsv_login_api, [
            'api_key'   => $api_key,
            'user_name' => $user_name,
            'password'  => $password
        ]);
        return $result;
    }

    public function getNLPSVStatus(Request $request)
    {
        $tId                = $request->tId;
        $ticket_id          = $request->ticket_id;
        $ticket_number      = $request->ticket_number;
        $ticket_response_id = $request->ticket_response_id;

        $api_key            = config('constants.api_key');
        $ticket_status_url  = config('constants.get_ticket_status_comments');

        $result = $this->NLPSVLoginApi();

        if (!$result->successful()) {
            return response()->json(['code' => 500, 'message' => 'Error While Login Please Check Login Details !', 'status' => false]);
        }

        $data  = $result->json();
        $token = $data['data']['token'];

        $ticketApiData = DB::table('ticket_api_data')
            ->where('ticket_id', $ticket_id)
            ->first();

        if (!$ticketApiData) {
            return response()->json(['code' => 500, 'message' => 'Ticket Details Not Found', 'status' => false]);
        }

        $ticket_encrypted_id = $ticketApiData->encrypted_id;

        $url_with_api_key = $ticket_status_url . $ticket_encrypted_id . "?api_key=" . $api_key;

        $statusResponse = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'Accept'        => 'application/json',
        ])->get($url_with_api_key);

        if ($statusResponse->failed()) {
            if ($statusResponse->clientError()) {
                return response()->json(['code' => 500, 'message' => 'Client error occurred: ' . $statusResponse->status(), 'status' => false]);
            } elseif ($statusResponse->serverError()) {
                return response()->json(['code' => 500, 'message' => 'Server error occurred: ' . $statusResponse->status(), 'status' => false]);
            }
            return response()->json(['code' => 500, 'message' => 'Unknown error occurred', 'status' => false]);
        }

        $dataTicket    = $statusResponse->json();
        $statusInLower = trim($dataTicket['data']['ticket']['status']);

        $ticketMaster = TicketMaster::where('ticket_id', $ticket_id)->first();

        if (!$ticketMaster) {
            return response()->json(['code' => 500, 'message' => 'Record Not Found!', 'status' => false]);
        }

        if (strtolower($statusInLower) === 'closed') {
            $ticketMaster->status = 4;
            $ticketMaster->save();
        }

        return response()->json([
            'code'    => 200,
            'message' => "Ticket No - " . $ticket_id . " And Status Is " . $statusInLower,
            'ticketStatus' => $statusInLower,
            'status'  => true
        ]);
    }
}
