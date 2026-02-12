<?php

$defaults = require base_path('vendor/laravel/octane/config/octane.php');

// Keep the package defaults, but make request timeout explicit and configurable.
$defaults['max_execution_time'] = (int) env('OCTANE_MAX_EXECUTION_TIME', 30);

return $defaults;
