<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Fix;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
const whitelist = ['127.0.0.1', "::1","localhost:8113"];

class ActivityController extends Controller
{
    protected $uploadUrl = "http://143.198.208.110:8113/storage/";
    public function getAll(Request $request)
    {
        if(in_array($_SERVER['HTTP_HOST'], whitelist)){
            $this->uploadUrl = 'http://localhost:8113/storage/';
        }
        // User DB
        $items = Activity::select(
                'activity.id as id',
                'activity.name as name',
                'activity.remark as remark',
                'activity.activity_date as activity_date',
                'activity.status as status',
                'activity.fix_id as fix_id',
                'fix_status.name as status_name',
                'fix_status.color as status_color',
            )
            ->join('fix_status','activity.status','=','fix_status.id')
            ->where('activity.deleted_at', null);

        if ($request->id) {
            $items->where('activity.id', $request->id);
        }

        if ($request->name) {
            $items->where('activity.name','LIKE',"%".$request->name."%");
        }

        if ($request->fix_id) {
            $items->where('activity.fix_id',$request->fix_id);
        }

        if ($request->status) {
            $items->where('activity.status',$request->status);
        }

        if ($request->is_publish != null) {
            $items->where('mou.is_publish', $request->is_publish);
        }

        if($request->orderBy){
            $items = $items->orderBy($request->orderBy, $request->order);
            
        }else{
            $items = $items->orderBy('id', 'desc');
        }

        $count = $items->count();
        $perPage = $request->perPage ? $request->perPage : 100;
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

    public function get($id)
    {
        if(in_array($_SERVER['HTTP_HOST'], whitelist)){
            $this->uploadUrl = 'http://localhost:8113/storage/';
        }
        // User DB
        $items = Activity::select(
            'activity.id as id',
            'activity.name as name',
            'activity.remark as remark',
            'activity.activity_date as activity_date',
            'activity.status as status',
            'activity.fix_id as fix_id',
            'fix_status.name as status_name',
            'fix_status.color as status_color',
        )->where('activity.deleted_at', null)
        ->join('fix_status','activity.status','=','fix_status.id')
        ->first();
        
        return response()->json([
            'message' => 'success',
            'data' => $item,
        ], 200);
    }

    public function add(Request $request)
    {
        $request->validate([
            'activity_date as required',
            'status as required',
            'fix_id as required',
            'is_publish as required',
        ]);

        $data = new Activity;
        $data->name = $request->name;
        $data->remark = $request->remark;
        $data->activity_date = $request->activity_date;
        $data->status = $request->status;
        $data->fix_id = $request->fix_id;
        $data->is_publish = 1;
        $data->created_by = 'arnonr';
        $data->save();

        $activity_lastest = Activity::where('fix_id',$request->fix_id)->orderBy('activity_date', 'desc')->orderBy('id', 'desc')->first();

        $fix = Fix::where('id',$request->fix_id)->first();
        $fix->status = $activity_lastest->status;
        if($activity_lastest->status == 5){
            $fix->success_date = $activity_lastest->activity_date;
        }else{
            $fix->success_date = null;
        }
        $fix->save();


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
            'activity_date as required',
            'status as required',
            'fix_id as required',
            'is_publish as required',
        ]);
        
        $data = Activity::where('id',$id)->first();

        $data->name = $request->name;
        $data->remark = $request->remark;
        $data->activity_date = $request->activity_date;
        $data->status = $request->status;
        $data->fix_id = $request->fix_id;
        $data->is_publish = 1;
        $data->updated_by = 'arnonr';
        $data->save();

        $activity_lastest = Activity::where('fix_id',$request->fix_id)->orderBy('activity_date', 'desc')->orderBy('id', 'desc')->first();

        $fix = Fix::where('id',$request->fix_id)->first();
        $fix->status = $activity_lastest->status;
        if($activity_lastest->status == 5){
            $fix->success_date = $activity_lastest->activity_date;
        }else{
            $fix->success_date = null;
        }
        $fix->save();

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];
        
        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $data = Activity::where('id', $id)->first();
        
        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }
}
