<?php

use App\Http\Controllers\NetworkCalculatorController;
use App\Http\Controllers\NetworkBasicsController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')->name('dashboard');

Route::get('/calculator', [NetworkCalculatorController::class, 'index'])
    ->name('calculator');

Route::post('/calculate', [NetworkCalculatorController::class, 'calculate'])
    ->name('calculator.calculate');

Route::get('/network-basics', [NetworkBasicsController::class, 'index'])->name('network-basics');
Route::post('/network-basics', [NetworkBasicsController::class, 'generate'])->name('network-basics.generate');
