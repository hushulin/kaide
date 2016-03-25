<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;

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
    * @ApiMethod(type="get")
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
            return response(['message' => '用户名已经被使用！'] , 200);
        }

        $password = $r->input('password');
        $api_token = md5($name . $password . time());

        User::create([
            'name' => $name,
            'password' => $password,
            'api_token' => $api_token,
        ]);

        return response(['api_token' => $api_token] , 200)->header('Content-Type' , 'json');
    }

    /**
    * @ApiDescription(section="User", description="用户中心-登录")
    * @ApiMethod(type="get")
    * @ApiRoute(name="/user/login")
    * @ApiParams(name="name", type="string", nullable=false, description="用户名")
    * @ApiParams(name="password", type="string", nullable=false, description="密码")
    * @ApiReturn(type="object", sample="{
    *  'api_token':'string'
    * }" , description="如果是返回空，则说明登录失败")
    */
    public function login(Request $r)
    {
        $name = $r->input('name');
        $password = $r->input('password');

        $user = User::where('name' , $name)->where('password' , $password)->first();
        if ($user) {
            $api_token = md5($name . $password . time());
            $user->api_token = $api_token;
            $user->save();
            $content = ['api_token' => $user->api_token];
            $status = 200;
        }else {
            $content = ['api_token' => null];
            $status = 200;
        }

        return response($content , $status)->header('Content-Type' , 'json');
    }

    /**
    * @ApiDescription(section="User", description="用户中心-退出")
    * @ApiMethod(type="get")
    * @ApiRoute(name="/user/logout")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiReturn(type="object", sample="{
    *  'messge':'success!'
    * }" , description="")
    */
    public function logout(Request $r)
    {
        $api_token = $r->input('api_token');
        if ($api_token) {
            User::where('api_token' , $api_token)->update([
                'api_token' => null,
            ]);
        }

        return response(['messge' => 'success!'] , 200)->header('Content-Type' , 'json');
    }


}
