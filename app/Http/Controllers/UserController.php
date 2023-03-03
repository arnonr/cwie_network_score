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
                'users.id as id',
                'users.email as email',
                'users.username as username',
                'users.prefix as prefix',
                'users.firstname as firstname',
                'users.lastname as lastname',
                'users.type as type',
                'users.avatar as avatar',
                'users.status as status',
                'department.id as departmentID',
                'department.name as departmentName',
            )
            ->where('users.id', $id)
            ->first();

        $data = [
            'userID' => $userDB->id,
            'email' => $userDB->email ? $userDB->email : '',
            'prefix' => $userDB->prefix ? $userDB->prefix : '',
            'firstname' => $userDB->firstname,
            'lastname' => $userDB->lastname,
            'type' => $userDB->type,
            'status' => $userDB->status,
            'avatar' =>  $userDB->avatar ? $userDB->avatar : '',
        ];
        
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
            'email' => $userDB->email,
            'username' => $userDB->username,
            'fullName' => $userDB->firstname.' '.$userDB->lastname,
            'avatar' => $userDB->avatar,
            'type' => $userDB->type,
            'status' => $userDB->status,
            'department' => ['id' => $userDB->departmentID,'name' => $userDB->departmentName],
            'role' => $role,
            'ability' => $ability,
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
            'user.firstname as firstname',
            'user.lastname as lastname',
            'user.email as email',
            'user.type as type',
            'user.status as status',
        )
        ->where('user.deleted_at', null);

        if ($request->id) {
            $items->where('user.id', $request->id);
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
        $data->username = $request->username;
        $data->email = $request->email;
        $data->type = $request->type;
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

        $data->email = $request->email;
        $data->type = $request->type;
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
