<?php

use Illuminate\Support\Facades\Route as IlluminateRoute;

IlluminateRoute::fallback(function () {
    abort(404, 'API resource not found');
});
