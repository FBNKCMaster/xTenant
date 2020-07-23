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
            'migrations' => ['numeric'],
            'seeds' => ['numeric'],
            'directory' => ['numeric'],
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
                    $messageBag = XTenantHelper::runSeeds($request->subdomain, $newTenant->database ?? null, null, null, $messageBag);
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
            'migrations' => ['numeric'],
            'seeds' => ['numeric'],
            'directory' => ['numeric'],
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

    public function cmd(Request $request)
    {
        $cmd = !$request->filled('cmd') || $request->input('cmd') == 'help' ? 'list' : 'xtenant:'. $request->input('cmd');
        $formatedOutput = [];
        $color = 'green';
        if ($cmd == 'xtenant:web_sess_clear') {
            session()->regenerate();
            $output = 'Session cleared';
        } else if ($cmd == 'xtenant:setup') {
            $output = 'This is a very risky command. You should not run it here';
            $color = 'red';
        } else {
            try {
                if ($cmd == 'list') {
                    $options = [];
                } else {
                    $options = [
                        '--web_sess_uid' => session()->getId(),
                        '--q_hash' => $request->filled('q_hash') ? $request->input('q_hash') : null,
                        '--web_input' => $request->filled('web_input') ? $request->input('web_input') : null,
                    ];
                }
                
                \Artisan::call($cmd, $options);
                $output = \Artisan::output();
                session()->regenerate();
            } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
                if (!$request->filled('q_hash')) {
                    $output = \Artisan::output();
                }
                $outputMessage = unserialize($e->getMessage());
                $formatedMessage = [
                    'action' => 'ask',
                    'color' => $outputMessage['color'] ?? 'orange',
                    'text' => ' > ' . $outputMessage['question'],
                    'q_hash' => $outputMessage['q_hash'],
                    'choices' => $outputMessage['choices'] ?? null,
                    'default' => $outputMessage['default'] ?? null,
                    'placeholder' => isset($outputMessage['choices']) ? ($outputMessage['default'] ?? null) : $outputMessage['question']
                ];
            } catch (\Exception $th) {
                $output = $th->getMessage();
                $color = 'red';
            }
        }
        
        if (isset($output) && !empty($output)) {
            $lines = explode("\n", $output);
            foreach ($lines as  $line) {
                $line = preg_replace('/<\/*warning>/', '', $line, -1, $count);
                $formatedOutput[] = [
                    'action' => 'display',
                    'text' => $line,
                    'color' => $count > 1 ? 'yellow' : $color
                ];
            }
        }

        if (isset($formatedMessage)) {
            $formatedOutput[] = $formatedMessage;
        }

        return $formatedOutput;
    }

}
