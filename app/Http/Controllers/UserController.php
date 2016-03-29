<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use Auth;
use App\Models\Money;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
    * @ApiDescription(section="User", description="用户中心-注册")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/user/register")
    * @ApiParams(name="name", type="string", nullable=false, description="用户名")
    * @ApiParams(name="password", type="string", nullable=false, description="密码")
    * @ApiReturn(type="object", sample="{
    *  'api_token':'string'
    * }")
    */
    public function register(Request $r)
    {
        $name = $r->input('name');

        if ( User::where('name' , $name)->count() ) {
            return response(apiformat('用户名已经被使用！' , -1) , 200);
        }

        $password = $r->input('password');
        $api_token = md5($name . $password . time());

        User::create([
            'name' => $name,
            'password' => $password,
            'api_token' => $api_token,
        ]);

        return response()->json(apiformat(['api_token' => $api_token]));
    }

    /**
    * @ApiDescription(section="User", description="用户中心-登录")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/user/login")
    * @ApiParams(name="name", type="string", nullable=false, description="用户名")
    * @ApiParams(name="password", type="string", nullable=false, description="密码")
    * @ApiParams(name="openid", type="string", nullable=false, description="微信唯一码openid")
    * @ApiReturn(type="object", sample="{
    *  'api_token':'string'
    * }")
    */
    public function login(Request $r)
    {
        $name = $r->input('name');
        $password = $r->input('password');
        $openid = $r->input('openid');

        if ($openid) {

            $user = User::where('wechat_number' , $openid)->first();

            if ($user) {
                $api_token = md5($openid . time());
                $user->api_token = $api_token;
                $user->save();
                $content = $user->where('api_token' , $api_token)->with('meters')->first();
                $code = 1;
                $msg = '登录成功！';
            }else {

                $api_token = md5($openid . time());

                $user = User::create([
                    'wechat_number' => $openid,
                    'api_token' => $api_token,
                ]);

                $content = $user->where('api_token' , $api_token)->with('meters')->first();
                $code = 1;
                $msg = '登录成功！';
            }

            return response()->json(apiformat($content , $code , $msg));
        }

        $user = User::where('name' , $name)->where('password' , $password)->first();
        if ($user) {
            $api_token = md5($name . $password . time());
            $user->api_token = $api_token;
            $user->save();
            $content = $user->where('api_token' , $api_token)->with('meters')->first();
            $code = 1;
            $msg = '登录成功！';
        }else {
            $content = (object) array();
            $code = -1;
            $msg = '账号密码错误！';
        }

        return response()->json(apiformat($content , $code , $msg));
    }

    /**
    * @ApiDescription(section="User", description="用户中心-退出")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/user/logout")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiReturn(type="object", sample="{
    *  'messge':'success!'
    * }")
    */
    public function logout(Request $r)
    {
        $api_token = $r->input('api_token');
        if ($api_token) {
            User::where('api_token' , $api_token)->update([
                'api_token' => null,
            ]);
        }

        return response()->json(apiformat());
    }


    /**
    * @ApiDescription(section="User", description="用户中心-修改用户的密码，默认水表 ，绑定微信号")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/user/update")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="password", type="string", nullable=true, description="修改密码")
    * @ApiParams(name="default_meter", type="int", nullable=true, description="修改默认水表")
    * @ApiParams(name="wechat_number", type="string", nullable=true, description="绑定微信号")
    * @ApiParams(name="xiaoqu", type="string", nullable=true, description="修改小区名")
    * @ApiReturn(type="object", sample="{
    *  'messge':'password update success!'
    * }")
    */
    public function update(Request $r)
    {

        // money later update ...

        $user = Auth::user();
        $password = $r->input('password');
        $default_meter = $r->input('default_meter');
        $wechat_number = $r->input('wechat_number');
        $xiaoqu = $r->input('xiaoqu');

        $msg = ' ';
        if ($password) {
            $user->password = $password;
            $msg .= 'password update success!';
        }

        if ($default_meter) {
            $user->default_meter = $default_meter;
            $msg .= 'default_meter update success!';
        }

        if ($wechat_number) {
            $user->wechat_number = $wechat_number;
            $msg .= 'wechat_number update success!';
        }

        if ($xiaoqu) {
            $user->xiaoqu = $xiaoqu;
            $msg .= 'xiaoqu update success!';
        }

        $user->save();

        return response()->json(apiformat($msg));
    }


    /**
    * @ApiDescription(section="User", description="用户中心-上传头像")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/user/update-face")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="face", type="object", nullable=true, description="上传头像")
    */
    public function updateFace(Request $request)
    {
        $face = $request->file('face');
        if ( $request->hasFile('face') ) {

            $user = Auth::user();

            $filePath = 'public/face/' . date('Ymd') . '/';

            $destinationPath = base_path($filePath);

            $fileName = md5(md5($face) . microtime()) . '.' . $face->getClientOriginalExtension();

            $savePath = $filePath . $fileName;

            $request->file('face')->move($destinationPath , $fileName);

            $user->face = '/' . str_replace('public/', '', $savePath);

            $user->save();

            return response()->json(apiformat($user));
        }

        return response()->json(apiformat(-1 , '上传文件无效！'));
    }

    /**
    * @ApiDescription(section="User", description="用户中心-用户余额")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/user/money")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiReturn(type="object", sample="{
    *  'money':'2.00'
    * }")
    */
    public function money(Request $r)
    {
        $money = Auth::user()->money;
        return response()->json(apiformat([ 'money' => $money ]));
    }

    /**
    * @ApiDescription(section="User", description="用户中心-用户默认水表的余下的水吨数")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/user/default-meter-ton")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiReturn(type="object", sample="{
    *  'money':'2.00'
    * }")
    */
    public function defaultMeterTon(Request $r)
    {
        $user = Auth::user();
        $default_meter_ton = $user->default_meter ? $user->default_meter->meter_ton : 0;
        return response()->json(apiformat(['default_meter_ton' => $default_meter_ton]));
    }


    /**
    * @ApiDescription(section="User", description="用户中心-充值")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/user/add-money")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="money", type="integer", nullable=false, description="充值金额")
    * @ApiReturn(type="object", sample="{
    *  'money':'2.00'
    * }")
    */
    public function addMoney(Request $r)
    {
        $money = $r->input('money');
        if ($money <= 0) {
            return response()->json(apiformat('无效的金额！' , -1));
        }
        $user = Auth::user();
        $user->money += $money * 1;
        $user->save();

        Money::create([
            'user_id' => $user->id,
            'pay_money' => $money,
            'mark' => '接口充值',
        ]);

        return response()->json(apiformat('充值成功！'));
    }


}
