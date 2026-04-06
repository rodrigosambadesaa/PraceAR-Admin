<?php

use App\Http\Controllers\AdminAjaxController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RootController;
use App\Http\Controllers\UnityController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RootController::class, 'show'])->name('root.show');
Route::post('/', [RootController::class, 'submit'])->name('root.submit');

Route::get('/index.php', [RootController::class, 'show']);
Route::post('/index.php', [RootController::class, 'submit']);
Route::get('/login', [RootController::class, 'show']);
Route::post('/login', [RootController::class, 'submit']);
Route::get('/login.php', [RootController::class, 'show']);
Route::post('/login.php', [RootController::class, 'submit']);
Route::get('/admin/index.php', [RootController::class, 'show']);
Route::post('/admin/index.php', [RootController::class, 'submit']);

Route::get('/admin/market_sections.php', [AdminController::class, 'marketSections'])->name('admin.market_sections');
Route::get('/admin/change_password.php', [AdminController::class, 'changePasswordForm'])->name('admin.change_password');
Route::post('/admin/change_password.php', [AdminController::class, 'changePasswordUpdate']);
Route::get('/admin/edit.php', [AdminController::class, 'editForm'])->name('admin.edit');
Route::post('/admin/edit.php', [AdminController::class, 'editUpdate']);
Route::get('/admin/edit_translations.php', [AdminController::class, 'translationsForm'])->name('admin.language');
Route::post('/admin/edit_translations.php', [AdminController::class, 'translationsUpdate']);
Route::get('/admin/logout.php', [AdminController::class, 'logout'])->name('admin.logout');

Route::post('/admin/ajax_quick_edit.php', [AdminAjaxController::class, 'quickEditForm'])->name('admin.ajax.quick_edit_form');
Route::post('/admin/ajax_quick_edit_save.php', [AdminAjaxController::class, 'quickEditSave'])->name('admin.ajax.quick_edit_save');
Route::get('/admin/ajax_sugerencias.php', [AdminAjaxController::class, 'suggestions'])->name('admin.ajax.suggestions');
Route::post('/ajax/generate_password.php', [AdminAjaxController::class, 'generatePassword'])->name('admin.ajax.generate_password');
Route::post('/helpers/verify_malicious_photo.php', [AdminAjaxController::class, 'verifyMaliciousPhoto'])->name('admin.ajax.verify_malicious_photo');

Route::prefix('/unity')->group(function (): void {
    Route::match(['get', 'post'], '/connection.php', [UnityController::class, 'connection'])->name('unity.connection');
    Route::match(['get', 'post'], '/get_all_puestos.php', [UnityController::class, 'allStalls'])->name('unity.all_stalls');
    Route::match(['get', 'post'], '/get_info_puesto.php', [UnityController::class, 'stallInfo'])->name('unity.stall_info');

    foreach (array_keys((array) config('unity.nave_endpoints', [])) as $endpoint) {
        Route::match(['get', 'post'], '/' . $endpoint . '.php', [UnityController::class, 'naveInfo'])
            ->defaults('endpoint', $endpoint)
            ->name('unity.' . $endpoint);
    }
});
