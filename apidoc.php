<?php
require_once __DIR__.'/vendor/autoload.php';

use Crada\Apidoc\Builder;
use Crada\Apidoc\Exception;

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
