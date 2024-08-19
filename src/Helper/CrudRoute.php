<?php

namespace Kazi\Crud\Helper;

use Illuminate\Support\Facades\Route;

class CrudRoute
{
    public static function generateRoutes($prefix, $controller, $array = [])
    {
        Route::prefix($prefix)->group(function () use ($array, $controller) {
            if (
                in_array('index', $array) || in_array('getAll', $array)
                || in_array('store', $array) || in_array('show', $array)
                || in_array('update', $array) || in_array('changeStatus', $array)
                || in_array('getMetaData', $array) || in_array('export', $array)
            ) {
                $routes = [];
                if (in_array('index', $array)) {
                    $routes[] = ['method' => 'get', 'uri' => '', 'action' => 'index'];
                }
                if (in_array('getAll', $array)) {
                    $routes[] = ['method' => 'get', 'uri' => 'all', 'action' => 'getAll'];
                }
                if (in_array('store', $array)) {
                    $routes[] = ['method' => 'post', 'uri' => '', 'action' => 'store'];
                }
                if (in_array('getMetaData', $array)) {
                    $routes[] = ['method' => 'get', 'uri' => 'meta-data', 'action' => 'getMetaData'];
                }
                if (in_array('delete', $array)) {
                    $routes[] = ['method' => 'post', 'uri' => 'delete', 'action' => 'delete'];
                }
                if (in_array('show', $array)) {
                    $routes[] = ['method' => 'get', 'uri' => '{id}', 'action' => 'show'];
                }
                if (in_array('update', $array)) {
                    $routes[] = ['method' => 'post', 'uri' => '{id}', 'action' => 'update'];
                }
                if (in_array('changeStatus', $array)) {
                    $routes[] = ['method' => 'put', 'uri' => '{id}/change-status', 'action' => 'changeStatus'];
                }
                if (in_array('export', $array)) {
                    $routes[] = ['method' => 'get', 'uri' => 'export', 'action' => 'export'];
                }
            } else {
                $routes = [
                    ['method' => 'get', 'uri' => '', 'action' => 'index'],
                    ['method' => 'post', 'uri' => 'delete', 'action' => 'delete'],
                    ['method' => 'get', 'uri' => 'all', 'action' => 'getAll'],
                    ['method' => 'post', 'uri' => '', 'action' => 'store'],
                    ['method' => 'get', 'uri' => 'meta-data', 'action' => 'getMetaData'],
                    ['method' => 'get', 'uri' => 'export', 'action' => 'export'],
                    ['method' => 'get', 'uri' => '{id}', 'action' => 'show'],
                    ['method' => 'post', 'uri' => '{id}', 'action' => 'update'],
                    ['method' => 'put', 'uri' => '{id}/change-status', 'action' => 'changeStatus'],
                ];
            }

            foreach ($routes as $route) {
                Route::{$route['method']}($route['uri'], "{$controller}@{$route['action']}");
            }
        });
    }

}

