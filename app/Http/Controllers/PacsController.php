<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pacs;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PacsController extends Controller
{
    public function index()
    {
        $data = Pacs::leftJoin('states as s', 's.id', '=', 'pacs.state_id')
            ->leftJoin('districts as d', 'd.id', '=', 'pacs.dist_id')
            ->leftJoin('talukas as t', 't.id', '=', 'pacs.taluka_id')
            ->leftJoin('banks as bk', 'bk.id', '=', 'pacs.bank_id')
            ->leftJoin('branches as b', 'b.id', '=', 'pacs.branch_id')
            ->leftJoin('villages as v', 'v.id', '=', 'pacs.village_id')
            ->select('pacs.*', 's.state', 'd.dist_name', 't.taluka_name', 'bk.bank_name', 'b.branch_name')
            ->where('pacs.status', 1)
            ->get();

        if ($data) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while getting data", 'code' => 500]);
        }
    }

    public function getPacsById($id)
    {
        $data = pacs::join('districts as d', 'd.id', '=', 'pacs.dist_id')
            ->select('pacs.*', 'd.dist_name')
            ->find($id);
        if ($data) {
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while getting data", 'code' => 500]);
        }
    }

    public function pacsList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = Pacs::join('states as s', 's.id', '=', 'pacs.state_id')
            ->join('districts as d', 'd.id', '=', 'pacs.dist_id')
            ->join('talukas as t', 't.id', '=', 'pacs.taluka_id')
            ->join('banks as bk', 'bk.id', '=', 'pacs.bank_id')
            ->join('branches as b', 'b.id', '=', 'pacs.branch_id')
            ->select('pacs.*', 's.state', 'd.dist_name', 't.taluka_name', 'bk.bank_name', 'b.branch_name')
            ->where('pacs.status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('s.state_name', 'like', "%{$search}%")
                    ->orWhere('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('t.taluka_name', 'like', "%{$search}%")
                    ->orWhere('bk.bank_name', 'like', "%{$search}%")
                    ->orWhere('b.branch_name', 'like', "%{$search}%")
                    ->orWhere('pacs.village_name', 'like', "%{$search}%")
                    ->orWhere('pacs.nabard_pacs_id', 'like', "%{$search}%")
                    ->orWhere('pacs.pacs_id', 'like', "%{$search}%")
                    ->orWhere('pacs.pacs_name', 'like', "%{$search}%")
                    ->orWhere('pacs.pacs_name_ll', 'like', "%{$search}%")
                    ->orWhere('pacs.ceo_name', 'like', "%{$search}%")
                    ->orWhere('pacs.ceo_mobile', 'like', "%{$search}%")
                    ->orWhere('pacs.ceo_email', 'like', "%{$search}%");
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
        $request['email'] = $request['ceo_email'];
        $request['mobile'] = $request['ceo_mobile'];

        if ($request->input('ceo_mobile') != "" && $request->input('ceo_mobile') != null) {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|unique:users,mobile|numeric|digits:10',
                'email'  => 'required|unique:users,email',
                'dist_id' => 'required',
                'taluka_id' => 'required',
                'bank_id' => 'required',
                'branch_id' => 'required',
                'village_id' => 'required',
                'nabard_pacs_id' => 'required|string|max:255',
                'pacs_id' => 'required|string|max:255',
                'pacs_name' => 'required|string|max:255',
                'pacs_name_ll' => 'required|string|max:255',
                'ceo_name' => 'required|string|max:255',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'email'  => 'required|unique:users,email',
                'dist_id' => 'required',
                'taluka_id' => 'required',
                'bank_id' => 'required',
                'branch_id' => 'required',
                'village_id' => 'required',
                'nabard_pacs_id' => 'required|string|max:255',
                'pacs_id' => 'required|string|max:255',
                'pacs_name' => 'required|string|max:255',
                'pacs_name_ll' => 'required|string|max:255',
                'ceo_name' => 'required|string|max:255',
            ]);
        }

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors(), 'code' => 422]);
        }

        $pacs = new Pacs();
        $pacs->state_id = 1;
        $pacs->dist_id = $request->dist_id;
        $pacs->taluka_id = $request->taluka_id;
        $pacs->bank_id = $request->bank_id;
        $pacs->branch_id = $request->branch_id;
        $pacs->village_id = $request->village_id;
        $pacs->nabard_pacs_id = $request->nabard_pacs_id;
        $pacs->pacs_id = $request->pacs_id;
        $pacs->pacs_name = $request->pacs_name;
        $pacs->pacs_name_ll = $request->pacs_name_ll;
        $pacs->ceo_name = $request->ceo_name;
        $pacs->status = 1;

        if ($request->input('ceo_mobile') != "" && $request->input('ceo_mobile') != null) {
            $pacs->ceo_mobile = $request->ceo_mobile;
        } else {
            $pacs->ceo_mobile = "";
        }

        $pacs->ceo_email = $request->ceo_email;
        $savePacs = $pacs->save();

        $lastInsertedId = $pacs->id;

        if ($savePacs) {
            $user = new User();
            $user->name = $request->pacs_name;
            $user->name_ll = $request->pacs_name_ll;
            $user->email = $request->ceo_email;
            $user->mobile = $request->ceo_mobile;
            $user->dist_id = $request->dist_id;
            $user->society_id = $lastInsertedId;
            $user->password = \Hash::make('Pass@123');
            $user->role = 2;
            $user->status = 1;

            $result = $user->save();

            if ($result) {
                return response()->json(['status' => true, 'message' => 'Saved Successfully!', 'code' => 200]);
            } else {
                return response()->json(['status' => false, 'message' => 'Error while adding user!', 'code' => 500]);
            }
        }

        return response()->json(['status' => false, 'message' => 'Error while saving PACS!', 'code' => 500]);
    }


    public function update(Request $request, $id)
    {
        $pacs = Pacs::find($id);

        if (!$pacs) {
            return response()->json(['status' => false, 'message' => 'PACS not found!', 'code' => 404]);
        }

        $user = User::where('society_id', $id)->first();
        $userId = $user ? $user->user_id : 'NULL';

        $request['email'] = $request['ceo_email'];
        $request['mobile'] = $request['ceo_mobile'];

        if ($request->input('ceo_mobile') != "" && $request->input('ceo_mobile') != null) {
            $validator = Validator::make($request->all(), [
                'mobile'         => 'required|numeric|digits:10|unique:users,mobile,' . $userId . ',user_id',
                'email'          => 'required|unique:users,email,' . $userId . ',user_id',
                'dist_id'        => 'required',
                'taluka_id'      => 'required',
                'bank_id'        => 'required',
                'branch_id'      => 'required',
                'village_id'     => 'required',
                'nabard_pacs_id' => 'required|string|max:255',
                'pacs_id'        => 'required|string|max:255',
                'pacs_name'      => 'required|string|max:255',
                'pacs_name_ll'   => 'required|string|max:255',
                'ceo_name'       => 'required|string|max:255',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'email'          => 'required|unique:users,email,' . $userId . ',user_id',
                'dist_id'        => 'required',
                'taluka_id'      => 'required',
                'bank_id'        => 'required',
                'branch_id'      => 'required',
                'village_id'     => 'required',
                'nabard_pacs_id' => 'required|string|max:255',
                'pacs_id'        => 'required|string|max:255',
                'pacs_name'      => 'required|string|max:255',
                'pacs_name_ll'   => 'required|string|max:255',
                'ceo_name'       => 'required|string|max:255',
            ]);
        }

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors(), 'code' => 422]);
        }

        $pacs->dist_id        = $request->dist_id;
        $pacs->taluka_id      = $request->taluka_id;
        $pacs->bank_id        = $request->bank_id;
        $pacs->branch_id      = $request->branch_id;
        $pacs->village_id     = $request->village_id;
        $pacs->nabard_pacs_id = $request->nabard_pacs_id;
        $pacs->pacs_id        = $request->pacs_id;
        $pacs->pacs_name      = $request->pacs_name;
        $pacs->pacs_name_ll   = $request->pacs_name_ll;
        $pacs->ceo_name       = $request->ceo_name;
        $pacs->ceo_mobile     = ($request->input('ceo_mobile') != "" && $request->input('ceo_mobile') != null)
            ? $request->ceo_mobile
            : "";
        $pacs->ceo_email      = $request->ceo_email;

        $updatePacs = $pacs->save();

        if ($updatePacs) {
            if ($user) {
                $user->name     = $request->pacs_name;
                $user->name_ll  = $request->pacs_name_ll;
                $user->email    = $request->ceo_email;
                $user->mobile   = $request->ceo_mobile;
                $user->dist_id  = $request->dist_id;
                $user->save();
            }
            return response()->json(['status' => true, 'message' => 'Updated Successfully!', 'code' => 200]);
        }
        return response()->json(['status' => false, 'message' => 'Error while updating PACS!', 'code' => 500]);
    }


    public function delete($id)
    {
        $data = Pacs::findOrFail($id);
        $data->status = 0;
        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Data deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleting data", 'code' => 500]);
        }
    }

    public function getSocitySectionId()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized', 'code' => 401]);
        }

        $query = Pacs::join('branches as b', 'b.id', '=', 'pacs.branch_id')
            ->select('pacs.*', 'b.branch_name', 'b.id as branch_id')
            ->where('pacs.status', 1)
            ->where('pacs.id', $user->society_id);

        // $socity = $user->society_id;
        // $query->where('pacs.pacs_name', $socity);

        // return $user->society_id;

        // if ($user->role != 1) {
        //     $society_id = trim($user->society_id);
        //     // return $society_id;
        //     if (!empty($society_id)) {
        //         $user_array = array_filter(array_map('trim', explode(',', $society_id)));
        //         $query->whereIn('pacs.branch_id', $user_array);
        //     } else {
        //         $query->whereRaw('1 = 0');
        //     }
        // }

        $result = $query->get();

        if ($result && $result->count()) {
            return response()->json(['status' => true, 'message' => $result, 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Data not found", 'code' => 500]);
        }
    }
}
