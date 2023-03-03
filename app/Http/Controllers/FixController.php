<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fix;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
const whitelist = ['127.0.0.1', "::1","localhost:8113"];

class FixController extends Controller
{
    protected $uploadUrl = 'http://143.198.208.110:8113/storage/';
    public function getAll(Request $request)
    {
        if(in_array($_SERVER['HTTP_HOST'], whitelist)){
            $this->uploadUrl = 'http://localhost:8113/storage/';
        }

        // User DB
        $items = Fix::select(
            'fix.id as id',
            'fix.title as title',
            'fix.detail as detail',
            'fix.place as place',
            DB::raw("(CASE WHEN fix_img_file = NULL THEN NULL
                ELSE CONCAT('".$this->uploadUrl."',fix_img_file) END) AS fix_img_file"),
            DB::raw("(CASE WHEN fix_img2_file = NULL THEN NULL
                ELSE CONCAT('".$this->uploadUrl."',fix_img2_file) END) AS fix_img2_file"),
            DB::raw("(CASE WHEN fix_img3_file = NULL THEN NULL
                ELSE CONCAT('".$this->uploadUrl."',fix_img3_file) END) AS fix_img3_file"),
            'fix.name as name',
            'fix.email as email',
            'fix.phone as phone',
            'fix.user_id as user_id',
            'fix.fix_date as fix_date',
            'fix.success_date as success_date',
            'fix.status as status',
            'fix.building_id as building_id',
            'building.name as building_name',
            'fix.status as status_id',
            'fix_status.name as status_name',
            'fix_status.color as status_color',
            'fix.is_publish as is_publish'
        )
        ->join('building','fix.building_id','=','building.id')
        ->join('fix_status','fix.status','=','fix_status.id')
        ->where('fix.deleted_at', null);

        if ($request->id) {
            $items->where('fix.id', $request->id);
        }

        if ($request->title) {
            $items->where('fix.title','LIKE',"%".$request->title."%");
        }

        if ($request->name) {
            $items->where('fix.name','LIKE',"%".$request->name."%");
        }

        if ($request->email) {
            $items->where('fix.email','LIKE',"%".$request->email."%");
        }

        if ($request->phone) {
            $items->where('fix.phone','LIKE',"%".$request->phone."%");
        }

        if ($request->building_id) {
            $items->where('fix.building_id',$request->building_id);
        }

        if (($request->user_id) && ($request->user_id != 'undefined')){
            $items->where('fix.user_id',$request->user_id);
        }

        if ($request->status) {
            $items->where('fix.status',$request->status);
        }

        if ($request->is_publish != null) {
            $items->where('mou.is_publish', $request->is_publish);
        }

        if ($request->fix_date) {
            $fixDate = Carbon::createFromFormat('Y-m-d', $request->fix_date);
            $items->whereDate('fix_date', $fixDate);
        }

        if ($request->fix_start_date) {
            $fixStartDate = Carbon::createFromFormat('Y-m-d', $request->fix_start_date);
            $items->whereDate('fix_date','>=', $fixStartDate);
        }

        if ($request->fix_end_date) {
            $fixEndDate = Carbon::createFromFormat('Y-m-d', $request->fix_end_date);
            $items->whereDate('fix_date','<=', $fixEndDate);
        }

        if ($request->success_date) {
            $successDate = Carbon::createFromFormat('Y-m-d', $request->success_date);
            $items->whereDate('success_date', $successDate);
        }

        if($request->orderBy){
            $items = $items->orderBy($request->orderBy, $request->order);
            $items = $items->orderBy('id', 'desc');
            
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

    public function get($id)
    {
        if(in_array($_SERVER['HTTP_HOST'], whitelist)){
            $this->uploadUrl = 'http://localhost:8113/storage/';
        }
        
        // User DB
        $item = Fix::select(
            'fix.id as id',
            'fix.title as title',
            'fix.detail as detail',
            'fix.place as place',
            DB::raw("(CASE WHEN fix_img_file = NULL THEN NULL
                ELSE CONCAT('".$this->uploadUrl."',fix_img_file) END) AS fix_img_file"),
            DB::raw("(CASE WHEN fix_img2_file = NULL THEN NULL
                ELSE CONCAT('".$this->uploadUrl."',fix_img2_file) END) AS fix_img2_file"),
            DB::raw("(CASE WHEN fix_img3_file = NULL THEN NULL
                ELSE CONCAT('".$this->uploadUrl."',fix_img3_file) END) AS fix_img3_file"),
            'fix.name as name',
            'fix.email as email',
            'fix.phone as phone',
            'fix.user_id as user_id',
            'fix.fix_date as fix_date',
            'fix.success_date as success_date',
            'fix.status as status',
            'fix.building_id as building_id',
            'building.name as building_name',
            'fix.status as status_id',
            'fix_status.name as status_name',
            'fix_status.color as status_color',
            'fix.is_publish as is_publish'
        )
        ->where('fix.id', $id)
        ->join('building','fix.building_id','=','building.id')
        ->join('fix_status','fix.status','=','fix_status.id')
        ->where('fix.deleted_at', null)->first();

        return response()->json([
            'message' => 'success',
            'data' => $item,
        ], 200);
    }

    public function add(Request $request)
    {
        $request->validate([
            'title as required',
            'place as required',
            'name as required',
            'user_id as required',
            'fix_date as required',
            'is_publish as required',
        ]);
        
        // Save File Success
        $pathFixImg = null;
        if(($request->fix_img_file != "") && ($request->fix_img_file != 'null')){
            $fileNameFixImg = 'logo-'.rand(10,100).'-'.$request->fix_img_file->getClientOriginalName();
            $pathFixImg = '/fix/'.$fileNameFixImg;
            Storage::disk('public')->put($pathFixImg, file_get_contents($request->fix_img_file));
        }

        $pathFixImg2 = null;
        if(($request->fix_img2_file != "") && ($request->fix_img2_file != 'null')){
            $fileNameFixImg2 = 'logo-'.rand(10,100).'-'.$request->fix_img2_file->getClientOriginalName();
            $pathFixImg2 = '/fix/'.$fileNameFixImg2;
            Storage::disk('public')->put($pathFixImg2, file_get_contents($request->fix_img2_file));
        }

        $pathFixImg3 = null;
        if(($request->fix_img3_file != "") && ($request->fix_img3_file != 'null')){
            $fileNameFixImg3 = 'logo-'.rand(10,100).'-'.$request->fix_img3_file->getClientOriginalName();
            $pathFixImg3 = '/fix/'.$fileNameFixImg3;
            Storage::disk('public')->put($pathFixImg3, file_get_contents($request->fix_img3_file));
        }

        $data = new Fix;
        $data->title = $request->title;
        $data->detail = $request->detail;
        $data->building_id = $request->building_id;
        $data->place = $request->place;
        $data->fix_img_file = $pathFixImg;
        $data->fix_img2_file = $pathFixImg2;
        $data->fix_img3_file = $pathFixImg3;
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->user_id = $request->user_id;
        $data->fix_date = $request->fix_date;
        $data->success_date = $request->success_date;
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
            'title as required',
            'place as required',
            'name as required',
            'fix_date as required',
            'is_publish as required',
        ]);
        
        $data = Fix::where('id',$id)->first();

        // Save File Success
        $pathFixImg = null;
        if(($request->fix_img_file != "") && ($request->fix_img_file != 'null')){
            $fileNameFixImg = 'logo-'.rand(10,100).'-'.$request->fix_img_file->getClientOriginalName();
            $pathFixImg = '/fix/'.$fileNameFixImg;
            Storage::disk('public')->put($pathFixImg, file_get_contents($request->fix_img_file));
        }else{
            $pathFixImg =  $data->fix_img_file;
        }

        // Save File Success
        $pathFixImg2 = null;
        if(($request->fix_img2_file != "") && ($request->fix_img2_file != 'null')){
            $fileNameFixImg2 = 'logo-'.rand(10,100).'-'.$request->fix_img2_file->getClientOriginalName();
            $pathFixImg2 = '/fix/'.$fileNameFixImg2;
            Storage::disk('public')->put($pathFixImg2, file_get_contents($request->fix_img2_file));
        }else{
            $pathFixImg2 =  $data->fix_img2_file;
        }

        // Save File Success
        $pathFixImg3 = null;
        if(($request->fix_img3_file != "") && ($request->fix_img3_file != 'null')){
            $fileNameFixImg3 = 'logo-'.rand(10,100).'-'.$request->fix_img3_file->getClientOriginalName();
            $pathFixImg3 = '/fix/'.$fileNameFixImg3;
            Storage::disk('public')->put($pathFixImg3, file_get_contents($request->fix_img3_file));
        }else{
            $pathFixImg3 =  $data->fix_img3_file;
        }

        $data->title = $request->title;
        $data->detail = $request->detail;
        $data->building_id = $request->building_id;
        $data->place = $request->place;
        $data->fix_img_file = $pathFixImg;
        $data->fix_img2_file = $pathFixImg2;
        $data->fix_img3_file = $pathFixImg3;
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->fix_date = $request->fix_date;
        $data->success_date = $request->success_date;
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

    public function delete($id)
    {
        $data = Fix::where('id', $id)->first();
        
        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }
}
