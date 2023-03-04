<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function getAll(Request $request)
    {
        $data = Project::select(
            'project.id as id',
            'project.code as code',
            'project.university_id as university_id',
            'project.project_file as project_file',
            'project.project_type_id as project_type_id',
            'project.status as status',
            'project.is_publish as is_publish',
            'university.name as university_name',
            'project_type.name as project_type_name',
            'project.deleted_at as delete_at',
            'project.created_at as created_at',
            'project.created_by as created_by',
            'project.updated_at as updated_at',
            'project.updated_by as updated_by',
        )
        ->where('project.deleted_at', null)
        ->join('university','project.university_id','=','university.id')
        ->join('project_type','project.project_type_id','=','project_type.id');
        

        if ($request->id) {
            $data->where('project.id',$request->id);
        }

        if ($request->code) {
            $data->where('project.code','LIKE',"%".$request->code."%");
        }

        if ($request->university_id) {
            $data->where('project.university_id',$request->university_id);
        }

        if ($request->project_type_id) {
            $data->where('project.project_type_id',$request->project_type_id);
        }

        if ($request->status) {
            $data->where('project.status',$request->status);
        }

        if ($request->is_publish) {
            $data->where('project.is_publish',$request->is_publish);
        }

        $order_by = $request->order_by ? $request->order_by : 'project.id';
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
        $data = Project::select(
            'project.id as id',
            'project.code as code',
            'project.university_id as university_id',
            'project.project_file as project_file',
            'project.project_type_id as project_type_id',
            'project.status as status',
            'project.is_publish as is_publish',
            'university.name as university_name',
            'project_type.name as project_type_name',
            'project.deleted_at as delete_at',
            'project.created_at as created_at',
            'project.created_by as created_by',
            'project.updated_at as updated_at',
            'project.updated_by as updated_by',
        )
        ->join('university','project.university_id','=','university.id')
        ->join('project_type','project.project_type_id','=','project_type.id')
        ->where('project.id', $id)
        ->first();
        
        return response()->json([
            'message' => 'success',
            'data' => $data,
        ], 200);
    }

    public function add(Request $request)
    {
        $request->validate([
            'project_type_id as required',
        ]);

        $data = new Project;
        $data->code = $request->code;
        $data->university_id = $request->university_id;
        // $data->project_file = $request->project_file;
        $data->project_type_id = $request->project_type_id;
        $data->status = $request->status;
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
        ]);

        $id = $request->id;
        // $name = $request->name;

        $data = Project::where('id', $id)->first();

        $data->code = $request->code;
        $data->university_id = $request->university_id;
        // $data->project_file = $request->project_file;
        $data->project_type_id = $request->project_type_id;
        $data->status = $request->status;
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
        $data = Project::where('id', $id)->first();

        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }
}
