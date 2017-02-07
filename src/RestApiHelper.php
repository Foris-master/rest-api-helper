<?php
/**
 * Created by PhpStorm.
 * User: evari
 * Date: 2/7/2017
 * Time: 10:13 PM
 */

namespace Foris\RestApiHelper;


use BadMethodCallException;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class RestApiHelper
{

    private function get_operator($op,$value){
        switch ($op) {
            // greater than
            case 'gt': {
                return [">",$value];
            }
            // greater or equal to
            case 'get': {
                return [">=",$value];
            }
            // less than
            case 'lt': {
                return ["<",$value];
            }
            // less or equal to
            case 'let': {
                return ["<=",$value];
            }
            // like
            case 'lk': {
                return ["like",'%'.$value.'%'];
            }
            // not like
            case 'not_lk': {
                return ["not like",'%'.$value.'%'];
            }
            // not equal
            case 'ne': {
                return ["!=",$value];
            }
            // in
            case 'in': {
                return ["in",explode(',',$value)];
            }
            // not in
            case 'not_in': {
                return ["not in",explode(',',$value)];
            }
            // between
            case 'bt': {
                return ["bt",explode(',',$value)];
            }
            // not between
            case 'not_bt': {
                return ["not_bt",explode(',',$value)];
            }
            default:
                return ["=",$value];
        }
    }
    public function get($Model)
    {
        $ms = new  $Model;
        $ord = $ms->timestamps;
        $data = Input::get();
        $field = $ms->getFillable();
        $dates = $ms->getDates();
        $per_page = Input::has('per_page') ? Input::get('per_page') : 10;
        $order_d = Input::get('_sortDir') == 'desc' ? Input::get('_sortDir') : 'asc';
        $sort = Input::has('_sort') && in_array(Input::get('_sort'), $field) ? Input::get('_sortd') : 'updated_at';
        $fo = Input::has('_includes') ? explode(',', Input::get('_includes')) : $ms->getForeign();
        foreach ($data as $key => $value) {
            $tkey = explode('-',$key);
            $op="=";
            if(count($tkey)==2&&in_array($tkey[0], $field)){
                $opres = self::get_operator($tkey[1],$value);
                $op =$opres[0];
                $key= $tkey[0];
                $value=$opres[1];
                if ($tkey[1]='in') {
                    $ms = $ms->whereIn($key,$value);
                }elseif ($tkey[1]='not_in') {
                    $ms = $ms->whereNotIn($key,$value);
                }elseif ($tkey[1]='bt') {
                    $ms = $ms->whereBetween($key,$value);
                }elseif ($tkey[1]='not_bt') {
                    $ms = $ms->whereNotBetween($key,$value);
                }
            }
            if (in_array($key, $field)) {
                if(in_array($key, $dates)){
                    $ms = $ms->whereDate($key, $op, $value);
                } else {
                    $ms = $ms->where($key, $op, $value);
                }
            }
        }
        try {
            if ($ord) {
                $ms = $ms->with($fo)->orderBy($sort, $order_d)->paginate($per_page);
            } else {
                $ms = $ms->with($fo)->paginate($per_page);
            }
            return Response::json($ms, 200, [], JSON_NUMERIC_CHECK);
        } catch (BadMethodCallException $e) {
            return Response::json(['error' => 'includes methods does not exists'], 400, [], JSON_NUMERIC_CHECK);
        }
    }
    public function store($Model, $data)
    {
        $m = self::pre_store($Model, $data);
        $m->save();
        return self::post_store($Model, $m);
    }
    public function pre_store($Model, $data)
    {
        $m = new $Model;
        $name = explode("App\\", $Model)[1];
        $field = $m->getFillable();
        foreach ($data as $key => $value) {
            if (in_array($key, $field)) {
                if (in_array($key, $m->getFiles())) {
                    $image = $value;
                    $fpath = "img/" . strtolower($name) . "/" . uniqid() . '_' . $image->getClientOriginalName();
                    $m->$key = $fpath;
                    Storage::disk('local')->put($fpath, File::get($image));
                } else {
                    $m->$key = $value;
                }
            }
        }
        return $m;
    }
    public function post_store($Model, $m)
    {
        $name = explode("App\\", $Model)[1];
        $m = $Model::with($m->getForeign())->find($m->id);
        Log::info(Carbon::now() . 'the ' . $name . '  ' . $m->getLabel() . ' has been created ');
        return Response::json($m, 200, [], JSON_NUMERIC_CHECK);
    }
    public function show($Model, $id)
    {
        $m = new $Model;
        $name = explode("App\\", $Model)[1];
        $m = $Model::with($m->getForeign())->find($id);
        if ($m) {
            return Response::json($m, 200, [], JSON_NUMERIC_CHECK);
        } else {
            return Response::json(array("erreur" => "the " . $name . " you are looking for does not exist"), 422);
        }
    }
    public function pre_update($Model, $data, $id)
    {
        $m = $Model::find($id);
        $name = explode("App\\", $Model)[1];
        if ($m) {
            $field = $m->getFillable();
            $name = explode("App\\", $Model)[1];
            foreach ($data as $key => $value) {
                if (in_array($key, $field)) {
                    if (in_array($key, $m->getFiles())) {
                        if (Storage::has($m->$key))
                            Storage::delete($m->$key);
                        $image = $value;
                        $fpath = "img/" . strtolower($name) . "/" . uniqid() . '_' . $image->getClientOriginalName();
                        $m->$key = $fpath;
                        Storage::disk('local')->put($fpath, File::get($image));
                    } else {
                        $m->$key = $value;
                    }
                }
            }
            return $m;
        } else {
            return Response::json(array("erreur" => "the " . $name . " you are looking for does not exist"), 422);
        }
    }
    public function post_update($Model, $m)
    {
        $name = explode("App\\", $Model)[1];
        $m =$Model::with($m->getForeign())->find($m->id);
        Log::info(Carbon::now() . 'the ' . $name . ' ' . $m->getLabel() . ' has been updated ');
        return Response::json($m, 200, [], JSON_NUMERIC_CHECK);
    }
    public function update($Model, $data, $id)
    {
        $m = self::pre_update($Model, $data, $id);
        $m->save();
        return self::post_update($Model, $m);
    }
    public function destroy($Model, $id)
    {
        $m = $Model::find($id);
        $name = explode("App\\", $Model)[1];
        if ($m) {
            foreach ($m->getFiles() as $img) {
                if (Storage::has($img))
                    Storage::delete($img);
            }
            $m->delete();
            Log::critical(Carbon::now() . 'the ' . $name . ' ' . $m->getLabel() . ' has been deleted');
            return Response::json($m, 200, [], JSON_NUMERIC_CHECK);
        } else {
            return Response::json(array("erreur" => "the " . $name . " you are looking for does not exist"), 422);
        }
    }
    public function getImage($model, $image)
    {
        $filename = "img/" . $model . "/" . $image;
        if (!Storage::has($filename)) abort(404);
        $path = storage_path('app/') . $filename;
        $file = Storage::get($filename);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }


}