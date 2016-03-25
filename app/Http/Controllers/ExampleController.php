<?php

namespace App\Http\Controllers;

class ExampleController extends Controller
{
     /**
     * @ApiDescription(section="Example", description="Create's a new Example")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/user/create")
     * @ApiParams(name="username", type="string", nullable=false, description="Username")
     * @ApiParams(name="email", type="string", nullable=false, description="Email")
     * @ApiParams(name="password", type="string", nullable=false, description="Password")
     * @ApiParams(name="age", type="integer", nullable=true, description="Age")
     */
    public function __construct()
    {
        //
    }

    //
}
