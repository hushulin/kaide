<?php

namespace App\Http\Controllers;

use Crada\Apidoc\Builder;
use Crada\Apidoc\Exception;

class ExampleController extends Controller
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
    public function gen()
    {

        $classes = array(
            'App\Http\Controllers\ExampleController',
        );

        $output_dir  = __DIR__.'/apidocs';
        $output_file = 'api.html'; // defaults to index.html

        try {
            $builder = new Builder($classes, $output_dir, 'Api Title', $output_file);
            $builder->generate();
        } catch (Exception $e) {
            echo 'There was an error generating the documentation: ', $e->getMessage();
        }

        return response('<a href="http://www.baidu.com">点击查看</a>');
    }
}
