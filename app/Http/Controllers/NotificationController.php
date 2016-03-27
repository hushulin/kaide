<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{

    public function __construct()
    {
        //
    }

    /**
    * @ApiDescription(section="Notification", description="停水通知-所有通知")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/notification")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    */
    public function index(Request $req)
    {
        return response()->json(apiformat(Notification::all()));
    }

    /**
    * @ApiDescription(section="Notification", description="停水通知-添加一条通知")
    * @ApiMethod(type="post")
    * @ApiRoute(name="/notification/add")
    * @ApiParams(name="api_token", type="string", nullable=false, description="当前登录者的token")
    * @ApiParams(name="title", type="string", nullable=false, description="通知的标题")
    * @ApiParams(name="content", type="string", nullable=false, description="通知的内容")
    */
    public function add(Request $req)
    {
        $title = $req->input('title');
        $content = $req->input('content');
        return response()->json(apiformat(Notification::create(compact('title' , 'content'))));
    }
}
