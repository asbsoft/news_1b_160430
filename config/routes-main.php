<?php
// route without prefix => controller/action without current (and parent) module(s) IDs
return [
    '<action:(view)>/<id:\d+>'    => 'main/<action>',
    '<action:(list)>/<page:\d+>'  => 'main/<action>',
    '<action:(index|list)>'       => 'main/<action>',
    '?'                           => 'main/index', // without URL-normalizer
];
