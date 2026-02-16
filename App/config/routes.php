<?php

/**
 * Routes configuration file.
 *
 * @category   Configuration
 * @package    App
 * @subpackage Config
 * @author     Dinesh RADJOU <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */

use Controllers\SAE\DeleteSaeController;
use Controllers\SAE\ModifySaeController;
use Controllers\SAE\ModifySaePostController;
use Controllers\SAE\PageSaeController;
use Controllers\Dashboard\DashboardController;
use Controllers\Index\IndexController;
use Controllers\Info\LegalNoticeController;
use Controllers\Info\SiteMapController;
use Controllers\pwd\ForgotPasswordController;
use Controllers\pwd\ForgotPasswordPostController;
use Controllers\pwd\ResetPasswordController;
use Controllers\pwd\ResetPasswordPostController;
use Controllers\SAE\CreateSaeController;
use Controllers\SAE\CreateSaePostController;
use Controllers\SAE\ManageGroupsController;
use Controllers\SAE\ManageGroupsPostController;
use Controllers\Settings\DeleteUserController;
use Controllers\Settings\EditProfileController;
use Controllers\Settings\EditProfilePost;
use Controllers\Settings\SettingsController;
use Controllers\ToDoList\ToDoListController;
use Controllers\ToDoList\ToDoListPost;
use Controllers\User\Login;
use Controllers\User\LoginPost;
use Controllers\User\Logout;
use Controllers\User\Register;
use Controllers\User\RegisterPost;

const ROUTES = [
    // ============================================================
    // PUBLIC ROUTES
    // ============================================================

    // Home
    '/' => [
        'GET' => ['controller' => IndexController::class , 'method' => 'control']
    ],
    '/index' => [
        'GET' => ['controller' => IndexController::class , 'method' => 'control']
    ],

    // Authentication
    '/login' => [
        'GET' => ['controller' => Login::class , 'method' => 'control'],
        'POST' => ['controller' => LoginPost::class , 'method' => 'control']
    ],
    '/register' => [
        'GET' => ['controller' => Register::class , 'method' => 'control'],
        'POST' => ['controller' => RegisterPost::class , 'method' => 'control']
    ],
    '/logout' => [
        'GET' => ['controller' => Logout::class , 'method' => 'control']
    ],

    // Password Reset
    '/forgot-password' => [
        'GET' => ['controller' => ForgotPasswordController::class , 'method' => 'control'],
        'POST' => ['controller' => ForgotPasswordPostController::class , 'method' => 'control']
    ],
    '/reset-password' => [
        'GET' => ['controller' => ResetPasswordController::class , 'method' => 'control'],
        'POST' => ['controller' => ResetPasswordPostController::class , 'method' => 'control']
    ],

    // Info
    '/legal-notice' => [
        'GET' => ['controller' => LegalNoticeController::class , 'method' => 'control']
    ],
    '/site-map' => [
        'GET' => ['controller' => SiteMapController::class , 'method' => 'control']
    ],

    // ============================================================
    // AUTHENTICATED ROUTES
    // ============================================================

    // Dashboard
    '/dashboard' => [
        'GET' => ['controller' => DashboardController::class , 'method' => 'control']
    ],

    // Settings
    '/settings' => [
        'GET' => ['controller' => SettingsController::class , 'method' => 'control']
    ],
    '/settings/edit-profile' => [
        'GET' => ['controller' => EditProfileController::class , 'method' => 'control'],
        'POST' => ['controller' => EditProfilePost::class , 'method' => 'control']
    ],
    '/settings/delete' => [
        'GET' => ['controller' => DeleteUserController::class , 'method' => 'control']
    ],

    // ============================================================
    // SAE ROUTES
    // ============================================================
    '/sae/create' => [
        'GET' => ['controller' => CreateSaeController::class , 'method' => 'control'],
        'POST' => ['controller' => CreateSaePostController::class , 'method' => 'control']
    ],
    '/sae/{id}/delete' => [
        'GET' => ['controller' => DeleteSaeController::class , 'method' => 'control'],
    ],
    '/sae/{id}' => [
        'GET' => ['controller' => PageSaeController::class , 'method' => 'control']
    ],
    '/sae/{id}/modify' => [
        'GET' => ['controller' => ModifySaeController::class , 'method' => 'control'],
        'POST' => ['controller' => ModifySaePostController::class , 'method' => 'control']
    ],
    '/sae/{id}/groups' => [
        'GET' => ['controller' => ManageGroupsController::class , 'method' => 'control'],
    ],
    '/sae/{id}/groups/create' => [
        'POST' => ['controller' => ManageGroupsPostController::class , 'method' => 'control']
    ],
    '/sae/{id}/groups/delete' => [
        'POST' => ['controller' => ManageGroupsPostController::class , 'method' => 'control']
    ],
    '/sae/{id}/groups/add-student' => [
        'POST' => ['controller' => ManageGroupsPostController::class , 'method' => 'control']
    ],
    '/sae/{id}/groups/remove-student' => [
        'POST' => ['controller' => ManageGroupsPostController::class , 'method' => 'control']
    ],

    // ============================================================
    // TODO LIST ROUTES
    // ============================================================

    '/sae/{id}/to-do' => [
        'GET' => ['controller' => ToDoListController::class , 'method' => 'control'],
    ],
    '/sae/{id}/to-do/add' => [
        'POST' => ['controller' => ToDoListPost::class , 'method' => 'control']
    ],
    '/sae/{id}/to-do/delete/{id}' => [
        'POST' => ['controller' => ToDoListPost::class , 'method' => 'control']
    ],
    '/sae/{id}/to-do/update/{id}' => [
        'POST' => ['controller' => ToDoListPost::class , 'method' => 'control']
    ],
];
