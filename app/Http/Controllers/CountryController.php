<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CountryController extends Controller
{
    public function getAll(Request $request)
    {
        // User DB
        $data = Country::select(
            'ct_code as ct_code',
            'ct_nameENG as ct_nameENG',
            'ct_nameTHA as ct_nameTHA',
            'code as code',
        );
        
        if ($request->ct_code) {
            $data->where('ct_code',$request->ct_code);
        }

        if ($request->ct_nameENG) {
            $data->where('ct_nameENG','LIKE',"%".$request->ct_nameENG."%");
        }

        if ($request->ct_nameTHA) {
            $data->where('ct_nameTHA','LIKE',"%".$request->ct_nameTHA."%");
        }

        if ($request->code) {
            $data->where('code',$request->code);
        }

        $order_by = $request->order_by ? $request->order_by : 'ct_code';
        $order = $request->order ? $request->order : 'asc';

        $data = $data->orderBy($order_by, $order);
            
        $data = $data->get();

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ], 200);
    }

    public function get($ct_code)
    {
        // User DB
        $data = Country::select(
            'ct_code as ct_code',
            'ct_nameENG as ct_nameENG',
            'ct_nameTHA as ct_nameTHA',
            'code as code',
        )
        ->where('ct_code', $ct_code)
        ->first();
        
        return response()->json([
            'message' => 'success',
            'data' => $data,
        ], 200);
    }
}
