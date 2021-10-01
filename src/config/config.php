<?php
$config = [
    'CARDS'  => 'cards',
    'LAYOUT' => BASE_DIR . '/templates/layout/layout.html',
    'HOME'   => 'home.phtml',   // default home page
    'HOST'   => '',
    'DELIM'  => '%%',
    // use '' for CACHE if you want to disable it
    'CACHE'  => str_replace('//', '/', BASE_DIR . '/logs/cache.txt'),
    'META' => [
        'default' => [
            'title' => 'SimpleHtml',
            'keywords' => 'php, html, simple',
            'description'  => 'Once installed all you need to do is to upload HTML snippets into the site templates folder',
        ],
    ],
    'SUPER' => [
        'username' => 'admin',
        'password' => 'password',
        'attempts' => 3,
        'message'  => 'Sorry! Unable to login.  Please contact your administrator',
        // array of $_SERVER keys to store in session if authenticated
        'profile'  => ['REMOTE_ADDR','HTTP_USER_AGENT','HTTP_ACCEPT_LANGUAGE','HTTP_COOKIE'],
        // change the values to reflect the names of fiels in your login.phtml form
        'login_fields' => [
            'name'     => 'name',
            'password' => 'password',
            'other'    => 'other',
            'phrase'   => 'phrase',     // CAPTCHA phrase
        ],
        'validation'   => [
            'City' => 'London',
            'Postal Code' => '12345',
            'Last Name' => 'Smith',
        ],
        'allowed_ext'  => ['html','htm'],
        'ckeditor'     => [
            'width' => '100%',
            'height' => 400,
        ],
    ],
    'STORAGE' => [
        'db_host' => 'localhost',
        'db_name' => 'REPL_DB_NAME',
        'db_user' => 'REPL_DB_USER',
        'db_pwd'  => 'REPL_DB_PWD',
        'tables'  => [
            'contacts' => [
                'name'    => function($item) { return trim(substr($item, 0, 64));   },
                'email'   => function($item) { return trim(substr($item, 0, 255));  },
                'phone'   => function($item) { return trim(substr($item, 0, 32));   },
                'subject' => function($item) { return trim(substr($item, 0, 64));   },
                'source'  => function($item) { return trim(substr($item, 0, 64));   },
                'message' => function($item) { return trim(substr($item, 0, 4096)); },
                'created' => function()      { return date('Y-m-d H:i:s');    },
            ],
        ],
    ],
    'COMPANY_EMAIL' => [
        'to'   => '',
        'cc'   => '',
        'from' => '',
        'SUCCESS' => '<span style="color:green;font-weight:700;">Thanks!  Your request has been sent.</span>',
        'ERROR'   => '<span style="color:red;font-weight:700;">Sorry!  Your question, comment or request info was not received.</span>',
        'phpmailer' => [
            'smtp'          => TRUE,                // Use SMTP (true) or PHP Mail() function (false)
            'smtp_host'     => 'REPL_SMTP_HOST',    // SMTP server address - URL or IP
            'smtp_port'     => 587,                 // 25 (standard), 465 (SSL), or 587 (TLS)
            'smtp_auth'     => TRUE,                // SMTP Authentication - PLAIN
            'smtp_username' => 'REPL_SMTP_USERNAME',// Username if smtp_auth is true
            'smtp_password' => 'REPL_SMTP_PASSWORD',// Password if smtp_auth is true
            'smtp_secure'   => 'tls',               // Supported SMTP secure connection - 'none, 'ssl', or 'tls'
        ],
    ],
    'MSG_MARKER'    => '<!-- %%MESSAGES%% -->',
    'CONTACT_LOG'   => BASE_DIR . '/logs/contact.log',
    'CAPTCHA' => [
        'input_tag_name' => 'phrase',
        'sess_hash_key'  => 'hash',
        'font_file'      => SRC_DIR . '/fonts/FreeSansBold.ttf',
        'img_dir'        => BASE_DIR . '/public/img/captcha',
        'num_bytes'      => 2,
    ],
];
return $config;
