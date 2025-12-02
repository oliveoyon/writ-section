<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PermissionGroupController;
use App\Http\Controllers\Admin\PermissionManagerController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LawyerCaseController;
use App\Http\Controllers\LawyerController;
use App\Http\Controllers\LawyerRegistrationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;


Route::get('language/{locale}', [LanguageController::class, 'setLocale'])->name('locale.set');
Route::get('/', [WebController::class, 'index'])->name('web.home');

// Lawyer login and registration
Route::get('/lawyer/login', [WebController::class, 'login'])->name('lawyer.login');
Route::post('/lawyer/login', [WebController::class, 'lawyerLoginSubmit'])->name('lawyer.login.submit');
Route::get('/lawyer/register', [LawyerRegistrationController::class, 'showForm'])->name('lawyer.register');
Route::post('/lawyer/check-member', [LawyerRegistrationController::class, 'checkMember'])->name('lawyer.check-member');
Route::post('/lawyer/register', [LawyerRegistrationController::class, 'register'])->name('lawyer.register.submit');

// Lawyer routes only
Route::middleware(['auth', 'checkUserType:lawyer'])->prefix('lawyer')->group(function() {
    Route::get('/dashboard', [LawyerController::class, 'dashboard'])->name('lawyer.dashboard');
    Route::get('/my-cases', [LawyerController::class, 'myCases'])->name('lawyer.my_cases');
    Route::get('/notifications', [LawyerController::class, 'notifications'])->name('lawyer.notifications');
    Route::get('/messages', [LawyerController::class, 'messages'])->name('lawyer.messages');
    Route::get('/documents', [LawyerController::class, 'documents'])->name('lawyer.documents');
    Route::get('/settings', [LawyerController::class, 'settings'])->name('lawyer.settings');
    Route::post('/settings/update', [LawyerController::class, 'settingsUpdate'])->name('lawyer.settings.update');



    Route::get('/case/create', [LawyerCaseController::class, 'create'])->name('lawyer.case.create');
    Route::post('/case/store', [LawyerCaseController::class, 'store'])->name('lawyer.case.store');
    Route::get('/case/{case}/top-sheet', [LawyerCaseController::class, 'printTopSheet'])->name('lawyer.case.top_sheet');
    
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::prefix('admin')->name('admin.')->middleware(['auth', 'checkUserType:admin'])->group(function () {
    Route::get('/homes', [DashboardController::class, 'homes'])->name('dashboard.homes');
    
    Route::get('/home', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home-test', [DashboardController::class, 'test'])->name('dashboard.test');
    Route::resource('permission-groups', PermissionGroupController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->except(['show']);
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    Route::post('roles/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])->name('roles.assignPermissions');

    
    Route::get('permission-manager', [PermissionManagerController::class, 'index'])->name('permission-manager.index');
    Route::get('permission-manager/{group}/permissions', [PermissionManagerController::class, 'groupPermissions'])->name('permission-manager.permissions');
    Route::post('permission-manager/group', [PermissionManagerController::class, 'storeGroup'])->name('permission-manager.group.store');
    Route::put('permission-manager/group/{id}', [PermissionManagerController::class, 'updateGroup'])->name('permission-manager.group.update');
    Route::delete('permission-manager/group/{id}', [PermissionManagerController::class, 'destroyGroup'])->name('permission-manager.group.destroy');
    Route::post('permission-manager/permission', [PermissionManagerController::class, 'storePermission'])->name('permission-manager.permission.store');
    Route::put('permission-manager/permission/{id}', [PermissionManagerController::class, 'updatePermission'])->name('permission-manager.permission.update');
    Route::delete('permission-manager/permission/{id}', [PermissionManagerController::class, 'destroyPermission'])->name('permission-manager.permission.destroy');

    Route::resource('users', UserController::class);


});

require __DIR__.'/auth.php';
