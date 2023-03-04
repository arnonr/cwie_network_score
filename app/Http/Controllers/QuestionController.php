<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuestionController extends Controller
{
    public function getAll(Request $request)
    {
        $data = Question::select(
            'question.id as id',
            'question.name as name',
            'level as level',
            'question.project_type_id as project_type_id',
            'question.detail as detail',
            'question.total_score as total_score',
            'project_type.name as project_type_name',
            'question.is_check as is_check',
            'question.is_publish as is_publish',
            'question.deleted_at as delete_at',
            'question.created_at as created_at',
            'question.created_by as created_by',
            'question.updated_at as updated_at',
            'question.updated_by as updated_by',
        )
        ->where('question.deleted_at', null)
        ->join('project_type','question.project_type_id','=','project_type.id');
        

        if ($request->id) {
            $data->where('question.id',$request->id);
        }

        if ($request->name) {
            $data->where('question.name','LIKE',"%".$request->name."%");
        }

        if ($request->level) {
            $data->where('question.level',$request->level);
        }

        if ($request->project_type_id) {
            $data->where('question.project_type_id',$request->project_type_id);
        }

        if ($request->is_publish) {
            $data->where('question.is_publish',$request->is_publish);
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
        $data = Question::select(
            'question.id as id',
            'question.name as name',
            'level as level',
            'question.project_type_id as project_type_id',
            'question.detail as detail',
            'question.total_score as total_score',
            'project_type.name as project_type_name',
            'question.is_check as is_check',
            'question.is_publish as is_publish',
            'question.deleted_at as delete_at',
            'question.created_at as created_at',
            'question.created_by as created_by',
            'question.updated_at as updated_at',
            'question.updated_by as updated_by',
        )
        ->where('question.id', $id)
        ->join('project_type','question.project_type_id','=','project_type.id')
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
            'project_type_id as required'
        ]);

        $data = new Question;
        $data->name = $request->name;
        $data->level = $request->level;
        $data->total_score = $request->total_score;
        $data->project_type_id = $request->project_type_id;
        $data->detail = $request->detail;
        $data->is_check = $request->is_check;
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
            'project_type_id as required'
        ]);

        $id = $request->id;
        // $name = $request->name;

        $data = Question::where('id', $id)->first();

        $data->name = $request->name;
        $data->level = $request->level;
        $data->project_type_id = $request->project_type_id;
        $data->total_score = $request->total_score;
        $data->detail = $request->detail;
        $data->is_check = $request->is_check;
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
        $data = Question::where('id', $id)->first();

        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }
}
