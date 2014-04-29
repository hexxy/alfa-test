<?php

use Symfony\Component\HttpFoundation\Request;

define('BASE_PATH', realpath(__DIR__ . '/'));
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/classes/pamm.php';

$app = new Silex\Application();
$app['debug'] = true;

/**
 * Регистрация шаблонизатора
 */
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => BASE_PATH . '/views',
));

/**
 *  Регистрация обертки БД
 */
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'astharot',
        'dbname' => 'alfa_test',
        'charset' => 'utf8',
    ),
));

/**
 *  Контроллер, отвечающий за вывод статистики по ПАММ счетам
 */

$app->get('/', function (Request $request) use ($app) {

    $pammModel = new PammAccount($app['db']);
    $pamms = $pammModel->fetchAll();

    return $app['twig']->render('index.html', array(
        'statistics' => $pamms
    ));

});

/**
 * Ajax-контроллер для получения подневной статистики по совокупности ПАММ счетов
 */
$app->get('/get-chart-data', function (Request $request) use ($app) {
    $ids = $request->get('pamm_ids');
    $pammModel = new PammAccount($app['db']);
    $statistics = $pammModel->getStatsForGraph($ids);
    return json_encode($statistics);
});


/**
 * Контроллер с описанием задания и особенностей реализации
 */
$app->get('/about', function (Request $request) use ($app) {
    return $app['twig']->render('about.html');
});

$app->run();
