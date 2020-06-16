<?php

use FBNKCMaster\xTenant\Models\XTenantSetting;
//use FBNKCMaster\xTenant\Controllers\SuperAdminController;

$domain = XTenantSetting::getDomain();
$superAdminSubdomain = XTenantSetting::getSuperAdminSubdomain();
$controllersNamespace = 'FBNKCMaster\xTenant\Controllers\\';

Route::domain($superAdminSubdomain . '.' . $domain)->group(function () use ($controllersNamespace) {

    /* Route::get('{any}', function () {
        return 'This SuperAdmin Area';
    })->where('any', '.*'); */

    /* Route::get('/', function () {
        return view('xtenant::dashboard');
        //return view('xtenant::howto');
    }); */

    // Authentication Routes...
    //$this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
    //$this->post('login', 'Auth\LoginController@login');
    //$this->post('logout', 'Auth\LoginController@logout')->name('logout');
    Route::group(['middleware' => ['web']], function () use ($controllersNamespace) {
        
        Route::get('/login', $controllersNamespace . 'LoginController@showLoginForm')->name('login');
        Route::post('/login', $controllersNamespace . 'LoginController@login');
        Route::post('/logout', $controllersNamespace . 'LoginController@logout')->name('logout');
        Route::get('/register', function () { return redirect('/'); })->name('register');

    /* });

    Route::group(['middleware' => ['web', 'auth:superadmin']], function () use ($controllersNamespace) { */
        //routes should go here
        Route::get('/dashboard', $controllersNamespace . 'SuperAdminController@dashboard');
        Route::get('/settings', $controllersNamespace . 'SuperAdminController@settings');
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
    });
    

});