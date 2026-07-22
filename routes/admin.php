<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrganizationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/organizations/{organization}', [AdminOrganizationController::class, 'show'])->name('organizations.show');
    Route::delete('/organizations/{organization}/data', [AdminOrganizationController::class, 'destroyData'])->name('organizations.destroy-data');
    Route::put('/organizations/{organization}/users/{user}/quota', [AdminOrganizationController::class, 'updateQuota'])->name('organizations.users.update-quota');
    Route::delete('/organizations/{organization}/users/{user}', [AdminOrganizationController::class, 'destroyUser'])->name('organizations.users.destroy');
});
