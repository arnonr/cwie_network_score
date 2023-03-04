<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\University;
use Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UniversityController extends Controller
{
    public function getAll(Request $request)
    {
        $data = University::select(
            'id as id',
            'name as name',
            'is_publish as is_publish',
            'deleted_at as delete_at',
            'created_at as created_at',
            'created_by as created_by',
            'updated_at as updated_at',
            'updated_by as updated_by',
        )
        ->where('deleted_at', null);
        

        if ($request->id) {
            $data->where('id',$request->id);
        }

        if ($request->name) {
            $data->where('name','LIKE',"%".$request->name."%");
        }

        if ($request->is_publish) {
            $data->where('is_publish',$request->is_publish);
        }

        $order_by = $request->order_by ? $request->order_by : 'id';
        $order = $request->order ? $request->order : 'asc';

        $data = $data->orderBy($order_by, $order);
            
        $data = $data->get();

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ], 200);
    }

    public function get($id)
    {
        // User DB
        $data = University::select(
            'id as id',
            'name as name',
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

    public function add(Request $request)
    {
        $request->validate([
            'name as required',
        ]);

        $data = new University;
        $data->name = $request->name;
        $data->is_publish = $request->is_publish;
        $data->created_by = 'arnonr';
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
            'name as required',
        ]);

        $id = $request->id;
        // $name = $request->name;

        $data = University::where('id', $id)->first();

        $data->name = $request->name;
        $data->is_publish = $request->is_publish;
        $data->updated_by = 'arnonr';
        $data->save();

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];
        
        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $data = University::where('id', $id)->first();

        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }
}
