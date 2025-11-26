<?php

use Slim\App;

$app->group('', function () use ($app) {
    $app->get('/hello',  \HomeController::class . ':hello');
})->add('loginLog');


$app->group('', function () use ($app) {
    $app->get('/hellotest',  \HomeController::class . ':hello');
    $app->group('/utility', function () use ($app) {
        $app->group('/classify_structure', function () use ($app) {
            $app->group('_type', function () use ($app) {
                $app->group('_depth', function () use ($app) {
                    $app->get('', \componentcontroller::class . ':get_classify_structure_type_depth');
                    $app->post('', \componentcontroller::class . ':post_classify_structure_type_depth');
                    $app->patch('', \componentcontroller::class . ':patch_classify_structure_type_depth');
                    $app->delete('', \componentcontroller::class . ':delete_classify_structure_type_depth');
                });
                $app->get('', \componentcontroller::class . ':get_classify_structure_type');
                $app->post('', \componentcontroller::class . ':post_classify_structure_type');
                $app->patch('', \componentcontroller::class . ':patch_classify_structure_type');
                $app->delete('', \componentcontroller::class . ':delete_classify_structure_type');
            });
            $app->group('_depth', function () use ($app) {
                $app->get('', \componentcontroller::class . ':get_classify_structure_depth');
                $app->post('', \componentcontroller::class . ':post_classify_structure_depth');
                $app->patch('', \componentcontroller::class . ':patch_classify_structure_depth');
                $app->delete('', \componentcontroller::class . ':delete_classify_structure_depth');
            });
            $app->get('', \componentcontroller::class . ':get_classify_structure');
            $app->post('', \componentcontroller::class . ':post_classify_structure');
            $app->patch('', \componentcontroller::class . ':patch_classify_structure');
            $app->delete('', \componentcontroller::class . ':delete_classify_structure');
        });
    });
})->add('logincheck');