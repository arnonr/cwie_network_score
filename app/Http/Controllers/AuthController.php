<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    const ERROR_NONE = 0;
    const ERROR_API_FAIL = 1;
    const ERROR_INVALID_CREDENTIALS = 2;
    const ERROR_INTERNAL = 3;
    const ERROR_CREATE_USER = 4;  
    const ERROR_UPDATE_USER = 5;

    public $errorCode;
    public $errorMessage;

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $userDB = User::where('username', $request->username)->first();

        $check = 0;
        if($userDB){
            $check = 1;
            if (Hash::check($request->password, $userDB->password)){
                $check = 2; 
            }
        }
      
        if($check == 2){

            $tokenResult = $userDB->createToken('Personal Access Token');
            $token = $tokenResult->token;
            if ($request->remember_me)
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

            if($userDB->status == 2){
                array_push($ability, [
                    'action' => 'manage',
                    'subject'=> 'User',
                ]);
                if($userDB->type == 'referee'){
                    $role = 'referee';
                    array_push($ability, [
                        'action' => 'manage',
                        'subject'=> 'RefereeUser',
                    ]);
                }else if($userDB->type == 'staff'){
                    $role = 'staff';
                    array_push($ability, [
                        'action' => 'manage',
                        'subject'=> 'StaffUser',
                    ]);
                }else if($userDB->type == 'admin'){
                    $role = 'admin';
                    // array_push($ability, [
                    //     'action' => 'manage',
                    //     'subject'=> 'RefereeUser',
                    // ]);
                    array_push($ability, [
                        'action' => 'manage',
                        'subject'=> 'StaffUser',
                    ]);
                    array_push($ability, [
                        'action' => 'manage',
                        'subject'=> 'AdminUser',
                    ]);
                }else{

                }
            }else{
                $role = 'block';
                array_push($ability, [
                    'action' => 'manage',
                    'subject'=> 'BlockUser',
                ]);
            }

        }else{
            return response()->json([
                'message' => $check == 1 ? 'Password Not Match': 'Username Not Found',
                'text' => bcrypt($request->password)
            ], 200);
        }


        $user = [
            'userID' => $userDB->id,
            'email' => $userDB->email,
            'username' => $userDB->username,
            'fullName' =>  $userDB->prefix.''.$userDB->firstname.' '.$userDB->lastname,
            'avatar' => $userDB->avatar,
            'type' => $userDB->type,
            'status' => $userDB->status,
            'project_type_id' => $userDB->project_type_id,
            'project_type_arr' => $userDB->project_type_arr,
            'role' => $role,
            'ability' => $ability,
        ];

        return response()->json([
            'message' => 'success',
            'userData' => $user,
            'accessToken' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ], 200);
    }

    public function icitAccountApi($username, $password)
    {
        $this->errorCode = self::ERROR_NONE;

        $access_token = '5UaTyf96aWgeAeha912oqF-9vtMc_LiZ'; // <----- API - Access Token Here
        
        $data = [
            'scopes' => "personel,student,exchange_student",  // <----- Scopes for search account type
            'username' => $username, // <----- Username for authen
            'password' => $password,  // <----- Password for authen
        ];

        $api_url = 'https://api.account.kmutnb.ac.th/api/account-api/user-authen'; // <----- API URL

        $response = Http::timeout(50)->withToken($access_token)->post($api_url, $data);

        // return $response->json();
        
        if($response == false){
            $this->errorCode = self::ERROR_API_FAIL;
            $this->errorMessage = 'HTTP Guzzle error';
        }else{
            $json_data = json_decode($response, true);
                
            if(!isset($json_data['api_status'])){
                $this->errorCode = self::ERROR_INTERNAL;
                $this->errorMessage = 'API Error ' . print_r($response, true);
            }else if($json_data['api_status'] == 'success'){
                // $userDB = User::where('icit_name', $json_data['userInfo']['displayname'])->first();
                $userDB = User::where('username', $username)->first();
                
                // New User
                if(!$userDB){
                    $this->errorCode = self::ERROR_INVALID_CREDENTIALS;
                    $this->errorMessage = "ไม่มีสิทธิ์เข้าใช้งาน";
                    // $nameArray = explode(" ", $json_data['userInfo']['displayname']);
                    // $lastname = '';
                    // for($i = 0; $i < count($nameArray); $i++) {
                    //     if($i != 0){
                    //         $lastname = $lastname . " " . $nameArray[$i];
                    //     }
                    // }

                    // $userDB = new User;
                    // $userDB->pid = $json_data['userInfo']['pid'];
                    // $userDB->account_type = $json_data['userInfo']['account_type'];
                    // $userDB->email = $json_data['userInfo']['email'];
                    // $userDB->username = $username;
                    // $userDB->firstname = $nameArray[0];
                    // $userDB->lastname = $lastname;
                    // $userDB->status = 1;

                    // if($json_data['userInfo']['account_type'] == 'students'){
                    //     $userDB->type = 'student';
                        
                    // }   

                }else{
                    $nameArray = explode(" ", $json_data['userInfo']['displayname']);
                    $lastname = '';
                    for($i = 0; $i < count($nameArray); $i++) {
                        if($i != 0){
                            $lastname = $lastname . " " . $nameArray[$i];
                        }
                    }

                    $userDB->pid = $json_data['userInfo']['pid'];
                    $userDB->account_type = $json_data['userInfo']['account_type'];

                    if($userDB->email == null){
                        $userDB->email = $json_data['userInfo']['email'];
                    }
                    
                    $userDB->username = $username;
                    $userDB->firstname = $nameArray[0];
                    $userDB->lastname = $lastname;
                    $userDB->status = 2;

                    $userDB->icit_name = $json_data['userInfo']['displayname'];
                    $userDB->icit_email = $json_data['userInfo']['email'];
                    $userDB->password = bcrypt($password);
                    $userDB->save();
                }

            }else if($json_data['api_status'] == 'fail'){
                $this->errorCode = self::ERROR_INVALID_CREDENTIALS;
                $this->errorMessage = $json_data['api_message'];
            }else{
                $this->errorCode = self::ERROR_INTERNAL;
                $this->errorMessage = 'Unable to Authenticate';
            }
        }
        
        return $this->errorCode;

    } /* !--authenticate() */

    public function logout(Request $request)
    {
      $request->user()->token()->revoke();
      return response()->json([
        'message' => 'Successfully logged out'
      ]);
    }
}
