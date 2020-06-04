<?php

use FBNKCMaster\xTenant\Models\XTenantParam;
use Illuminate\Http\RedirectResponse;

$domain = XTenantParam::getDomain();
$superAdminSubdomain = XTenantParam::getSuperAdminSubdomain();

Route::domain('{subdomain}.' . $domain)->group(function () use ($superAdminSubdomain) {

    // Redirect static assets
    Route::get('{any}', function ($subdomain, $any) {
        $extensions = [
            'jpg', 'jpeg', 'png', 'gif', 'svg',
            'mp4', 'mov', 'webm', 'avi', 'mkv',
            'css',
            'js',
            'pdf',
        ];
        if (preg_match('(' . implode('|', $extensions) .')', $any)) {
            $host = request()->getHost();
            $newPath = str_replace($host, $host . '/' . $subdomain . '/public', url()->current());
            
            return new RedirectResponse($newPath, 302, [
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }
    
    })->where([
        'subdomain' => '^((?!' . $superAdminSubdomain . ').)*$',
        'any'       => '^(\w+/){1,2}\w+\.\w+$'
    ]);

});