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
use Controllers\Password\ForgotPasswordController;
use Controllers\Password\ForgotPasswordPostController;
use Controllers\Password\ResetPasswordController;
use Controllers\Password\ResetPasswordPostController;
use Controllers\SAE\CreateSaeController;
use Controllers\SAE\CreateSaePostController;
use Controllers\SAE\ManageGroupsController;
use Controllers\SAE\ManageGroupsPostController;
use Controllers\Settings\DeleteUserController;
use Controllers\Settings\EditProfileController;
use Controllers\Settings\EditProfilePostController;
use Controllers\Settings\SettingsController;
use Controllers\ToDoList\ToDoListController;
use Controllers\ToDoList\ToDoListAddPostController;
use Controllers\ToDoList\ToDoListUpdatePostController;
use Controllers\ToDoList\ToDoListDeletePostController;
use Controllers\User\LoginController;
use Controllers\User\LoginPostController;
use Controllers\User\LogoutController;
use Controllers\User\RegisterController;
use Controllers\User\RegisterPostController;
use Controllers\LegalTermsConditions\ConfigConservationDateController;
use Controllers\LegalTermsConditions\LegalTermsConditionsController;

const ROUTES = [
    // ============================================================
    // PUBLIC ROUTES
    // ============================================================

    // Home
    '/^\/$/' => [
        'GET' => ['controller' => IndexController::class , 'method' => 'control']
    ],
    '/^\/index$/' => [
        'GET' => ['controller' => IndexController::class , 'method' => 'control']
    ],

    // Authentication
    '/^\/login$/' => [
        'GET' => ['controller' => LoginController::class , 'method' => 'control'],
        'POST' => ['controller' => LoginPostController::class , 'method' => 'control']
    ],
    '/^\/register$/' => [
        'GET' => ['controller' => RegisterController::class , 'method' => 'control'],
        'POST' => ['controller' => RegisterPostController::class , 'method' => 'control']
    ],
    '/^\/logout$/' => [
        'GET' => ['controller' => LogoutController::class , 'method' => 'control']
    ],

    // Password Reset
    '/^\/forgot-password$/' => [
        'GET' => ['controller' => ForgotPasswordController::class , 'method' => 'control'],
        'POST' => ['controller' => ForgotPasswordPostController::class , 'method' => 'control']
    ],
    '/^\/reset-password$/' => [
        'GET' => ['controller' => ResetPasswordController::class , 'method' => 'control'],
        'POST' => ['controller' => ResetPasswordPostController::class , 'method' => 'control']
    ],

    // Info
    '/^\/legal-notice$/' => [
        'GET' => ['controller' => LegalNoticeController::class , 'method' => 'control']
    ],
    '/^\/site-map$/' => [
        'GET' => ['controller' => SiteMapController::class , 'method' => 'control']
    ],

    // ============================================================
    // AUTHENTICATED ROUTES
    // ============================================================

    // Dashboard
    '/^\/dashboard$/' => [
        'GET' => ['controller' => DashboardController::class , 'method' => 'control']
    ],

    // Settings
    '/^\/settings$/' => [
        'GET' => ['controller' => SettingsController::class , 'method' => 'control']
    ],
    '/^\/settings\/edit-profile$/' => [
        'GET' => ['controller' => EditProfileController::class , 'method' => 'control'],
        'POST' => ['controller' => EditProfilePostController::class , 'method' => 'control']
    ],
    '/^\/settings\/delete$/' => [
        'GET' => ['controller' => DeleteUserController::class , 'method' => 'control']
    ],

    // ============================================================
    // SAE ROUTES
    // ============================================================
    '/^\/sae\/create$/' => [
        'GET' => ['controller' => CreateSaeController::class , 'method' => 'control'],
        'POST' => ['controller' => CreateSaePostController::class , 'method' => 'control']
    ],
    '/^\/sae\/(?<saeId>\d+)\/delete$/' => [
        'GET' => ['controller' => DeleteSaeController::class , 'method' => 'control'],
    ],
    '/^\/sae\/(?<saeId>\d+)$/' => [
        'GET' => ['controller' => PageSaeController::class , 'method' => 'control']
    ],
    '/^\/sae\/(?<saeId>\d+)\/modify$/' => [
        'GET' => ['controller' => ModifySaeController::class , 'method' => 'control'],
        'POST' => ['controller' => ModifySaePostController::class , 'method' => 'control']
    ],
    '/^\/sae\/(?<saeId>\d+)\/groups$/' => [
        'GET' => ['controller' => ManageGroupsController::class , 'method' => 'control'],
    ],
    '/^\/sae\/(?<saeId>\d+)\/groups\/(?<action>create|delete|add-student|remove-student)$/' => [
        'POST' => ['controller' => ManageGroupsPostController::class , 'method' => 'control']
    ],

    // ============================================================
    // TODO LIST ROUTES
    // ============================================================

    '/^\/sae\/(?<saeId>\d+)\/to-do$/' => [
        'GET' => ['controller' => ToDoListController::class , 'method' => 'control'],
    ],
    '/^\/sae\/(?<saeId>\d+)\/to-do\/add$/' => [
        'POST' => ['controller' => ToDoListAddPostController::class , 'method' => 'control']
    ],
    '/^\/sae\/(?<saeId>\d+)\/to-do\/update\/(?<todoId>\d+)$/' => [
        'POST' => ['controller' => ToDoListUpdatePostController::class , 'method' => 'control']
    ],
    '/^\/sae\/(?<saeId>\d+)\/to-do\/delete\/(?<todoId>\d+)$/' => [
        'POST' => ['controller' => ToDoListDeletePostController::class , 'method' => 'control']
    ],

    // ============================================================
    // General Conditions
    // ============================================================

    '/^\/conservation-date$/' => [
        'GET' => ['controller' => ConfigConservationDateController::class , 'method' => 'control']
    ],

    '/^\/legal-terms-conditions$/' => [
        'GET' => ['controller' => LegalTermsConditionsController::class , 'method' => 'control']
    ]
];
