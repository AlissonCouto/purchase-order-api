<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->resource('clients', ['controller' => 'ClientController']);
$routes->resource('products', ['controller' => 'ProductController']);
$routes->resource('orders', ['controller' => 'OrderController']);
