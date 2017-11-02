<?php

/**
 * @var $routeConfig array
 */

use yii\rest\UrlRule as RestUrlRule;

if (empty($routeConfig['sublink'])) $routeConfig['sublink'] = 'rest-news';

// controller(s) sublink(s): link part => controllerUid, result link will be ".../$urlPrefix/$sublink/..."
$controller = [
    $routeConfig['sublink'] => $routeConfig['moduleUid'] . '/' . 'rest',
];

return [
    'enablePrettyUrl' => true,
    'rest-routes-' . $routeConfig['moduleUid'] => [
        'class' => RestUrlRule::className(),
        'controller' => $controller,
        'prefix' => $routeConfig['urlPrefix'],
        //'patterns' => [...], // if need
    ],
];
