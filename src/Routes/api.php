<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('api/dropdown', [YajTech\Crud\Controllers\Controller::class, 'dropdown']);