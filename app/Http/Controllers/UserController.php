<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\UsersAssignDist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    public function userList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = trim($request->input('search'));

        $query = User::leftJoin('districts as d', 'd.id', '=', 'users.dist_id')
            ->leftJoin('roles as r', 'r.id', '=', 'users.role')
            ->leftJoin('designations as desg', 'desg.id', '=', 'users.designation')
            ->leftJoin('organizations as org', 'org.id', '=', 'users.organization')
            ->select('users.*', 'd.dist_name', 'r.role', 'desg.designation', 'org.org_name')
            ->where('users.status', 1);
        // dd($query->toSql());

        // return response()->json(['status'=>true,'message'=>$query->toSql(),'code'=>200]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.name_ll', 'like', "%{$search}%")
                    ->orWhere('users.mobile', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('r.role', 'like', "%{$search}%")
                    ->orWhere('d.dist_name', 'like', "%{$search}%")
                    ->orWhere('users.designation', 'like', "%{$search}%")
                    ->orWhere('users.organization', 'like', "%{$search}%");
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
            'name' => 'required|string|max:255',
            'mobile' => 'required|unique:users|numeric|digits:10',
            'email' => 'required|email|unique:users,email|regex:/(.+)@(.+)\.(.+)/i',
            'role' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 500]);
        }
        $distIds = $request->input('dist_id');
        $dist_id = $distIds ? explode(",", $distIds) : [];

        $data = new User();
        $data->name = $request->name;
        $data->name_ll = $request->name_ll;
        $data->email = $request->email;
        $data->mobile = $request->mobile;
        $data->designation = $request->designation;
        $data->organization = $request->organization;
        $data->role = $request->role;
        $data->society_id = $request->society_id;
        $data->status = 1;

        if ($request['role'] != 7 && $request['role'] != 8 && $request['role'] != 9) {
            if ($request->has('dist_id')) {
                if (!empty($request['dist_id']) && !empty($dist_id)) {
                    $data->dist_id = $dist_id[0];
                } else {
                    $data->dist_id = null;
                }
            }
        }

        $data->password = \Hash::make("Test@123");

        $result = $data->save();
        $lastInsertedUserID = $data->user_id;

        if ($result) {
            if ($request['role'] != 7 && $request['role'] != 8 && $request['role'] != 9 && $request['role'] != 1) {
                $data_user_dist = [];
                for ($i = 0; $i < count($dist_id); $i++) {
                    if (!empty($dist_id[$i])) {
                        $data_user_dist[] = [
                            'user_id' => $lastInsertedUserID,
                            'dist_id' => $dist_id[$i],
                            'created_by' => auth()->id()
                        ];
                    }
                }
                if (!empty($data_user_dist)) {
                    DB::table('users_assign_dist')->insert($data_user_dist);
                }
            }
            return response()->json(['data' => 'Saved Successful !', 'code' => true]);
        } else {
            return response()->json(['data' => 'Note Saved !', 'code' => false]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|numeric|digits:10|unique:users,mobile,' . $id . ',user_id',
            'email'  => 'required|email|unique:users,email,' . $id . ',user_id|regex:/(.+)@(.+)\.(.+)/i',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => "Validation Error", 'code' => 422]);
        }

        $distIds = $request->input('dist_id');
        $dist_id = $distIds ? explode(",", $distIds) : [];

        $data = User::find($id);
        $data->name = $request->name;
        $data->name_ll = $request->name_ll;
        $data->email = $request->email;
        $data->mobile = $request->mobile;
        $data->designation = $request->designation;
        $data->organization = $request->organization;
        $data->role = $request->role;
        $data->society_id = $request->society_id;
        $data->status = 1;

        if ($request['role'] != 7 && $request['role'] != 8 && $request['role'] != 9) {
            if ($request->has('dist_id')) {
                if (!empty($request['dist_id']) && !empty($dist_id)) {
                    $data->dist_id = $dist_id[0];
                } else {
                    $data->dist_id = null;
                }
            }
        }

        $data->password = \Hash::make("Test@123");
        $result = $data->save();
        $lastInsertedUserID = $data->user_id;

        if ($result) {
            if ($request['role'] != 7 && $request['role'] != 8 && $request['role'] != 9 && $request['role'] != 1) {
                DB::table('users_assign_dist')->where('user_id', $lastInsertedUserID)->delete();

                $data_user_dist = [];
                for ($i = 0; $i < count($dist_id); $i++) {
                    if (!empty($dist_id[$i])) {
                        $data_user_dist[] = [
                            'user_id' => $lastInsertedUserID,
                            'dist_id' => $dist_id[$i],
                            'created_by' => auth()->id()
                        ];
                    }
                }
                if (!empty($data_user_dist)) {
                    DB::table('users_assign_dist')->insert($data_user_dist);
                }
            }
            return response()->json(['data' => 'Updated Successful !', 'code' => true]);
        } else {
            return response()->json(['data' => 'Not Updated !', 'code' => false]);
        }
    }

    public function delete($id)
    {
        $data = User::findOrFail($id);
        $data->status = 0;
        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Data deleted successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while deleting data", 'code' => 500]);
        }
    }

    public function changeUserPassword(Request $request)
    {
        $user_id = $request->user_id;
        $password = $request->new_password;
        $data = User::where('user_id', $user_id)->first();
        $data->password = Hash::make($password);
        $result = $data->save();
        if ($result) {
            return response()->json(['status' => true, 'message' => "Password changed successfully", 'code' => 200]);
        } else {
            return response()->json(['status' => false, 'message' => "Error while changing password", 'code' => 500]);
        }
    }

    public function getUserByRoleId($role)
    {
        $user = Auth::user();
        $dist_id = $user->dist_id;

        if ($role == 1) {
            $data = User::where('role', $role)
                ->whereNull('deleted_at')
                ->get();
            return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
        } else {
            if ($dist_id != '' && $dist_id != null) {
                $data = User::where('role', $role)
                    ->where('dist_id', $dist_id)
                    ->whereNull('deleted_at')
                    ->get();
                return response()->json(['status' => true, 'message' => $data, 'code' => 200]);
            }
            return response()->json(['status' => false, 'message' => "District not found", 'code' => 500]);
        }
    }
}
