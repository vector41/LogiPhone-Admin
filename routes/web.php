<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProccessController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\API\CallController;
use App\Http\Controllers\API\SmsController;
use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

WebSocketsRouter::webSocket('/app/{appKey}', \App\WebSockets\AndroidWebSocketsHandler::class);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// No need
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Vue : Laravel => 1:1
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return Inertia::render('Dashboard');
    });
    Route::get('/companys', function () {
        return Inertia::render('CompanyContent');
    })->name('companies');
    Route::get('/company-regist', function () {
        return Inertia::render('CompanyRegister');
    })->name('companyreg');
    Route::get('/employers', function () {
        return Inertia::render('EmployeerContent');
    })->name('emplyeers');
    Route::get('/employeer-regist', function () {
        return Inertia::render('EmployeerRegister');
    })->name('addemplyeer');
    Route::get('/calls', function () {
        return Inertia::render('CallContent');
    })->name('callhistories');
    Route::get('/sms', function () {
        return Inertia::render('SMSPage');
    })->name('sendsms');
    Route::get('/company-info', function () {
        return Inertia::render('CompanyInfos');
    })->name('companyinfo');
    Route::get('/company-employees', function () {
        return Inertia::render('CompanyEmployeerContent');
    })->name('members');
    Route::get('/phoneBook-setting', function () {
        return Inertia::render('PhoneBookContent');
    })->name('phoneBook');
});




// Controller function connection.


// No need ?

// Route::get('/users', [AdminController::class, 'getAllUsers']);
// Route::post('/removeuser', [AdminController::class, 'removeUser']);
// Route::post('/permuser', [AdminController::class, 'changePermUser']);
// Route::get('/get_setting', [AdminController::class, 'getSettings']);
// Route::post('/update_setting', [AdminController::class, 'changeSettings']);

//取引先関連関連 : Company branch
Route::get('/suppliers/company/all', [SupplierController::class, 'list']);
Route::get('/suppliers/company/get', [SupplierController::class, 'get']);
Route::post('/suppliers/company/save', [SupplierController::class, 'save']);
Route::post('/suppliers/company/remove', [SupplierController::class, 'destroy']);

Route::get('/suppliers/branch/get', [SupplierController::class, 'getCompanyBranch']);
Route::post('/suppliers/branch/save', [SupplierController::class, 'saveCompanyBranch']);


Route::post('/suppliers/company/savelp', [SupplierController::class, 'saveLP']);

Route::get('/suppliers/employees/all', [EmployeeController::class, 'list']);
Route::get('/suppliers/employees/get', [EmployeeController::class, 'get']);
Route::post('/suppliers/employees/save', [EmployeeController::class, 'save']);
Route::post('/suppliers/employees/remove', [EmployeeController::class, 'destroy']);

Route::post('/suppliers/employees/save_lp', [EmployeeController::class, 'saveLP']);
Route::get('/suppliers/employees/get_from_branch', [EmployeeController::class, 'getEmployeeFromBranch']);
Route::post('/suppliers/employees/update', [EmployeeController::class, 'updateEmployeer']);

//取引先関連関連 : Company clients
Route::get('/company/all', [CompanyController::class, 'list']);
Route::any('/companies/employees/all', [CompanyController::class, 'employeer_list']);
Route::post('/company/branches', [CompanyController::class, 'getBranches']);
Route::post('/branch/employees', action: [EmployeeController::class, 'getEmployees']);
// Province, City
Route::get('area', [AreaController::class, 'index']);
Route::get('area/zip', [AreaController::class, 'zip']);

// Logiphone company from CompanyBranch
Route::get('suppliers/company/get_from_branch', [SupplierController::class, 'getCompanyFromBranchLP']);
Route::get('suppliers/branch/list_lp', [SupplierController::class, 'getBranchLPList']);

Route::get('/setting', [SettingController::class, 'getCount']);
Route::post('/setting', [SettingController::class, 'saveCount']);

Route::post('/upload/image', [SupplierController::class, 'uploadImage']);
Route::get('/image/get', [SupplierController::class, 'getFilesFromStorage']);


Route::get('/proccess/ls_branch', [ProccessController::class, 'merge_app_process']);
Route::get('/proccess/lp_branch', [ProccessController::class, 'lp_branch_process']);
Route::get('/proccess/ls_employee', [ProccessController::class, 'ls_employee_process']);
Route::get('/proccess/lp_employee', [ProccessController::class, 'lp_employee_process']);

// call routes
Route::get('/call/getCallHistories', [CallController::class, 'getCallHistories']);
Route::post('/call/searchCallHistories', [CallController::class, 'searchCallHistories']);
Route::post('/call/getCallDetailsOfDay', [CallController::class, 'getCallDetailsOfDay']);
Route::post('/call/getCallDetailsOfPeriod', [CallController::class, 'getCallDetailsOfPeriod']);
Route::post('/call/getSearchCallOfDay', [CallController::class, 'searchCall']);
Route::post('/call/getSearchCallOfPeriod', [CallController::class, 'searchCall']);

// sms routes
Route::get('/sms/getSmsHistories', [SmsController::class, 'getSmsHistories']);
Route::post('/sms/searchSmsHistories', [SmsController::class, 'searchSmsHistories']);
Route::post('/sms/getSMSDetailsOfDay', [SmsController::class, 'getSmsDetails']);
Route::post('/sms/getSMSDetailsOfPeriod', [SmsController::class, 'getSmsDetails']);
Route::post('/sms/searchSMSOfDay', [SmsController::class, 'searchSms']);
Route::post('/sms/searchSMSOfPeriod', [SmsController::class, 'searchSms']);

Route::get('/call/callControl', [CallController::class, 'callControl']);


Route::get('/test', function () {
    return Inertia::render('Test');
});

require __DIR__ . '/auth.php';