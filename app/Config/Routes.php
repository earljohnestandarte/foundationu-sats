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
    $routes->get('dashboard', 'TicketController::dashboard');
    $routes->get('tickets', 'TicketController::index');
    $routes->get('tickets/archived', 'TicketController::archived');
    $routes->get('tickets/create', 'TicketController::create');
    $routes->post('tickets/store', 'TicketController::store');
    $routes->get('tickets/(:num)', 'TicketController::view/$1');
    $routes->get('tickets/(:num)/thread', 'TicketController::thread/$1');
    $routes->post('tickets/(:num)/reply', 'TicketController::addReply/$1');
    $routes->post('tickets/(:num)/confirm', 'TicketController::confirm/$1');
    $routes->post('tickets/(:num)/reopen', 'TicketController::reopen/$1');
    $routes->post('tickets/(:num)/escalate', 'TicketController::escalate/$1');
    $routes->get('tickets/(:num)/rate', 'TicketController::rate/$1');
    $routes->post('tickets/(:num)/feedback', 'TicketController::saveFeedback/$1');
});

$routes->group('agent', ['filter' => ['auth', 'role:agent,sao,admin']], function ($routes) {
    $routes->get('dashboard', 'AgentController::dashboard');
    $routes->get('archived', 'AgentController::archived');
    $routes->get('view/(:num)', 'AgentController::view/$1');
    $routes->get('view/(:num)/thread', 'AgentController::thread/$1');
    $routes->post('assign/(:num)', 'AgentController::assign/$1');
    $routes->post('updateStatus/(:num)', 'AgentController::updateStatus/$1');
    $routes->post('addReply/(:num)', 'AgentController::addReply/$1');
    $routes->post('escalate/(:num)', 'AgentController::escalate/$1');
});

$routes->group('sao', ['filter' => ['auth', 'role:sao,admin']], function ($routes) {
    $routes->get('dashboard', 'ManagementController::dashboard');
    $routes->get('reports', 'ManagementController::reports');
    $routes->get('users', 'ManagementController::users');
    $routes->post('users/create', 'ManagementController::userCreate');
    $routes->post('users/edit/(:num)', 'ManagementController::userEdit/$1');
    $routes->post('users/delete/(:num)', 'ManagementController::userDelete/$1');
    $routes->post('users/toggle-active/(:num)', 'ManagementController::userToggleActive/$1');
    $routes->get('departments', 'ManagementController::departments');
    $routes->post('departments/create', 'ManagementController::departmentCreate');
    $routes->post('departments/edit/(:num)', 'ManagementController::departmentEdit/$1');
    $routes->post('departments/delete/(:num)', 'ManagementController::departmentDelete/$1');
    $routes->get('templates', 'ManagementController::templates');
    $routes->post('templates/create', 'ManagementController::templateCreate');
    $routes->post('templates/delete/(:num)', 'ManagementController::templateDelete/$1');
    $routes->get('templates/json', 'ManagementController::templatesJson');
});

$routes->group('notification', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'NotificationController::index');
    $routes->post('markAsRead/(:num)', 'NotificationController::markAsRead/$1');
});

$routes->group('search', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'SearchController::index');
});

$routes->group('attachment', ['filter' => 'auth'], function ($routes) {
    $routes->get('download/(:num)',  'AttachmentController::download/$1');
    $routes->post('delete/(:num)',   'AttachmentController::delete/$1');
});


$routes->group('ai', ['filter' => 'auth'], function ($routes) {
    $routes->get('suggest/(:num)', 'AiController::suggest/$1');
});
