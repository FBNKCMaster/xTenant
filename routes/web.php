<?php

use FBNKCMaster\xTenant\Models\XTenantSetting;
//use FBNKCMaster\xTenant\Controllers\SuperAdminController;

$domain = XTenantSetting::getDomain();
$superAdminSubdomain = XTenantSetting::getSuperAdminSubdomain();
$controllersNamespace = 'FBNKCMaster\xTenant\Controllers\\';

Route::domain($superAdminSubdomain . '.' . $domain)->group(function () use ($controllersNamespace) {

    Route::group(['middleware' => ['web']], function () use ($controllersNamespace) {
        
        Route::get('/login', $controllersNamespace . 'LoginController@showLoginForm')->name('login');
        Route::post('/login', $controllersNamespace . 'LoginController@login');
        Route::post('/logout', $controllersNamespace . 'LoginController@logout')->name('logout');
        Route::get('/register', function () { return redirect('/'); })->name('register');

        Route::get('/dashboard', $controllersNamespace . 'SuperAdminController@dashboard');
        Route::get('/settings', $controllersNamespace . 'SuperAdminController@settings');
        Route::get('/console', $controllersNamespace . 'SuperAdminController@console');
        Route::patch('/settings', $controllersNamespace . 'SuperAdminController@update_settings')->name('settings');
        Route::get('/console', $controllersNamespace . 'SuperAdminController@console');
        Route::get('/tenants/create', $controllersNamespace . 'SuperAdminController@create');
        Route::post('/tenants', $controllersNamespace . 'SuperAdminController@store')->name('create');
        Route::get('/tenants/{id}/edit', $controllersNamespace . 'SuperAdminController@edit');
        Route::patch('/tenants/{id}', $controllersNamespace . 'SuperAdminController@update')->name('update');
        Route::delete('/tenants/{id}', $controllersNamespace . 'SuperAdminController@destroy')->name('delete');
    
        Route::get('/', $controllersNamespace . 'SuperAdminController@index');
        Route::get('/about', $controllersNamespace . 'SuperAdminController@about');
        Route::get('/commands', $controllersNamespace . 'SuperAdminController@commands');
        Route::get('/installation', $controllersNamespace . 'SuperAdminController@installation');
        Route::get('/documentation', $controllersNamespace . 'SuperAdminController@documentation');
        
        Route::post('/cmd', $controllersNamespace . 'SuperAdminController@cmd');
        
    });
    
    
});

// This route works as proxy for the js file
Route::get($superAdminSubdomain . '/public/js/app.js', function () {
    return response(file_get_contents(__DIR__ . '/../public/js/app.js'))
            ->header('Content-Type', 'application/x-javascript');
});