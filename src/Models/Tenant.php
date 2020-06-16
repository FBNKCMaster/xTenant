<?php

namespace FBNKCMaster\xTenant\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Http\Request;

class Tenant extends Model
{
    protected $fillable = [
        'subdomain',
        'name',
        'database',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function isEnabled()
    {
        return $this->status;
    }

    public static function findTenant(Request $request, $allowWww = null)
    {
        $host = $request->getHost();

        if (!is_null($allowWww) && $allowWww == true && strpos($host, 'www.') === 0) {
            $host = str_replace('www.', '', $host);
        }

        list($subdomain) = explode('.', $host, 2);

        return [
            'subdomain' => $subdomain,
            'tenant' => self::where('subdomain', $subdomain)->first(),
        ];
    }

    public static function getAllTenants()
    {
        return self::get();
    }

    public static function getRegistredSubdomains()
    {
        return self::get('subdomain')->pluck('subdomain');
    }
}
