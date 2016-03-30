<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use App\Models\Meter;
use App\Models\Order;
use App\Models\Xiaofei;

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
    * @ApiParams(name="meter_number", type="string", nullable=false, description="水表的编号")
    * @ApiParams(name="meter_md5", type="string", nullable=false, description="水表的编号的md5")
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

        $meter_md5 = $r->input('meter_md5');

        if ($meter_number == '' && $meter_md5 == '') {
            return response()->json(apiformat('参数无效！' , -1));
        }

        $meter = Meter::where('meter_number' , $meter_number)->orWhere('meter_md5' , $meter_md5)->first();

        if (!$meter) {
            return response()->json(apiformat('此水表不存在！' , -3));
        }

        if ($meter->user_id != '') {
            return response()->json(apiformat('此水表已经被绑定！' , -2));
        }

        $meter->user_id = Auth::id();

        $meter->save();

        return response()->json(apiformat($meter));
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
            return response()->json(apiformat('设置失败！没有水表:' . $r->input('default_meter') , -1));
        }

        $user->save();

        return response()->json(apiformat('设置成功！ID:' . $r->input('default_meter')));
    }

    /**
    * @ApiDescription(section="Meter", description="水表-充值水费 ， 充值的吨数与消费的金额都传过来，如果不传，则告诉我换算关系")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/meter/add-ton")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="meter_id", type="int", nullable=false, description="水表ID")
    * @ApiParams(name="pay_ton", type="float", nullable=false, description="充值多少吨")
    * @ApiParams(name="pay_money", type="float", nullable=false, description="充值多少钱")
    * @ApiReturn(type="object", sample="[{
    *  'message':'string'
    * }]")
    */
    public function addTon(Request $req)
    {
        $meter_id = $req->input('meter_id');
        $pay_ton = $req->input('pay_ton');
        $pay_money = $req->input('pay_money');

        $user = Auth::user();

        if ( $pay_money <= 0 || $pay_ton <= 0 ) {
            return response()->json(apiformat(-1 , '参数无效！'));
        }

        // if ($user->money < $pay_money) {
        //     return response()->json(apiformat(-2 , '余额不足！'));
        // }

        Order::create([
            'meter_id' => $meter_id ,
            'pay_ton' => $pay_ton ,
            'pay_money' => $pay_money ,
            'user_id' => $user->id ,
        ]);

        Meter::where('id' , $meter_id)->increment('meter_ton' , $pay_ton);

        // $user->decrement('money' , $pay_money);

        return response()->json(apiformat());
    }

    /**
    * @ApiDescription(section="Meter", description="设置水表开关")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/meter/set-status")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="id", type="int", nullable=false, description="要设置的水表ID")
    * @ApiParams(name="status", type="int", nullable=false, description="要设置的状态，1为开启，0为关闭")
    * @ApiReturn(type="object", sample="{
    *  'code':'int',
    *  'msg':'string',
    *  'data':{
    *      'id':'int',
    *      'name':'string'
    *  }
    * }")
    */
    public function setStatus(Request $r)
    {
        $status = $r->input('status');
        $id = $r->input('id');
        Meter::where('id' , $id)->update(['status' => $status]);
        return response()->json(apiformat());
    }

    /**
    * @ApiDescription(section="Meter", description="根据MD5获取水表信息")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/meter/get-by-md5")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="meter_md5", type="string", nullable=false, description="水表的MD5值")
    * @ApiReturn(type="object", sample="{
    *  'code':'int',
    *  'msg':'string',
    *  'data':{
    *      'id':'int',
    *      'name':'string'
    *  }
    * }")
    */
    public function getByMd5(Request $r)
    {
        $meter_md5 = $r->input('meter_md5');
        $meter = Meter::where('meter_md5' , $meter_md5)->first();

        return response()->json(apiformat($meter));
    }

    /**
    * @ApiDescription(section="Meter", description="当月实时水费查询")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/meter/act-meter-fee")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="id", type="int", nullable=false, description="实时水费查询-水表ID")
    * @ApiReturn(type="object", sample="{
    *  'code':'int',
    *  'msg':'string',
    *  'data':{
    *      'id':'int',
    *      'name':'string'
    *  }
    * }")
    */
    public function actMeterFee(Request $r)
    {
        $id = $r->input('id');
        $tons = Xiaofei::where('meter_id' , $id)->whereRaw("date_format(`created_at` , '%Y-%m') = date_format(now() , '%Y-%m')")->sum('xiaofei_ton');
        return response()->json(apiformat(['xiaofei_ton' => $tons] , '当月实时水费读取成功！'));
    }


    /**
    * @ApiDescription(section="Meter", description="根据年份水费查询列表")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/meter/fee-list-year")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="meter_id", type="int", nullable=false, description="水表ID")
    * @ApiParams(name="year", type="string", nullable=false, description="年份")
    * @ApiReturn(type="object", sample="{
    *  'code':'int',
    *  'msg':'string',
    *  'data':{
    *      'id':'int',
    *      'name':'string'
    *  }
    * }")
    */
    public function getFeeListByYear(Request $r)
    {
        $year = $r->input('year');
        $meter_id = $r->input('meter_id');

        if ( empty($year) || empty($meter_id) ) {
            return response()->json(apiformat(-1 , '参数无效！'));
        }

        $fees = Xiaofei::where('meter_id' , $meter_id)->whereRaw("date_format(`created_at` , '%Y') = {$year}")->selectRaw("date_format(`created_at` , '%Y-%m') as month , SUM(`xiaofei_ton`) as total_ton")->groupBy('month')->orderBy('id' , 'desc')->get();

        return response()->json(apiformat($fees));
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
