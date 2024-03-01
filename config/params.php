<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    // data directory is used to store uploaded files by the user (e.g. root dir for every user's home)
    'dataDirectory' => '@app/data',
    'verifyProvider' => $_ENV['VERIFY_PROVIDER'],
    'reCAPTCHA' => [
        'siteKey' => $_ENV['RECAPTCHA_SITE_KEY'],
        'secret' => $_ENV['RECAPTCHA_SECRET'],
    ],
    'hCaptcha' => [
        'siteKey' => $_ENV['HCAPTCHA_SITE_KEY'],
        'secret' => $_ENV['HCAPTCHA_SECRET'],
    ],
    'Turnstile' => [
        'siteKey' => $_ENV['TURNSTILE_SITE_KEY'],
        'secret' => $_ENV['TURNSTILE_SECRET'],
    ],
    'ipinfoToken' => $_ENV['IPINFO_TOKEN'],
];
