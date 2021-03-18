<?php

use Bahramn\EcdIpg\Http\Controllers\TransactionCallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::any('payment/gateways/{gateway}/callback', TransactionCallbackController::class)
    ->name('payment.callback');
