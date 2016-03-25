<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use App\Models\Meter;

class MeterController extends Controller
{
    public function __construct()
    {
        //
    }


    /**
    * @ApiDescription(section="Meter", description="水表-添加水表")
    * @ApiMethod(type="get")
    * @ApiRoute(name="/meter/add")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="meter_number", type="string", nullable=false, description="水表的ID")
    * @ApiReturn(type="object", sample="{
    *  'id':'int',
    *  'meter_number':'string',
    *  'meter_md5':'string',
    *  'user_id':'int',
    *  'status':'int',
    *  'meter_ton':'demical'
    * }")
    */
    public function add(Request $r)
    {
        $meter_number = $r->input('meter_number');

        if ( Meter::where('meter_number' , $meter_number)->where('user_id' , Auth::id())->count() ) {
            return response(['message' => '该账户下已经添加过次水表！'] , 200)->header('Content-Type' , 'json');
        }
        $meter_md5 = md5($meter_number);
        $user_id = Auth::id();
        $status = 1;
        $meter_ton = 0;
        $add_meter = Meter::create(compact('meter_number','meter_md5','user_id','status','meter_ton'));
        if ($add_meter) {
            return response(['meter' => $add_meter] , 200)->header('Content-Type' , 'json');
        }else {
            return response(['message' => '添加水表出错！'] , 200)->header('Content-Type' , 'json');
        }
    }
}
