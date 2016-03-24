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

    //
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
