<?php
// route without prefix => controller/action without current (and parent) module(s) IDs
return [
    '<action:(view|update|delete|change-visible)>/<id:\d+>' => 'admin/<action>',
    '<action:(index)>/<page:\d+>'                           => 'admin/<action>',
    '<action:(index|create)>'                               => 'admin/<action>',
    'el-finder/<action:(connect|manager)>/<id:\d+>'         => 'el-finder/<action>',
    '?'                                                     => 'admin/index', //!! no '' - never routes from root
];
