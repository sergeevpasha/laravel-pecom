<?php

use Illuminate\Support\Facades\Route;
use SergeevPasha\Pecom\Http\Controllers\PecomController;

Route::get('/cities', [PecomController::class, 'queryCity'])
    ->name('pecom.cities');
Route::get('/cities/{city}/terminals', [PecomController::class, 'getCityTerminals'])
    ->name('pecom.cities.terminals');
Route::post('/calculate', [PecomController::class, 'calculateDeliveryPrice'])
    ->name('pecom.calculate');
Route::get('/history', [PecomController::class, 'getCargoStatus'])
    ->name('pecom.history');