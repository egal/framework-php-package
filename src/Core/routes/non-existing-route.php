<?php

use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    abort(404, 'API resource not found');
});
