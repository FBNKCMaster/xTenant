<?php

use FBNKCMaster\xTenant\Models\XTenantParam;

$domain = XTenantParam::getDomain();
$superAdminSubdomain = XTenantParam::getSuperAdminSubdomain();

Route::domain($superAdminSubdomain . '.' . $domain)->group(function () {

    Route::get('{any}', function () {
        return 'This SuperAdmin Area';
    })->where('any', '.*');

});