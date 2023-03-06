<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Score;
use Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScoreController extends Controller
{
    public function getAll(Request $request)
    {
        $data = Score::select(
            'score.id as id',
            'score.project_id as project_id',
            'score.user_id as user_id',
            'score.question_id as question_id',
            'score.answer as answer',
            'score.status as status',
            'score.is_publish as is_publish',
            'score.deleted_at as delete_at',
            'score.created_at as created_at',
            'score.created_by as created_by',
            'score.updated_at as updated_at',
            'score.updated_by as updated_by',
        )
        ->where('deleted_at', null);
        // ->join('user','score.user_id','=','user.id');
        

        if ($request->id) {
            $data->where('id',$request->id);
        }

        if ($request->project_id) {
            $data->where('project_id',$request->project_id);
        }
        if ($request->user_id) {
            $data->where('user_id',$request->user_id);
        }

        if ($request->question_id) {
            $data->where('question_id',$request->question_id);
        }

        if ($request->status) {
            $data->where('status',$request->status);
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
        $data = Score::select(
            'id as id',
            'project_id as project_id',
            'user_id as user_id',
            'question_id as question_id',
            'answer as answer',
            'status as status',
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
            'project_id as required',
            'user_id as required',
            'question_id as required',
        ]);

        $data = Score::where('project_id', $request->project_id)
            ->where('question_id', $request->question_id)
            ->where('user_id', $request->user_id)
            ->delete();

        // if($data){
        //     $data->deleted_at = Carbon::now();
        //     $data->save();
        // }

        $data = new Score;
        $data->project_id = $request->project_id;
        $data->user_id = $request->user_id;
        $data->question_id = $request->question_id;
        $data->answer = $request->answer;
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
            'project_id as required',
            'user_id as required',
            'question_id as required',
        ]);

        $id = $request->id;
        // $name = $request->name;

        $data = Score::where('id', $id)->first();

        $data->project_id = $request->project_id;
        $data->user_id = $request->user_id;
        $data->question_id = $request->question_id;
        $data->answer = $request->answer;
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
        $data = Score::where('id', $id)->first();

        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }
}
