<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Xiaofei;

class XiaofeiController extends Controller
{

    public function __construct()
    {
        //
    }

    /**
    * @ApiDescription(section="Xiaofei", description="水表消费，消费列表")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/xiaofei/xflist")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="meter_id", type="int", nullable=false, description="要查询的水表ID")
    * @ApiParams(name="start", sample="2016-03-27 05:49:13 or 2016-03-27 or 2016-03" , type="datetime", nullable=true, description="要查询的起始时间，可不传，不传就没有限制")
    * @ApiParams(name="end", sample="2016-03-27 05:49:13 or 2016-03-27 or 2016-03" , type="datetime", nullable=true, description="要查询的结束时间，可不传，不传就没有限制")
    * @ApiReturn(type="object", sample="{
    *  'money':'2.00'
    * }")
    */
    public function xflist(Request $r)
    {
        $meter_id = $r->input('meter_id');
        $start = $r->input('start');
        $end = $r->input('end');

        $list = Xiaofei::where('meter_id' , $meter_id);

        if ( $start != '' ) {
            $list->where('created_at' , '>=' , $start);
        }

        if ( $end != '' ) {
            $list->where('created_at' , '<=' , $end);
        }

        $list->orderBy('id' , 'desc');

        return response()->json(apiformat($list->get()));
    }
}
