<?php

namespace FBNKCMaster\xTenant\Models;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class XTenantSetting extends Authenticatable
{

    protected $guard = 'superadmin';

    protected $fillable = [
        'super_admin_subdomain',
        'email',
        'password',
        'name',
        'allow_www',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'allow_www' => 'boolean'
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public static function isRootDomain(Request $request)
    {
        $host = $request->getHost();
        $xTenantSettings = self::getSettings();
        
        if ($xTenantSettings->allow_www && strpos($host, 'www.') === 0) {
            $host = str_replace('www.', '', $host);
        }

        $rootDomain = self::getRootDomain();
        
        return $host == $rootDomain;
    }

    public static function isSuperAdmin(Request $request)
    {
        list($subdomain) = explode('.', $request->getHost(), 2);

        return self::where('super_admin_subdomain', $subdomain)->exists();
    }

    public static function getSettings()
    {
        return self::first();
    }

    public static function getRootDomain()
    {
        $parts = explode('.', request()->getHost());

        return implode('.', array_slice($parts, -2, 2));
    }

    /* public static function getDomain()
    {
        $xTenantSettings = self::getSettings();
        $reservedSubdomains[] = ($xTenantSettings->super_admin_subdomain ?? 'xtenant') . '.';
        if ($xTenantSettings->allow_www) {
            $reservedSubdomains[] = 'www.';
        }
        $registredSubdomains = array_map(function ($subdomain) { return $subdomain . '.'; }, Tenant::getRegistredSubdomains()->toArray());
        $subdomains = array_merge($reservedSubdomains, $registredSubdomains);
        return str_replace($subdomains, '', request()->getHost()); 
    } */

    public static function getSuperAdminSubdomain()
    {
        $xTenantSettings = self::getSettings();
        return $xTenantSettings->super_admin_subdomain ?? 'xtenant';
    }
}
