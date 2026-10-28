<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// ========== WEB ROUTES (Sessions) ==========
$routes->get('/', 'Web\AuthController::loginForm');
$routes->get('login', 'Web\AuthController::loginForm');
$routes->post('auth/login', 'Web\AuthController::login');
$routes->get('logout', 'Web\AuthController::logout');

$routes->get('dashboard', 'Web\DashboardController::index');

// Users
$routes->get('users', 'Web\UserController::index');
$routes->post('users/save', 'Web\UserController::save');
$routes->get('users/delete/(:num)', 'Web\UserController::delete/$1');

// Roles
$routes->get('roles', 'Web\RoleController::index');
$routes->post('roles/save', 'Web\RoleController::save');
$routes->get('roles/delete/(:num)', 'Web\RoleController::delete/$1');

// Elections
$routes->get('elections', 'Web\ElectionController::index');
$routes->post('elections/save', 'Web\ElectionController::save');
$routes->get('elections/delete/(:num)', 'Web\ElectionController::delete/$1');

// Dignities
$routes->get('dignities', 'Web\DignityController::index');
$routes->post('dignities/save', 'Web\DignityController::save');
$routes->get('dignities/delete/(:num)', 'Web\DignityController::delete/$1');

// Parties
$routes->get('parties', 'Web\PartyController::index');
$routes->post('parties/save', 'Web\PartyController::save');
$routes->get('parties/delete/(:num)', 'Web\PartyController::delete/$1');

// Candidates
$routes->get('candidates', 'Web\CandidateController::index');
$routes->post('candidates/save', 'Web\CandidateController::save');
$routes->post('candidates/import', 'Web\CandidateController::import');
$routes->get('candidates/template', 'Web\CandidateController::template');
$routes->get('candidates/delete/(:num)', 'Web\CandidateController::delete/$1');

// Territorial
$routes->get('territorial', 'Web\TerritorialController::index');
$routes->post('territorial/save-province', 'Web\TerritorialController::saveProvince');
$routes->get('territorial/delete-province/(:num)', 'Web\TerritorialController::deleteProvince/$1');
$routes->post('territorial/save-canton', 'Web\TerritorialController::saveCanton');
$routes->get('territorial/delete-canton/(:num)', 'Web\TerritorialController::deleteCanton/$1');

// Polling Stations
$routes->get('polling-stations', 'Web\PollingStationController::index');
$routes->post('polling-stations/update', 'Web\PollingStationController::update');
$routes->post('polling-stations/import', 'Web\PollingStationController::import');
$routes->post('polling-stations/delete-all', 'Web\PollingStationController::deleteAll');

// Territorial AJAX (session-based)
$routes->get('ajax/cantons', 'Web\TerritorialAjaxController::cantons');
$routes->get('ajax/parishes', 'Web\TerritorialAjaxController::parishes');
$routes->get('ajax/precincts', 'Web\TerritorialAjaxController::precincts');
$routes->get('ajax/stations-by-precinct', 'Web\TerritorialAjaxController::stationsByPrecinct');
$routes->get('ajax/stations-by-election', 'Web\TerritorialAjaxController::stationsByElection');
$routes->get('ajax/cantons-by-province', 'Web\TerritorialAjaxController::cantonsByProvince');
$routes->get('ajax/precincts-by-canton', 'Web\TerritorialAjaxController::precinctsByCanton');
$routes->get('ajax/parishes-by-canton', 'Web\TerritorialAjaxController::parishesByCanton');
$routes->get('ajax/precincts-by-parish', 'Web\TerritorialAjaxController::precinctsByParish');
$routes->get('ajax/stations-by-location', 'Web\TerritorialAjaxController::stationsByLocation');
$routes->get('ajax/station-results/(:num)/(:num)', 'Web\TerritorialAjaxController::stationResults/$1/$2');
$routes->get('ajax/station-current-acta/(:num)', 'Web\TerritorialAjaxController::stationCurrentActa/$1');
$routes->get('ajax/audit-logs', 'Web\TerritorialAjaxController::auditLogs');
$routes->get('ajax/candidates-by-dignity/(:num)/(:num)', 'Web\TerritorialAjaxController::candidatesByDignity/$1/$2');
$routes->get('ajax/election-dignities/(:num)', 'Web\TerritorialAjaxController::electionDignities/$1');
$routes->get('ajax/results-by-dignity/(:num)/(:num)', 'Web\TerritorialAjaxController::resultsByDignity/$1/$2');
$routes->get('ajax/minute-data/(:num)', 'Web\TerritorialAjaxController::minuteData/$1');

// Assignments
$routes->get('assignments', 'Web\AssignmentController::index');
$routes->post('assignments/save', 'Web\AssignmentController::save');
$routes->post('assignments/save-batch', 'Web\AssignmentController::saveBatch');
$routes->post('assignments/update', 'Web\AssignmentController::update');
$routes->get('assignments/delete/(:num)', 'Web\AssignmentController::delete/$1');

// Results
$routes->get('results', 'Web\ResultController::index');
$routes->get('results/register', 'Web\ResultController::register');
$routes->post('results/save', 'Web\ResultController::save');

// Inconsistencies
$routes->get('inconsistencies', 'Web\InconsistencyController::index');

// System
$routes->post('system/migrate', 'Web\SystemController::migrate');

// Minutes
$routes->get('minutes', 'Web\MinuteController::index');
$routes->post('minutes/upload', 'Web\MinuteController::upload');
$routes->post('minutes/update', 'Web\MinuteController::update');
$routes->get('minutes/preview/(:num)', 'Web\MinuteController::preview/$1');
$routes->get('minutes/download/(:num)', 'Web\MinuteController::download/$1');
$routes->get('minutes/delete/(:num)', 'Web\MinuteController::delete/$1');

// Reports
$routes->get('reports', 'Web\ReportController::index');
$routes->get('reports/export-pdf', 'Web\ReportController::exportPdf');
$routes->get('reports/export-excel', 'Web\ReportController::exportExcel');
$routes->get('reports/advance', 'Web\ReportController::advance');

// Audit
$routes->get('audit', 'Web\AuditController::index');

// Profile
$routes->get('profile', 'Web\ProfileController::index');
$routes->post('profile/update', 'Web\ProfileController::update');
$routes->post('profile/password', 'Web\ProfileController::password');

// ========== API ROUTES (JSON, JWT) ==========
$routes->group('api', function ($routes) {
    $routes->post('auth/login', 'Api\AuthController::login');
    $routes->post('auth/refresh', 'Api\AuthController::refresh');

    $routes->group('', ['filter' => 'jwtauth'], function ($routes) {
        $routes->get('auth/me', 'Api\AuthController::me');
        $routes->post('auth/logout', 'Api\AuthController::logout');
        $routes->put('auth/profile', 'Api\AuthController::updateProfile');

        $routes->get('users', 'Api\UserController::index');
        $routes->get('users/(:num)', 'Api\UserController::show/$1');
        $routes->post('users', 'Api\UserController::create');
        $routes->put('users/(:num)', 'Api\UserController::update/$1');
        $routes->delete('users/(:num)', 'Api\UserController::delete/$1');
        $routes->get('users/operators', 'Api\UserController::operators');
        $routes->get('users/supervisors', 'Api\UserController::supervisors');

        $routes->get('roles', 'Api\RoleController::index');
        $routes->get('roles/(:num)', 'Api\RoleController::show/$1');
        $routes->post('roles', 'Api\RoleController::create');
        $routes->put('roles/(:num)', 'Api\RoleController::update/$1');
        $routes->delete('roles/(:num)', 'Api\RoleController::delete/$1');
        $routes->get('permissions', 'Api\RoleController::permissions');

        $routes->get('elections', 'Api\ElectionController::index');
        $routes->get('elections/current', 'Api\ElectionController::current');
        $routes->get('elections/(:num)', 'Api\ElectionController::show/$1');
        $routes->post('elections', 'Api\ElectionController::create');
        $routes->put('elections/(:num)', 'Api\ElectionController::update/$1');
        $routes->delete('elections/(:num)', 'Api\ElectionController::delete/$1');

        $routes->get('dignities', 'Api\DignityController::index');
        $routes->get('dignities/(:num)', 'Api\DignityController::show/$1');
        $routes->post('dignities', 'Api\DignityController::create');
        $routes->put('dignities/(:num)', 'Api\DignityController::update/$1');
        $routes->delete('dignities/(:num)', 'Api\DignityController::delete/$1');

        $routes->get('parties', 'Api\PoliticalPartyController::index');
        $routes->get('parties/(:num)', 'Api\PoliticalPartyController::show/$1');
        $routes->post('parties', 'Api\PoliticalPartyController::create');
        $routes->put('parties/(:num)', 'Api\PoliticalPartyController::update/$1');
        $routes->delete('parties/(:num)', 'Api\PoliticalPartyController::delete/$1');

        $routes->get('lists', 'Api\CandidateListController::index');
        $routes->get('lists/(:num)', 'Api\CandidateListController::show/$1');
        $routes->post('lists', 'Api\CandidateListController::create');
        $routes->put('lists/(:num)', 'Api\CandidateListController::update/$1');
        $routes->delete('lists/(:num)', 'Api\CandidateListController::delete/$1');
        $routes->get('lists/by-election/(:num)', 'Api\CandidateListController::byElection/$1');

        $routes->get('candidates', 'Api\CandidateController::index');
        $routes->get('candidates/(:num)', 'Api\CandidateController::show/$1');
        $routes->post('candidates', 'Api\CandidateController::create');
        $routes->put('candidates/(:num)', 'Api\CandidateController::update/$1');
        $routes->delete('candidates/(:num)', 'Api\CandidateController::delete/$1');
        $routes->get('candidates/by-election/(:num)', 'Api\CandidateController::byElection/$1');
        $routes->get('candidates/by-dignity/(:num)/(:num)', 'Api\CandidateController::byDignity/$1/$2');

        $routes->get('provinces', 'Api\ProvinceController::index');
        $routes->get('provinces/with-cantons', 'Api\ProvinceController::withCantons');
        $routes->get('provinces/(:num)', 'Api\ProvinceController::show/$1');
        $routes->post('provinces', 'Api\ProvinceController::create');
        $routes->put('provinces/(:num)', 'Api\ProvinceController::update/$1');
        $routes->delete('provinces/(:num)', 'Api\ProvinceController::delete/$1');
        $routes->get('cantons', 'Api\CantonController::index');
        $routes->get('cantons/(:num)', 'Api\CantonController::show/$1');
        $routes->post('cantons', 'Api\CantonController::create');
        $routes->put('cantons/(:num)', 'Api\CantonController::update/$1');
        $routes->delete('cantons/(:num)', 'Api\CantonController::delete/$1');
        $routes->get('parishes', 'Api\ParishController::index');
        $routes->get('parishes/(:num)', 'Api\ParishController::show/$1');
        $routes->post('parishes', 'Api\ParishController::create');
        $routes->put('parishes/(:num)', 'Api\ParishController::update/$1');
        $routes->delete('parishes/(:num)', 'Api\ParishController::delete/$1');
        $routes->get('precincts', 'Api\PrecinctController::index');
        $routes->get('precincts/(:num)', 'Api\PrecinctController::show/$1');
        $routes->post('precincts', 'Api\PrecinctController::create');
        $routes->put('precincts/(:num)', 'Api\PrecinctController::update/$1');
        $routes->delete('precincts/(:num)', 'Api\PrecinctController::delete/$1');

        $routes->get('polling-stations', 'Api\PollingStationController::index');
        $routes->get('polling-stations/stats', 'Api\PollingStationController::stats');
        $routes->get('polling-stations/my-stations', 'Api\PollingStationController::myStations');
        $routes->get('polling-stations/(:num)', 'Api\PollingStationController::show/$1');
        $routes->post('polling-stations', 'Api\PollingStationController::create');
        $routes->post('polling-stations/import', 'Api\PollingStationController::import');
        $routes->put('polling-stations/(:num)', 'Api\PollingStationController::update/$1');
        $routes->put('polling-stations/(:num)/status', 'Api\PollingStationController::updateStatus/$1');
        $routes->delete('polling-stations/(:num)', 'Api\PollingStationController::delete/$1');

        $routes->get('assignments', 'Api\AssignmentController::index');
        $routes->get('assignments/my-assignments', 'Api\AssignmentController::myAssignments');
        $routes->get('assignments/(:num)', 'Api\AssignmentController::show/$1');
        $routes->post('assignments', 'Api\AssignmentController::create');
        $routes->post('assignments/batch', 'Api\AssignmentController::createBatch');
        $routes->delete('assignments/(:num)', 'Api\AssignmentController::delete/$1');

        $routes->get('results', 'Api\ResultController::index');
        $routes->get('results/(:num)', 'Api\ResultController::show/$1');
        $routes->post('results', 'Api\ResultController::create');
        $routes->put('results/(:num)', 'Api\ResultController::update/$1');
        $routes->put('results/(:num)/verify', 'Api\ResultController::verify/$1');
        $routes->delete('results/(:num)', 'Api\ResultController::delete/$1');
        $routes->get('results/by-station/(:num)', 'Api\ResultController::byPollingStation/$1');
        $routes->get('results/by-dignity/(:num)/(:num)', 'Api\ResultController::byDignity/$1/$2');
        $routes->get('results/by-province/(:num)/(:num)/(:any)', 'Api\ResultController::byProvince/$1/$2/$3');

        $routes->get('minutes', 'Api\MinuteController::index');
        $routes->get('minutes/(:num)', 'Api\MinuteController::show/$1');
        $routes->post('minutes/upload', 'Api\MinuteController::upload');
        $routes->get('minutes/(:num)/download', 'Api\MinuteController::download/$1');
        $routes->get('minutes/(:num)/preview', 'Api\MinuteController::preview/$1');
        $routes->delete('minutes/(:num)', 'Api\MinuteController::delete/$1');

        $routes->get('dashboard', 'Api\DashboardController::index');
        $routes->get('dashboard/by-province', 'Api\DashboardController::byProvince');
        $routes->get('dashboard/participation', 'Api\DashboardController::participation');
        $routes->get('dashboard/pending-stations', 'Api\DashboardController::pendingStations');

        $routes->get('reports/results-by-province/(:num)', 'Api\ReportController::resultsByProvince/$1');
        $routes->get('reports/export-pdf', 'Api\ReportController::exportPdf');
        $routes->get('reports/export-excel', 'Api\ReportController::exportExcel');
        $routes->get('reports/advance', 'Api\ReportController::advanceReport');

        $routes->get('audits', 'Api\AuditController::index');
        $routes->get('audits/(:num)', 'Api\AuditController::show/$1');
    });
});
