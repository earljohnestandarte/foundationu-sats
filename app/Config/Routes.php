<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::index');
$routes->get('login', 'AuthController::index');
$routes->post('login', 'AuthController::attempt');
$routes->get('logout', 'AuthController::logout');

$routes->group('student', ['filter' => ['auth', 'role:student']], function ($routes) {
    $routes->get('tickets', 'TicketController::index');
    $routes->get('tickets/create', 'TicketController::create');
    $routes->post('tickets/store', 'TicketController::store');
    $routes->get('tickets/(:num)', 'TicketController::view/$1');
    $routes->post('tickets/(:num)/reply', 'TicketController::addReply/$1');
});

$routes->group('agent', ['filter' => ['auth', 'role:agent']], function ($routes) {
    $routes->get('dashboard', 'AgentController::dashboard');
    $routes->get('view/(:num)', 'AgentController::view/$1');
    $routes->post('assign/(:num)', 'AgentController::assign/$1');
    $routes->post('updateStatus/(:num)', 'AgentController::updateStatus/$1');
    $routes->post('addReply/(:num)', 'AgentController::addReply/$1');
    $routes->post('reassign/(:num)', 'AgentController::reassign/$1');
});

$routes->group('notification', ['filter' => 'auth'], function ($routes) {
    $routes->post('markAsRead/(:num)', 'NotificationController::markAsRead/$1');
});
