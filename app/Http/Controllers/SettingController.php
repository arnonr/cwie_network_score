<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingController extends Controller
{

    public function get($id)
    {
        // User DB
        $data = Setting::select(
            'id as id',
            'is_close as is_close',
            'is_final_close as is_final_close',
            'is_publish as is_publish',
            'deleted_at as delete_at',
            'created_at as created_at',
            'created_by as created_by',
            'updated_at as updated_at',
            'updated_by as updated_by',
        )
        ->where('id', $id)
        ->first();
        
        return response()->json([
            'message' => 'success',
            'data' => $data,
        ], 200);
    }
}
