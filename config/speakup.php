<?php

return [
    /*
    | Student walkthrough keeps Google off (frontend/src/config/index.ts).
    | The admin portal is the only place Google sign-in is offered.
    */
    'google_sign_in_enabled' => filter_var(env('GOOGLE_SIGN_IN_ENABLED', false), FILTER_VALIDATE_BOOL),
    'google_admin_sign_in_enabled' => filter_var(env('GOOGLE_ADMIN_SIGN_IN_ENABLED', true), FILTER_VALIDATE_BOOL),

    'email_domain' => 'gordoncollege.edu.ph',

    // Same allow-list as frontend/src/utils/admin/adminConfig.ts
    'admin_emails' => array_values(array_filter(array_map(
        fn ($email) => strtolower(trim($email)),
        explode(',', (string) env(
            'ADMIN_EMAILS',
            'admin@speakupgc.com,admin@safespace.com'
        ))
    ))),
];
