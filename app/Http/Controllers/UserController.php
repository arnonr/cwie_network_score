<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function get($id)
    {
        // User DB
        $userDB = User::select(
                'user.id as id',
                'user.email as email',
                'user.username as username',
                'user.prefix as prefix',
                'user.firstname as firstname',
                'user.lastname as lastname',
                'user.type as type',
                'user.avatar as avatar',
                'user.status as status',
                'user.tel as tel',
                'user.project_type_id as project_type_id',
                'project_type.name as project_type_name',
                'project_type_arr as project_type_arr'
            )
            ->leftJoin('project_type','project_type.id','=','user.project_type_id')
            ->where('user.id', $id)
            ->first();

        
        $tokenResult = $userDB->createToken('Personal Access Token');
        $token = $tokenResult->token;
 
        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        $ability = [
            [
                'subject'=> 'Auth',
                'action'=> 'manage',
            ],
            [
                'subject'=> 'Auth',
                'action'=> 'read',
            ]
        ];

        if($userDB->status == 1){
            $role = 'wating-approve-user';

            array_push($ability, [
                'action' => 'manage',
                'subject'=> 'WatingApproveUser',
            ]);
        }else if($userDB->status == 2){

            array_push($ability, [
                'action' => 'manage',
                'subject'=> 'User',
            ]);

            if($userDB->type == 'staff'){
                $role = 'staff';
                array_push($ability, [
                    'action' => 'manage',
                    'subject'=> 'StaffUser',
                ]);
            }else if($userDB->type == 'admin'){
                $role = 'admin';
                array_push($ability, [
                    'action' => 'manage',
                    'subject'=> 'AdminUser',
                ]);
            }else{

            }
        }else if ($userDB->status == 3){
            $role = 'block';
            array_push($ability, [
                'action' => 'manage',
                'subject'=> 'BlockUser',
            ]);
        }else{

        }


        $userData = [
            'userID' => $userDB->id,
            'email' => $userDB->email ? $userDB->email : '',
            'username' => $userDB->username,
            'prefix' => $userDB->prefix ? $userDB->prefix : '',
            'firstname' => $userDB->firstname,
            'lastname' => $userDB->lastname,
            'type' => $userDB->type,
            'status' => $userDB->status,
            'avatar' =>  $userDB->avatar ? $userDB->avatar : '',
            'tel' => $userDB->tel,
            'project_type_id' => $userDB->project_type_id,
            'project_type_name' => $userDB->project_type_name ? $userDB->project_type_name : '',
            'fullName' => $userDB->firstname.' '.$userDB->lastname,
            'ability' => $ability,
            'role' => $role,
        ];

        return response()->json([
            'message' => 'success',
            'user' => $data,
            'userData' => $userData,
            'accessToken' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ], 200);
    }

    public function getAll(Request $request)
    {
        // User DB
        $items = User::select(
            'user.id as id',
            'user.username as username',
            'user.prefix as prefix',
            'user.firstname as firstname',
            'user.lastname as lastname',
            'user.email as email',
            'user.type as type',
            'user.status as status',
            'user.tel as tel',
            'user.project_type_id as project_type_id',
            'project_type.name as project_type_name',
            'project_type_arr as project_type_arr'
        )
        ->leftJoin('project_type','project_type.id','=','user.project_type_id')
        ->where('user.deleted_at', null);

        if ($request->id) {
            $items->where('user.id', $request->id);
        }

        if ($request->username) {
            $items->where('user.username','LIKE',"%". $request->username."%");
        }
        if ($request->firstname) {
            $items->where('user.firstname', 'LIKE',"%". $request->firstname."%");
        }
        if ($request->lastname) {
            $items->where('user.lastname',  'LIKE',"%". $request->lastname."%");
        }

        if ($request->fullname) {
            $items->whereRaw("concat(prefix,firstname, ' ', lastname) like '%" .$request->fullname. "%' ");
        }

        if ($request->email) {
            $items->where('user.email','LIKE',"%".  $request->email."%");
        }
        if ($request->type) {
            $items->where('user.type', $request->type);
        }
        if ($request->project_type_id) {
            $items->where('user.project_type_id', $request->project_type_id);
        }
        if ($request->status) {
            $items->where('user.status', $request->status);
        }


        if($request->orderBy){
            $items = $items->orderBy($request->orderBy, $request->order);
            
        }else{
            $items = $items->orderBy('id', 'desc');
        }
    
        $count = $items->count();
        $perPage = $request->perPage ? $request->perPage : 10;
        $currentPage = $request->currentPage ? $request->currentPage : 1;

        $totalPage = ceil($count /$perPage) == 0 ? 1 : ceil($count / $perPage);
        $offset = $perPage * ($currentPage - 1);
        $items = $items->skip($offset)->take($perPage);
        $items = $items->get();
    
        return response()->json([
            'message' => 'success',
            'data' => $items,
            'totalPage' => $totalPage,
            'totalData' => $count,
        ], 200);
    }

    public function add(Request $request)
    {
        $request->validate([
            'username as required',
            'email as required',
            'type as required',
        ]);

        $data = new User;
        $data->username = $request->email;
        $data->prefix = $request->prefix;
        $data->firstname = $request->firstname;
        $data->lastname = $request->lastname;
        $data->email = $request->email;
        $data->type = $request->type;
        $data->status = $request->status;
        $data->tel = $request->tel;
        $data->password = bcrypt($request->tel);
        $data->project_type_id = $request->project_type_id;
        $data->project_type_arr = $request->project_type_arr;

        $data->save();

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];

        return response()->json($responseData, 200);
    }

    public function edit($id, Request $request)
    {
        $request->validate([
            'id as required',
            'email as required',
            'type as required',
        ]);

        $data = User::where('id',$id)->first();

        $data->username = $request->email;
        $data->prefix = $request->prefix;
        $data->firstname = $request->firstname;
        $data->lastname = $request->lastname;
        $data->email = $request->email;
        $data->type = $request->type;
        $data->status = $request->status;
        $data->tel = $request->tel;
        $data->project_type_id = $request->project_type_id;
        $data->project_type_arr = $request->project_type_arr;

        $data->password = bcrypt($request->tel);
        $data->save();

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];

        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $data = User::where('id', $id)->first();
        
        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }
}
