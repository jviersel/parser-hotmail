<?php

return [
    'parser' => [
        'name'          => 'Hotmail',
        'enabled'       => true,
        'sender_map'    => [
            '/staff@hotmail.com/',
        ],
        'body_map'      => [
            //
        ],
        // The aliases convert the body_map address into a more friendly source name
        'aliases'       => [
            '/staff@hotmail.com/'                           => 'Hotmail',
        ]
    ],

    'feeds' => [
        'default' => [
            'class'     => 'SPAM',
            'type'      => 'ABUSE',
            'enabled'   => true,
            'fields'    => [
                'Source-IP',
            ],
        ],

    ],
];