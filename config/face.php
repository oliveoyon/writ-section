<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Face Match Threshold
    |--------------------------------------------------------------------------
    |
    | Lower is stricter. 0.6 is common for demos, but too loose for real use.
    | 0.42-0.50 is typically safer for one-to-one verification by login_id.
    |
    */
    'match_threshold' => env('FACE_MATCH_THRESHOLD', 0.42),
];

