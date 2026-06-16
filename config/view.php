<?php

$compiledPath = env('VIEW_COMPILED_PATH', storage_path('framework/views'));

if (!str_starts_with($compiledPath, DIRECTORY_SEPARATOR) && !preg_match('/^[A-Za-z]:[\/\\\\]/', $compiledPath)) {
    $compiledPath = base_path($compiledPath);
}

return [
    'paths' => [
        resource_path('views'),
    ],

    'compiled' => $compiledPath,
];
