<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\CourtController;
use App\Http\Controllers\Admin\FilingController;
use App\Http\Controllers\Admin\CourtDispatchController;
use App\Http\Controllers\Admin\RegistrarTrackingController;
use App\Http\Controllers\Admin\SectionReceiveController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LawyerCaseController;
use App\Http\Controllers\LawyerController;
use App\Http\Controllers\LawyerRegistrationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

Route::get('/logout-all', function () {

    if (Auth::check()) {

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->delete();

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    return redirect('/login');

})->middleware('auth')->name('logout.all');


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
    Route::delete('{case}', [LawyerCaseController::class, 'destroy'])->name('lawyer.case.destroy');

    Route::get('/notifications', [LawyerController::class, 'notifications'])->name('lawyer.notifications');
    Route::get('/messages', [LawyerController::class, 'messages'])->name('lawyer.messages');
    Route::get('/documents', [LawyerController::class, 'documents'])->name('lawyer.documents');
    Route::get('/settings', [LawyerController::class, 'settings'])->name('lawyer.settings');
    Route::post('/settings/update', [LawyerController::class, 'settingsUpdate'])->name('lawyer.settings.update');

    Route::get('cases/create', [LawyerCaseController::class, 'create'])->name('lawyer.case.create');
    Route::post('cases', [LawyerCaseController::class, 'store'])->name('lawyer.case.store');
    Route::get('cases/{case}/summary', [LawyerCaseController::class, 'summary'])->name('lawyer.case.summary');
    Route::get('cases/{case}/top-sheet', [LawyerCaseController::class, 'printTopSheet'])->name('lawyer.case.top_sheet');
    Route::post('cases/{case}/resubmit', [LawyerCaseController::class, 'resubmit'])->name('lawyer.case.resubmit');
    
    Route::get('{case}/edit', [LawyerCaseController::class, 'edit'])->name('lawyer.case.edit');
    Route::put('{case}', [LawyerCaseController::class, 'update'])->name('lawyer.case.update');
    
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::prefix('admin')->name('admin.')->middleware(['auth', 'checkUserType:admin'])->group(function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('departments', DepartmentController::class)->except(['show', 'create']);
    Route::resource('courts', CourtController::class)->except(['show', 'create']);
    Route::resource('users', UserController::class)->except(['show']);

});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'checkUserType:admin,staff'])->group(function () {
    Route::prefix('tracking')->name('tracking.')->group(function () {
        Route::get('register-report', [RegistrarTrackingController::class, 'registerReport'])->name('register-report');
        Route::get('register-report/pdf', [RegistrarTrackingController::class, 'registerReportPdf'])->name('register-report.pdf');
        Route::get('filing/print', [FilingController::class, 'printIndex'])->name('filing.print-index');
        Route::get('filing/print/{case}', [FilingController::class, 'printLabel'])->name('filing.print-label');
        Route::get('filing/print/{case}/pdf', [FilingController::class, 'printLabelPdf'])->name('filing.print-label-pdf');
        Route::get('filing/print/{case}/tspl', [FilingController::class, 'printLabelTspl'])->name('filing.print-label-tspl');
        Route::post('filing/print/{case}/direct', [FilingController::class, 'printLabelDirect'])->name('filing.print-label-direct');

        Route::middleware('ensureDepartment:Office Assistant,Assistant Registrar Office')->group(function () {
            Route::get('court/dispatch', [CourtDispatchController::class, 'dispatchIndex'])->name('court.dispatch.index');
            Route::post('court/dispatch', [CourtDispatchController::class, 'dispatchStore'])->name('court.dispatch.store');
            Route::get('court/return', [CourtDispatchController::class, 'returnIndex'])->name('court.return.index');
            Route::post('court/return', [CourtDispatchController::class, 'returnStore'])->name('court.return.store');
            Route::get('court/batches/{batch}/pdf', [CourtDispatchController::class, 'batchPdf'])->name('court.batch.pdf');
        });

        Route::middleware('ensureDepartment:Filing Section')->group(function () {
            Route::get('filing', [FilingController::class, 'index'])->name('filing.index');
            Route::get('filing/scan-temp', [FilingController::class, 'showTempScan'])->name('filing.scan-temp');
            Route::post('filing/scan-temp', [FilingController::class, 'receiveTemp'])->name('filing.receive-temp');
            Route::post('filing/return-to-lawyer', [FilingController::class, 'returnToLawyer'])->name('filing.return-to-lawyer');
            Route::post('filing/lawyer-lookup', [FilingController::class, 'lookupLawyerMember'])->name('filing.lawyer-lookup');
            Route::get('filing/direct-create', [FilingController::class, 'showDirectCreate'])->name('filing.direct-create');
            Route::post('filing/direct-create', [FilingController::class, 'storeDirectCreate'])->name('filing.store-direct');
            Route::get('filing/cases/{case}', [FilingController::class, 'show'])->name('filing.show');
        });

        Route::middleware('ensureDepartment:Affidavit Section,Requisite Section,Put-Up Section,Typing Section,Compare Section,Superintendent,Ready Table,Record Room')->group(function () {
            Route::get('section/receive', [SectionReceiveController::class, 'show'])->name('section.receive');
            Route::post('section/receive', [SectionReceiveController::class, 'receive'])->name('section.receive.store');
        });

        Route::middleware('ensureDepartment:Assistant Registrar Office')->group(function () {
            Route::get('lookup', [RegistrarTrackingController::class, 'lookup'])->name('lookup');
            Route::get('lookup/suggest', [RegistrarTrackingController::class, 'lookupSuggest'])->name('lookup.suggest');
            Route::get('cases/{case}/timeline', [RegistrarTrackingController::class, 'timeline'])->name('timeline');
            Route::post('cases/{case}/override-receive', [RegistrarTrackingController::class, 'overrideReceive'])->name('override');
        });
    });
});

require __DIR__.'/auth.php';
