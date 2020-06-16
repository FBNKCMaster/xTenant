<?php

namespace FBNKCMaster\xTenant\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;

use Auth;
use FBNKCMaster\xTenant\Models\XTenantSetting;
use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Helpers\XTenantHelper;


class SuperAdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:superadmin');
    }

    public function index()
    {
        return view('xtenant::index');
    }

    public function about()
    {
        return view('xtenant::about');
    }

    public function commands()
    {
        return view('xtenant::commands');
    }

    public function installation()
    {
        return view('xtenant::installation');
    }

    public function documentation()
    {
        return view('xtenant::documentation');
    }
    
    public function dashboard()
    {
        return view('xtenant::dashboard');
    }

    public function settings()
    {
        return view('xtenant::settings', [ 'xtenant_settings' => XTenantSetting::getSettings() ]);
    }

    public function console()
    {
        return view('xtenant::console');
    }

    public function create()
    {
        return view('xtenant::create', [ 'xtenant_settings' => XTenantSetting::getSettings() ]);
    }

    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);

        return view('xtenant::edit', [ 'tenant' => $tenant ]);
    }

    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        if ($tenant) {
            $messageBag = new MessageBag;
            $messageBag = XTenantHelper::destroyTenant($tenant, 'backup', null, $messageBag);
        }
        return redirect('/')->withErrors($messageBag ?? []);
    }

    public function update_settings(Request $request)
    {
        $xTenantSettings = XTenantSetting::first();

        $request->validate([
            'super_admin_subdomain' => ['string', 'max:255'],
            'allow_www' => ['boolean'],
            'email' => ['string', 'email', 'max:255'],
            'current_password' => ['string', 'min:8', function ($attribute, $value, $fail) {
                if (!Hash::check($value, Auth::user()->password)) {
                    return $fail(__('The current password is incorrect.'));
                }
            }],
            'password' => ['string', 'min:8', 'confirmed'],
            'name' => ['string', 'max:255'],
            'profile' => ['image', 'mimes:jpg,jpeg'],
        ]);
        
        if ($request->filled('super_admin_subdomain') && $xTenantSettings->super_admin_subdomain != $request->super_admin_subdomain) {
            $currentSuperAdminSubdomain = $xTenantSettings->super_admin_subdomain;
            $newSuperAdminSubdomain = $request->super_admin_subdomain;
            $newUrl = $request->getScheme() . '://' . str_replace($currentSuperAdminSubdomain, $newSuperAdminSubdomain, $request->getHost()) . '/settings';
        }

        $bUpdated = $xTenantSettings->update($request->all());

        if ($request->hasFile('profile')) {
            $request->profile->storeAs('superadmin', 'profile.jpeg');
        }

        return isset($newUrl) && $bUpdated ? redirect($newUrl) : redirect()->back();
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => ['image', 'mimes:jpg,jpeg'],
            'subdomain' => ['string', 'max:255', 'unique:tenants'],
            'name' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
            'migrations' => ['boolean'],
            'seeds' => ['boolean'],
            'directory' => ['boolean'],
            'status' => ['boolean'],
        ]);

        $requestAll = $request->all();
        $database = XTenantHelper::createDatabase($request->subdomain);
        
        if ($database) {
            
            $requestAll['database'] = $database;
            $newTenant = Tenant::create($requestAll);

            if ($request->hasFile('image')) {
                $request->image->storeAs($newTenant->subdomain, 'image.jpeg');
            }
            
            if ($newTenant) {
                $messageBag = new MessageBag;
                if ($request->filled('migrations') && $request->migrations == 1) {
                    $messageBag = XTenantHelper::runMigrations($request->subdomain, $newTenant->database ?? null, 'default', null, $messageBag);
                }
                if ($request->filled('seeds') && $request->seeds == 1) {
                    $messageBag = XTenantHelper::runSeeds($request->subdomain, $newTenant->database ?? null, null, $messageBag);
                }
                if ($request->filled('directory') && $request->directory == 1) {
                    $messageBag = XTenantHelper::createDirectory($request->subdomain, 'default', null, $messageBag);
                    $messageBag = XTenantHelper::createSymlink($request->subdomain, null, $messageBag);
                }
            }

        }
        
        return isset($newTenant) ? redirect('/tenants/' . $newTenant->id . '/edit')->withErrors($messageBag ?? []) : redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'image' => ['image', 'mimes:jpg,jpeg'],
            'subdomain' => ['string', 'max:255', 'unique:tenants'],
            'name' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
            'migrations' => ['boolean'],
            'seeds' => ['boolean'],
            'directory' => ['boolean'],
            'status' => ['boolean'],
        ]);

        $tenant = Tenant::findOrFail($id);
        $tenant->update($request->all());

        if ($request->hasFile('image')) {
            $request->image->storeAs($tenant->subdomain, 'image.jpeg');
        }

        $messageBag = new MessageBag;
        if ($request->filled('migrations')) {
            $migrationType = $request->migrations == 1 ? 'reset' : 'fresh';
            $messageBag = XTenantHelper::runMigrations($tenant->subdomain, $tenant->database ?? null, $migrationType, null, $messageBag);
        }
        if ($request->filled('seeds')) {
            $seedType = $request->seeds == 1 ? 'default' : 'fresh';
            $messageBag = XTenantHelper::runSeeds($tenant->subdomain, $tenant->database ?? null, null, $messageBag);
        }
        if ($request->filled('directory')) {
            if ($request->directory == 1) {
                $messageBag = XTenantHelper::createDirectory($tenant->subdomain, 'default', null, $messageBag);
                $messageBag = XTenantHelper::createSymlink($tenant->subdomain, null, $messageBag);
            } else if ($request->directory == 2) {
                if (!XTenantHelper::backupDir($tenant->subdomain)) {
                    $messageBag->add('directory', 'An error occurred. Could not backup directory.');
                }
            } else if ($request->directory == 3) {
                if (!XTenantHelper::removeDir($tenant->subdomain)) {
                    $messageBag->add('directory', 'An error occurred. Could not remove directory.');
                }
            }
        }
    
        return redirect()->back()->withErrors($messageBag ?? []);
    }

}
