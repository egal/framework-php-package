<?php

use Illuminate\Support\Facades\Route;

Route::post('/register', [\Egal\Core\Auth\Controller::class, 'register']);
Route::post('/login', [\Egal\Core\Auth\Controller::class, 'login'])->middleware(['session', 'api']);
Route::post('/refreshToken', [\Egal\Core\Auth\Controller::class, 'refreshToken'])->middleware(['session', 'api']);
