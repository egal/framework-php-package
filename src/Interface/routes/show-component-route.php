<?php

use Illuminate\Support\Facades\Route;

Route::get('/interface-metadata/{label}', [\Egal\Interface\Http\Controller::class, 'show']);
