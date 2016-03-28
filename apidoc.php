<?php
require_once __DIR__.'/vendor/autoload.php';

use Crada\Apidoc\Builder;
use Crada\Apidoc\Exception;

$classes = array(
   'App\Http\Controllers\ExampleController',
   'App\Http\Controllers\UserController',
   'App\Http\Controllers\MeterController',
   'App\Http\Controllers\XiaofeiController',
   'App\Http\Controllers\NotificationController',
);

$output_dir  = __DIR__.'/public/apidocs';
$output_file = 'api.html'; // defaults to index.html

try {
    $builder = new Builder($classes, $output_dir, '开德API(V1.0.0)', $output_file);
    $builder->generate();
} catch (Exception $e) {
    echo 'There was an error generating the documentation: ', $e->getMessage();
}
