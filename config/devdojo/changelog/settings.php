<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Host User model
    |--------------------------------------------------------------------------
    | The changelog package relies on the host application's User model for the
    | read-tracking pivot. Leave null to resolve it from the auth config.
    */
    'user_model' => env('CHANGELOG_USER_MODEL'),
];
