<?php

use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\EnterpriseController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::apiResource('companys', CompanyController::class)->parameters(['companys' => 'id']);
        Route::patch('companys/{id}/activate', [CompanyController::class, 'activate']);
        Route::patch('companys/{id}/deactivate', [CompanyController::class, 'deactivate']);

        Route::apiResource('enterprises', EnterpriseController::class)->parameters(['enterprises' => 'id']);
        Route::patch('enterprises/{id}/activate', [EnterpriseController::class, 'activate']);
        Route::patch('enterprises/{id}/deactivate', [EnterpriseController::class, 'deactivate']);

        Route::apiResource('branchs', BranchController::class)->parameters(['branchs' => 'id']);
        Route::patch('branchs/{id}/activate', [BranchController::class, 'activate']);
        Route::patch('branchs/{id}/deactivate', [BranchController::class, 'deactivate']);
    });
});
