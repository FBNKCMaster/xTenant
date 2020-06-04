<?php

namespace FBNKCMaster\xTenant\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Http\Request;

class XTenantParam extends Model
{
    protected $fillable = [
        'super_admin_subdomain',
        'allow_www',
    ];

    protected $casts = [
        'allow_www' => 'boolean'
    ];

    public static function isSuperAdmin(Request $request)
    {
        list($subdomain) = explode('.', $request->getHost(), 2);

        return self::where('super_admin_subdomain', $subdomain)->exists();
    }

    public static function getParams()
    {
        return self::first();
    }

    public static function getDomain()
    {
        $xTenantParams = self::getParams();
        $reservedSubdomains[] = ($xTenantParams->super_admin_subdomain ?? 'xtenant') . '.';
        if ($xTenantParams->allow_www) {
            $reservedSubdomains[] = 'www.';
        }
        $registredSubdomains = array_map(function ($subdomain) { return $subdomain . '.'; }, Tenant::getRegistredSubdomains()->toArray());
        $subdomains = array_merge($reservedSubdomains, $registredSubdomains);
        return str_replace($subdomains, '', request()->getHost()); 
    }

    public static function getSuperAdminSubdomain()
    {
        $xTenantParams = self::getParams();
        return $xTenantParams->super_admin_subdomain ?? 'xtenant';
    }
}
