<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default School ID for Global Holidays
    |--------------------------------------------------------------------------
    |
    | This value specifies the default school ID to associate with global
    | public holidays. If a holiday is not specifically linked to an ecole,
    | it might be considered a global holiday applicable to this default ecole.
    | Set to null if no default global ecole is desired.
    |
    */

    'default_ecole_id' => env('HOLIDAYS_DEFAULT_ECOLE_ID', null),

    /*
    |--------------------------------------------------------------------------
    | Enable Holiday-based Logic
    |--------------------------------------------------------------------------
    |
    | This flag determines whether the application should actively consider
    | public holidays in its logic (e.g., for scheduling, notifications).
    | Set to false to disable all holiday-aware features globally.
    |
    */

    'enabled' => env('HOLIDAYS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------    | Default Behavior for Siren Programmations on Holidays
    |--------------------------------------------------------------------------
    |
    | This setting defines the default behavior for siren programmations
    | when a day is identified as a public holiday. This can be overridden
    | by individual programmation settings (jours_feries_inclus).
    |
    | Options: 'include' (programmations run), 'exclude' (programmations skipped)
    |
    */

    'default_siren_behavior' => env('HOLIDAYS_DEFAULT_SIREN_BEHAVIOR', 'exclude'),

];
