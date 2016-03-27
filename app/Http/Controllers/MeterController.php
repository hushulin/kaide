<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use App\Models\Meter;
use App\Models\Order;

class MeterController extends Controller
{
    public function __construct()
    {
        //
    }


    /**
    * @ApiDescription(section="Meter", description="水表-添加水表")
    * @ApiMethod(type="post")
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
            return response(apiformat('该账户下已经添加过次水表！' , -1 , []) , 200)->header('Content-Type' , 'json');
        }
        $meter_md5 = md5($meter_number);
        $user_id = Auth::id();
        $status = 1;
        $meter_ton = 0;
        $add_meter = Meter::create(compact('meter_number','meter_md5','user_id','status','meter_ton'));
        if ($add_meter) {
            return response(apiformat($add_meter) , 200)->header('Content-Type' , 'json');
        }else {
            return response(apiformat('添加水表出错！' , -2) , 200)->header('Content-Type' , 'json');
        }
    }

    /**
    * @ApiDescription(section="Meter", description="水表-该账户下的所有水表")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/meter")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiReturn(type="object", sample="[{
    *  'id':'int',
    *  'meter_number':'string',
    *  'meter_md5':'string',
    *  'user_id':'int',
    *  'status':'int',
    *  'meter_ton':'demical'
    * }]")
    */
    public function index(Request $r)
    {
        return response()->json(apiformat(Meter::where('user_id' , Auth::id())->get()));
    }


    /**
    * @ApiDescription(section="Meter", description="水表-设置默认水表")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/meter/set-default")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="default_meter", type="int", nullable=false, description="水表ID")
    * @ApiReturn(type="object", sample="[{
    *  'message':'string'
    * }]")
    */
    public function setDefault(Request $r)
    {
        $user = Auth::user();

        $user->default_meter = $r->input('default_meter');

        if ( ! Meter::find($r->input('default_meter')) ) {
            return response(apiformat('设置失败！没有水表:' . $r->input('default_meter') , -1) , 200)->header('Content-Type' , 'json');
        }

        $user->save();

        return response(apiformat('设置成功！ID:' . $r->input('default_meter')) , 200)->header('Content-Type' , 'json');
    }

    /**
    * @ApiDescription(section="Meter", description="水表-充值水费 ， 充值的吨数与消费的金额都传过来，如果不传，则告诉我换算关系")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/meter/add-ton")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="meter_id", type="int", nullable=false, description="水表ID")
    * @ApiParams(name="pay_ton", type="string", nullable=true, description="充值多少吨")
    * @ApiParams(name="pay_money", type="string", nullable=true, description="充值多少钱")
    * @ApiReturn(type="object", sample="[{
    *  'message':'string'
    * }]")
    */
    public function addTon(Request $req)
    {
        $meter_id = $req->input('meter_id');
        $pay_ton = $req->input('meter_ton');
        $pay_money = $req->input('pay_money');

        $user = Auth::user();

        if ( $pay_money <= 0 || $pay_ton <= 0 ) {
            return response()->json(apiformat(-1 , '参数无效！'));
        }

        if ($user->money < $pay_money) {
            return response()->json(apiformat(-2 , '余额不足！'));
        }

        Order::create([
            'meter_id' => $meter_id ,
            'pay_ton' => $pay_ton ,
            'pay_money' => $pay_money ,
            'user_id' => $user->id ,
        ]);

        Meter::where('id' , $meter_id)->increment('meter_ton' , $pay_ton);

        $user->decrement('money' , $pay_money);

        return response()->json(apiformat());
    }

    /**
    * @ApiDescription(section="Meter", description="测试接口")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/api/format")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiReturn(type="object", sample="{
    *  'code':'int',
    *  'msg':'string',
    *  'data':{
    *      'id':'int',
    *      'name':'string'
    *  }
    * }")
    */
    public function format(Request $r)
    {
        return response()->json(apiformat());
    }
}
