<?php

use App\Models\Plan;
use App\Models\User;
use App\Models\AddOn;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\AiModel;
use App\Models\Content;
use App\Models\Setting;
use App\Models\Currency;
use App\Models\Language;
use App\Models\WorkSpace;
use App\Models\Permission;
use App\Models\UserCoupon;
use App\Models\EmailTemplate;
use App\Models\userActiveModule;
use App\Models\EmailTemplateLang;
use Illuminate\Support\Collection;
use App\Models\ContentTemplateLang;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Modules\Taskly\Entities\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Modules\Taskly\Service\projectsTabs;
use Illuminate\Support\Facades\Validator;
use Modules\Taskly\Entities\ProjectEstimation;

if (! function_exists('getMenu')) {
    function getMenu()
    {

        $user     = auth()->user();
        $cacheKey = 'sidebar_menu_' . $user->id;

        $role = $user->roles->first();
        $menu = new \App\Classes\Menu($user);

        if ($role->name === 'super admin') {
            event(new \App\Events\SuperAdminMenuEvent($menu));
        } else {
            event(new \App\Events\CompanyMenuEvent($menu));
        }

        $groupedMenu = collect($menu->menu)
            ->groupBy('category')
            ->toArray();

        $categoryIcon = categoryIcon();


        uksort($groupedMenu, function ($a, $b) use ($categoryIcon) {
            return array_search($a, array_keys($categoryIcon)) <=> array_search($b, array_keys($categoryIcon));
        });

        return generateMenu($groupedMenu, null);
        // return Cache::rememberForever($cacheKey, function () use ($groupedMenu) {
        // });
    }

}

if (! function_exists('generateMenu')) {
    function generateMenu($grouped, $parent = null)
    {
        $html                    = '';
        $companySettings         = getCompanyAllSetting();
        $categoryWiseMenuEnabled = ! empty($companySettings['category_wise_sidemenu']) && $companySettings['category_wise_sidemenu'] === 'on';
        $categoryIcons           = categoryIcon();

        foreach ($grouped as $category => $menuItems) {


            if ($categoryWiseMenuEnabled) {
                $icon = $categoryIcons[$category] ?? 'home';
                $html .= '<li class="dash-item dash-caption">
                            <label>' . e($category) . '</label>
                            <i class="ti ti-' . e($icon) . '"></i>
                          </li>';
            }

            $html .= generateSubMenu($menuItems, $parent);
        }

        return $html;
    }
}

if (! function_exists('generateSubMenu')) {
    function generateSubMenu($menuItems, $parent = null)
    {
        $html = '';

        // Filter and sort the menu items
        $filteredItems = array_filter($menuItems, fn ($item) => $item['parent'] == $parent);
        usort($filteredItems, fn ($a, $b) => $a['order'] <=> $b['order']);

        foreach ($filteredItems as $item) {


            $hasChildren = hasChildren($menuItems, $item['name']);

            $liClass = $item['parent'] === null ? 'dash-item dash-hasmenu' : 'dash-item';

            // Begin list item
            $html .= '<li class="' . e($liClass) . '">';

            // Begin anchor tag
            $route = '#!';

            if (Route::has($item['route'])) {
                $route = route($item['route']);
            }

            $html .= '<a href="' . e($route) . '" class="dash-link">';

            // Add icon and title for parent items
            if ($item['parent'] === null) {
                $html .= '<span class="dash-micon"><i class="ti ti-' . e($item['icon']) . '"></i></span>';
                $html .= '<span class="dash-mtext">';
            }

            $html .= e($item['title']) . '</span>';

            // Add arrow for items with children
            if ($hasChildren) {
                $html .= '<span class="dash-arrow"><i data-feather="chevron-right"></i></span>';
            }

            $html .= '</a>'; // Close anchor tag

            // Recursively generate submenu if item has children
            if ($hasChildren) {
                $html .= '<ul class="dash-submenu">';
                $html .= generateSubMenu($menuItems, $item['name']);
                $html .= '</ul>';
            }

            if ($item['name'] == 'projects' && $item['depend_on'] && (request()->is('project') || request()->is('project/*'))) {
                $projectTabs = new projectsTabs();
                $html .= $projectTabs->render();
            }

            $html .= '</li>'; // Close list item
        }

        return $html;
    }
}

function projectTabItem()
{
    $html = '';

    $all_projects = Project::all();


    $html .= '<ul class="nav dash-item-tabs" id="myTab" role="tablist">';

    $html .= '<li class="nav-item" role="presentation">
                            <a class="nav-link" id="all-project" href="#all-project" role="tab" style="background-color:#eee;" data-bs-toggle="tab" data-bs-placement="top" title="all project">
                                <i class="fa-solid fa-list"></i>
                            </a>
                        </li>';


    foreach ($all_projects->unique('status_data.name') as $project) {

        if (isset($project->status_data->name)) {

            $status_css_name  = preg_replace('/[^a-zA-Z0-9_]/', '', strtolower(str_replace(' ', '_', $project->status_data->name)));
            $status_name      = substr($project->status_data->name, 0, 2); // Get shortened status name
            $status_full_name = $project->status_data->name;
            $background_color = $project->status_data->background_color ?? '';
            $font_color       = $project->status_data->font_color ?? '';
            $project_count    = $all_projects->where('status_data.name', $status_full_name)->count();



            $html .= '<li class="nav-item" role="presentation">';

            $html .= '<a class="nav-link" id="contact-tab-' . $status_css_name . '" '
                . 'href="#' . $status_css_name . '" '
                . 'role="tab" '
                . 'style="background-color:' . $background_color . '; color:' . $font_color . ';" '
                . 'data-bs-toggle="tab" '
                . 'data-bs-placement="top" '
                . 'title="' . $status_full_name . '">'
                . $status_name
                . '<span>' . $project_count . '</span>'
                . '</a>';

            $html .= '</li>';


        }

    }

    $html .= '</li>';
    $html .= '</ul>'; // END Tab item button

    return $html;
}

function projectList()
{
    $html = '';

    $html .= '<div class="tab-content" id="myTabContent">';

    $all_projects    = Project::with('status_data')->get();
    $groupedProjects = $all_projects->groupBy('status_data.name');


    foreach ($groupedProjects as $statusName => $projects) {

        $html .= '<div class="tab-pane fade" id="' . strtolower($statusName) . '" role="tabpanel">';

        $html .= '<ul class="tab-submenu">';

        foreach ($projects as $project) {

            $statusName = $project->status_data->name ?? 'NA';

            $backgrounColor = $project->status_data->background_color ?? '';
            $shortName      = substr($statusName, 0, 2);
            $projectUrl     = route('project.show', [$project->id]);

            $html .= '<li class="tab-item">
                <a class="tab-link" href="' . $projectUrl . '">
                    <span style="background-color: ' . $backgrounColor . ';">' . $shortName . '</span>
                    ' . $project->name . '
                </a>
            </li>';
        }

        $html .= ' </ul>';

        $html .= '</div>';
    }

    return $html;
}

if (! function_exists('categoryIcon')) {
    function categoryIcon()
    {
        $categoryIcon = [
            'General'       => 'indent-increase',
            'Addon Manager' => 'apps',
            'Finance'       => 'chart-dots',
            'HR'            => 'users',
            'Sales'         => 'businessplan',
            'eCommerce'     => 'shopping-cart',
            'Education'     => 'school',
            'Operations'    => 'stack-2',
            'Productivity'  => 'list-check',
            'Communication' => 'messages',
            'Medical'       => 'ambulance',
            'Vehicle'       => 'bike',
            'AI'            => 'brand-gitlab',
            'Settings'      => 'adjustments-horizontal',
        ];

        return $categoryIcon;
    }
}

if (! function_exists('hasChildren')) {
    function hasChildren($menuItems, $name)
    {
        foreach ($menuItems as $item) {
            if ($item['parent'] === $name) {
                return true;
            }
        }
        return false;
    }
}


if (! function_exists('getSettingMenu')) {
    function getSettingMenu()
    {
        $user = auth()->user();
        $role = $user->roles->first();
        $menu = new \App\Classes\Menu($user);
        if ($role->name == 'super admin') {
            event(new \App\Events\SuperAdminSettingMenuEvent($menu));
        } else {
            event(new \App\Events\CompanySettingMenuEvent($menu));
        }
        return generateSettingMenu($menu->menu);
    }
}


if (! function_exists('generateSettingMenu')) {
    function generateSettingMenu($menuItems)
    {
        usort($menuItems, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        $html = '';
        foreach ($menuItems as $menu) {
            $method = isset($menu['method']) ? $menu['method'] : null;
            $html .= '<a href="#' . $menu['navigation'] . '" data-module="' . $menu['module'] . '" data-method="' . $method . '"  class="list-group-item list-group-item-action setting-menu-nav">' . $menu['title'] . '<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>';
        }
        return $html;
    }
}
if (! function_exists('getSettings')) {
    function getSettings()
    {
        $user = auth()->user();
        $role = $user->roles->first();
        if ($role->name == 'super admin') {
            $settings = getAdminAllSetting();
            $html     = new \App\Classes\Setting($user, $settings);
            event(new \App\Events\SuperAdminSettingEvent($html));
        } else {
            $settings = getCompanyAllSetting();
            $html     = new \App\Classes\Setting($user, $settings);
            event(new \App\Events\CompanySettingEvent($html));
        }
        return generateSettings($html->html);
    }
}
if (! function_exists('generateSettings')) {
    function generateSettings($settingItems)
    {
        usort($settingItems, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        $html = '';
        foreach ($settingItems as $setting) {
            $html .= $setting['html'];
        }
        return $html;
    }
}

if (! function_exists('getAdminAllSetting')) {
    function getAdminAllSetting()
    {
        // Laravel cache
        return Cache::rememberForever('admin_settings', function () {
            $super_admin = User::where('type', 'super admin')->first();
            $settings    = [];
            if ($super_admin) {
                $settings = Setting::where('created_by', $super_admin->id)->where('workspace', $super_admin->active_workspace)->pluck('value', 'key')->toArray();
            }

            return $settings;
        });
    }
}

if (! function_exists('getCompanyAllSetting')) {
    function getCompanyAllSetting($user_id = null, $workspace = null)
    {
        if (! empty($user_id)) {
            $user = User::find($user_id);
        } else {
            $user = auth()->user();
        }

        $workspace = $workspace ?? $user->active_workspace;

        // // Check if the user is not 'company' or 'super admin' and find the creator
        if (! in_array($user->type, ['company', 'super admin'])) {
            $user = User::find($user->created_by);
        }

        if (! empty($user)) {
            $key = 'company_settings_' . $workspace . '_' . $user->id;
            return Cache::rememberForever($key, function () use ($user, $workspace) {
                $settings = [];
                $settings = Setting::where('created_by', $user->id)->where('workspace', $workspace)->pluck('value', 'key')->toArray();
                return $settings;
            });
        }

        return [];
    }
}

if (! function_exists('admin_setting')) {
    function admin_setting($key)
    {
        if ($key) {
            $admin_settings = getAdminAllSetting();
            $setting        = (array_key_exists($key, $admin_settings)) ? $admin_settings[$key] : null;
            return $setting;
        }
    }
}

if (! function_exists('company_setting')) {
    function company_setting($key, $user_id = null, $workspace = null)
    {
        if ($key) {
            $company_settings = getCompanyAllSetting($user_id, $workspace);
            $setting          = null;
            if (! empty($company_settings)) {
                $setting = (array_key_exists($key, $company_settings)) ? $company_settings[$key] : null;
            }
            return $setting;
        }
    }
}

if (! function_exists('AdminSettingCacheForget')) {
    function AdminSettingCacheForget()
    {
        try {
            Cache::forget('admin_settings');
        } catch (\Exception $e) {
            \Log::error('AdminSettingCacheForget :' . $e->getMessage());
        }
    }
}

if (! function_exists('comapnySettingCacheForget')) {
    function comapnySettingCacheForget($user_id = null, $workspace = null)
    {
        try {
            if (empty($user_id)) {
                $user_id = creatorId();
            }
            if (empty($workspace)) {
                $workspace = getActiveWorkSpace();
            }
            $key = 'company_settings_' . $workspace . '_' . $user_id;
            Cache::forget($key);
        } catch (\Exception $e) {
            \Log::error('comapnySettingCacheForget :' . $e->getMessage());
        }
    }
}

if (! function_exists('sideMenuCacheForget')) {
    function sideMenuCacheForget($type = null, $user_id = null)
    {
        if ($type == 'all') {
            Cache::flush();
        }

        if (! empty($user_id)) {
            $user = User::find($user_id);
        } else {
            $user = auth()->user();
        }

        if ($user->type == 'company') {
            $users = User::select('id')->where('created_by', $user->id)->pluck('id');
            foreach ($users as $id) {
                try {
                    $key = 'sidebar_menu_' . $id;
                    Cache::forget($key);
                } catch (\Exception $e) {
                    \Log::error('comapnySettingCacheForget :' . $e->getMessage());
                }
            }
            try {
                $key = 'sidebar_menu_' . $user->id;
                Cache::forget($key);
            } catch (\Exception $e) {
                \Log::error('comapnySettingCacheForget :' . $e->getMessage());
            }
            return true;
        }

        try {
            $key = 'sidebar_menu_' . $user->id;
            Cache::forget($key);
        } catch (\Exception $e) {
            \Log::error('comapnySettingCacheForget :' . $e->getMessage());
        }

        return true;
    }
}

if (! function_exists('getActiveWorkSpace')) {
    function getActiveWorkSpace($user_id = null)
    {
        if (! empty($user_id)) {
            $user = User::find($user_id);
        } else {
            $user = auth()->user();
        }

        if ($user) {
            if (! empty($user->active_workspace)) {
                return $user->active_workspace;
            } else {
                if ($user->type == 'super admin') {
                    return 0;
                } else {
                    static $WorkSpace = null;
                    if ($WorkSpace == null) {
                        $workspace = WorkSpace::where('created_by', $user->id)->first();
                    }
                    return $workspace->id;
                }
            }
        }
    }
}

if (! function_exists('getWorkspace')) {
    function getWorkspace()
    {
        $data = [];
        if (Auth::check()) {
            static $users = null;
            if ($users == null) {
                $users = User::where('email', Auth::user()->email)->get();
            }
            static $WorkSpace = null;
            if ($WorkSpace == null) {
                $WorkSpace = WorkSpace::whereIn('id', $users->pluck('workspace_id')->toArray())->orWhereIn('created_by', $users->pluck('id')->toArray())->where('is_disable', 1)->get();
            }
            return $WorkSpace;
        } else {
            return $data;
        }
    }
}


if (! function_exists('creatorId')) {
    function creatorId()
    {
        if (Auth::user()->type == 'super admin' || Auth::user()->type == 'company') {
            return Auth::user()->id;
        } else {
            return Auth::user()->created_by;
        }
    }
}


if (! function_exists('getModuleList')) {
    function getModuleList()
    {
        $all  = Module::getOrdered();
        $list = [];
        foreach ($all as $module) {
            array_push($list, $module->getName());
        }
        return $list;
    }
}

if (! function_exists('getshowModuleList')) {
    function getshowModuleList()
    {
        $all  = Module::getOrdered();
        $list = [];
        foreach ($all as $module) {
            $path = $module->getPath() . '/module.json';
            $json = json_decode(file_get_contents($path), true);
            if (! isset($json['display']) || $json['display'] == true) {
                array_push($list, $module->getName());
            }
        }
        return $list;
    }
}

if (! function_exists('module_is_active')) {
    function module_is_active($module, $user_id = null)
    {
        if (Module::has($module)) {
            $module = Module::find($module);
            if ($module->isEnabled()) {
                if (Auth::check()) {
                    $user = Auth::user();
                } elseif ($user_id != null) {
                    $user = User::find($user_id);
                }
                if (! empty($user)) {
                    if ($user->type == 'super admin') {
                        return true;
                    } else {
                        $active_module = ActivatedModule($user->id);
                        if ((count($active_module) > 0 && in_array($module->getName(), $active_module))) {
                            return true;
                        }
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
if (! function_exists('ActivatedModule')) {
    function ActivatedModule($user_id = null)
    {
        $activated_module = user::$superadmin_activated_module;

        if ($user_id != null) {
            $user = User::find($user_id);
        } elseif (Auth::check()) {
            $user = Auth::user();
        }
        if (! empty($user)) {
            $available_modules = array_keys(Module::getByStatus(1));

            if ($user->type == 'super admin') {
                $user_active_module = $available_modules;
            } else {
                if ($user->type != 'company') {
                    $user_not_com = User::find($user->created_by);
                    if (! empty($user)) {
                        // Sidebar Performance Changes
                        static $active_module = null;
                        if ($active_module == null) {
                            $active_module = userActiveModule::where('user_id', $user_not_com->id)->pluck('module')->toArray();
                        }
                    }
                } else {
                    static $active_module = null;
                    if ($active_module == null) {
                        $active_module = userActiveModule::where('user_id', $user->id)->pluck('module')->toArray();
                    }
                }

                // Find the common modules
                $commonModules      = array_intersect($active_module, $available_modules);
                $user_active_module = array_unique(array_merge($commonModules, $activated_module));
            }
        }
        return $user_active_module;
    }
}
// module alias name
if (! function_exists('Module_Alias_Name')) {
    function Module_Alias_Name($module_name)
    {
        static $addons = [];
        static $resultArray = [];
        if (count($addons) == 0 && count($resultArray) == 0) {
            $addons      = AddOn::all()->toArray();
            $resultArray = array_reduce($addons, function ($carry, $item) {
                // Check if both "module" and "name" keys exist in the current item
                if (isset($item['module']) && isset($item['name'])) {
                    // Add a new key-value pair to the result array
                    $carry[$item['module']] = $item['name'];
                }
                return $carry;
            }, []);
        }

        $module = Module::find($module_name);
        if (isset($resultArray)) {
            $module_name = array_key_exists($module_name, $resultArray) ? $resultArray[$module_name] : (! empty($module) ? $module->get('alias') : $module_name);
        } elseif (! empty($module)) {
            $module_name = $module->get('alias');
        }
        return $module_name;
    }
}

if (! function_exists('get_permission_by_module')) {
    function get_permission_by_module($mudule)
    {
        $user = Auth::user();

        if ($user->type == 'super admin') {
            $permissions = Permission::where('module', $mudule)->orderBy('name')->get();
        } else {
            $permissions = new Collection();
            foreach ($user->roles as $role) {
                $permissions = $permissions->merge($role->permissions);
            }
            $permissions = $permissions->where('module', $mudule);
        }
        // $permissions = Spatie\Permission\Models\Permission::where('module',$mudule)->orderBy('name')->get();
        return $permissions;
    }
}

if (! function_exists('getActiveLanguage')) {
    function getActiveLanguage()
    {
        if ((Auth::check()) && (! empty(Auth::user()->lang))) {
            return Auth::user()->lang;
        } else {
            $admin_settings = getAdminAllSetting();
            return ! empty($admin_settings['defult_language']) ? $admin_settings['defult_language'] : 'en';
        }
    }
}

if (! function_exists('languages')) {
    function languages()
    {

        try {
            $arrLang = Language::where('status', 1)->get()->pluck('name', 'code')->toArray();
        } catch (\Throwable $th) {
            $arrLang = [
                "ar" => "Arabic",
                "da" => "Danish",
                "de" => "German",
                "en" => "English",
                "es" => "Spanish",
                "fr" => "French",
                "it" => "Italian",
                "ja" => "Japanese",
                "nl" => "Dutch",
                "pl" => "Polish",
                "pt" => "Portuguese",
                "ru" => "Russian",
                "tr" => "Turkish",
            ];
        }
        return $arrLang;
    }
}


// setConfigEmail ( SMTP )
if (! function_exists('SetConfigEmail')) {
    function SetConfigEmail($user_id = null, $workspace_id = null)
    {
        try {

            if (! empty($user_id)) {
                $company_settings = getCompanyAllSetting($user_id);
            } elseif (! empty($user_id) && ! empty($workspace_id)) {
                $company_settings = getCompanyAllSetting($user_id, $workspace_id);
            } else if (Auth::check()) {
                $company_settings = getCompanyAllSetting();
            } else {
                $user_id          = User::where('type', 'super admin')->first()->id;
                $company_settings = getCompanyAllSetting($user_id);
            }

            config(
                [
                    'mail.driver'       => $company_settings['mail_driver'],
                    'mail.host'         => $company_settings['mail_host'],
                    'mail.port'         => $company_settings['mail_port'],
                    'mail.encryption'   => $company_settings['mail_encryption'],
                    'mail.username'     => $company_settings['mail_username'],
                    'mail.password'     => $company_settings['mail_password'],
                    'mail.from.address' => $company_settings['mail_from_address'],
                    'mail.from.name'    => $company_settings['mail_from_name'],
                ],
            );
            return true;
        } catch (\Exception $e) {

            return false;
        }
    }
}

// file upload

if (! function_exists('upload_file')) {
    function upload_file($request, $key_name, $name, $path, $custom_validation = [])
    {
        try {
            $storage_settings = getAdminAllSetting();

            if (isset($storage_settings['storage_setting'])) {
                if ($storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key'      => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret'   => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region'   => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket'   => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root'     => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url'],
                        ],
                    );
                    $max_size = ! empty($storage_settings['wasabi_max_upload_size']) ? $storage_settings['wasabi_max_upload_size'] : '2048';
                    $mimes    = ! empty($storage_settings['wasabi_storage_validation']) ? $storage_settings['wasabi_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                } else if ($storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key'    => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ],
                    );
                    $max_size = ! empty($storage_settings['s3_max_upload_size']) ? $storage_settings['s3_max_upload_size'] : '2048';
                    $mimes    = ! empty($storage_settings['s3_storage_validation']) ? $storage_settings['s3_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                } else {
                    $max_size = ! empty($storage_settings['local_storage_max_upload_size']) ? $storage_settings['local_storage_max_upload_size'] : '2048';
                    $mimes    = ! empty($storage_settings['local_storage_validation']) ? $storage_settings['local_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                }
                $file = $request->$key_name;
                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {
                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = Validator::make($request->all(), [
                    $key_name => $validation,
                ]);
                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg'  => $validator->messages()->first(),
                    ];
                    return $res;
                } else {
                    $name = $name;
                    $save = Storage::disk($storage_settings['storage_setting'])->putFileAs(
                        $path,
                        $file,
                        $name,
                    );
                    if ($storage_settings['storage_setting'] == 'wasabi') {
                        $url = $save;
                    } elseif ($storage_settings['storage_setting'] == 's3') {
                        $url = $save;
                    } else {
                        $url = 'uploads/' . $save;
                    }
                    $res = [
                        'flag' => 1,
                        'msg'  => 'success',
                        'url'  => $url,
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg'  => 'not set configurations',
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg'  => $e->getMessage(),
            ];
            return $res;
        }
    }
}

if (! function_exists('multi_upload_file')) {
    function multi_upload_file($request, $key_name, $name, $path, $custom_validation = [])
    {
        try {
            $storage_settings = getAdminAllSetting();

            if (isset($storage_settings['storage_setting'])) {
                if ($storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key'      => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret'   => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region'   => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket'   => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root'     => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url'],
                        ],
                    );
                    $max_size = ! empty($storage_settings['wasabi_max_upload_size']) ? $storage_settings['wasabi_max_upload_size'] : '2048';
                    $mimes    = ! empty($storage_settings['wasabi_storage_validation']) ? $storage_settings['wasabi_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                } else if ($storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key'    => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ],
                    );
                    $max_size = ! empty($storage_settings['s3_max_upload_size']) ? $storage_settings['s3_max_upload_size'] : '2048';
                    $mimes    = ! empty($storage_settings['s3_storage_validation']) ? $storage_settings['s3_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                } else {
                    $max_size = ! empty($storage_settings['local_storage_max_upload_size']) ? $storage_settings['local_storage_max_upload_size'] : '2048';
                    $mimes    = ! empty($storage_settings['local_storage_validation']) ? $storage_settings['local_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                }

                $file           = $request;
                $key_validation = $key_name . '*';
                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {
                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = Validator::make(array($key_name => $request), [
                    $key_validation => $validation,
                ]);
                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg'  => $validator->messages()->first(),
                    ];
                    return $res;
                } else {

                    $name = $name;

                    $save = Storage::disk($storage_settings['storage_setting'])->putFileAs(
                        $path,
                        $file,
                        $name,
                    );

                    if ($storage_settings['storage_setting'] == 'wasabi') {
                        $url = $save;
                    } elseif ($storage_settings['storage_setting'] == 's3') {
                        $url = $save;
                    } else {
                        $url = 'uploads/' . $save;
                    }
                    $res = [
                        'flag' => 1,
                        'msg'  => 'success',
                        'url'  => $url,
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg'  => 'not set configration',
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg'  => $e->getMessage(),
            ];
            return $res;
        }
    }
}

if (! function_exists('check_file')) {
    function check_file($path)
    {

        if (! empty($path)) {
            $storage_settings = getAdminAllSetting();
            if (isset($storage_settings['storage_setting']) && ($storage_settings['storage_setting'] == null || $storage_settings['storage_setting'] == 'local')) {

                return file_exists(base_path($path));
            } else {

                if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key'    => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ],
                    );
                } else if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key'      => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret'   => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region'   => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket'   => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root'     => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url'],
                        ],
                    );
                }
                try {
                    return Storage::disk($storage_settings['storage_setting'])->exists($path);
                } catch (\Throwable $th) {
                    return 0;
                }
            }
        } else {
            return 0;
        }
    }
}

if (! function_exists('get_file')) {
    function get_file($path)
    {

        $storage_settings = getAdminAllSetting();

        if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 's3') {
            config(
                [
                    'filesystems.disks.s3.key'    => $storage_settings['s3_key'],
                    'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                    'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                    'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                    // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                    // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                ],
            );
            return Storage::disk('s3')->url($path);
        } else if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 'wasabi') {
            config(
                [
                    'filesystems.disks.wasabi.key'      => $storage_settings['wasabi_key'],
                    'filesystems.disks.wasabi.secret'   => $storage_settings['wasabi_secret'],
                    'filesystems.disks.wasabi.region'   => $storage_settings['wasabi_region'],
                    'filesystems.disks.wasabi.bucket'   => $storage_settings['wasabi_bucket'],
                    'filesystems.disks.wasabi.root'     => $storage_settings['wasabi_root'],
                    'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url'],
                ],
            );

            return Storage::disk('wasabi')->url($path);
        } else {
            return asset($path);
        }
    }
}
if (! function_exists('get_base_file')) {
    function get_base_file($path)
    {
        $admin_settings = getAdminAllSetting();
        if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 's3') {
            config(
                [
                    'filesystems.disks.s3.key'    => $admin_settings['s3_key'],
                    'filesystems.disks.s3.secret' => $admin_settings['s3_secret'],
                    'filesystems.disks.s3.region' => $admin_settings['s3_region'],
                    'filesystems.disks.s3.bucket' => $admin_settings['s3_bucket'],
                    // 'filesystems.disks.s3.url' => $admin_settings['s3_url'],
                    // 'filesystems.disks.s3.endpoint' => $admin_settings['s3_endpoint'],
                ],
            );

            return Storage::disk('s3')->url($path);
        } else if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 'wasabi') {
            config(
                [
                    'filesystems.disks.wasabi.key'      => $admin_settings['wasabi_key'],
                    'filesystems.disks.wasabi.secret'   => $admin_settings['wasabi_secret'],
                    'filesystems.disks.wasabi.region'   => $admin_settings['wasabi_region'],
                    'filesystems.disks.wasabi.bucket'   => $admin_settings['wasabi_bucket'],
                    'filesystems.disks.wasabi.root'     => $admin_settings['wasabi_root'],
                    'filesystems.disks.wasabi.endpoint' => $admin_settings['wasabi_url'],
                ],
            );
            return Storage::disk('wasabi')->url($path);
        } else {
            return base_path($path);
        }
    }
}
if (! function_exists('delete_file')) {
    function delete_file($path)
    {
        if (check_file($path)) {
            $storage_settings = getAdminAllSetting();
            if (isset($storage_settings['storage_setting'])) {
                if ($storage_settings['storage_setting'] == 'local') {
                    return File::delete($path);
                } else {
                    if ($storage_settings['storage_setting'] == 's3') {
                        config(
                            [
                                'filesystems.disks.s3.key'    => $storage_settings['s3_key'],
                                'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                                'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                                'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                                // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                                // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                            ],
                        );
                    } else if ($storage_settings['storage_setting'] == 'wasabi') { {
                            config(
                                [
                                    'filesystems.disks.wasabi.key'      => $storage_settings['wasabi_key'],
                                    'filesystems.disks.wasabi.secret'   => $storage_settings['wasabi_secret'],
                                    'filesystems.disks.wasabi.region'   => $storage_settings['wasabi_region'],
                                    'filesystems.disks.wasabi.bucket'   => $storage_settings['wasabi_bucket'],
                                    'filesystems.disks.wasabi.root'     => $storage_settings['wasabi_root'],
                                    'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url'],
                                ],
                            );
                        }
                        return Storage::disk($storage_settings['storage_setting'])->delete($path);
                    }
                }
            }
        }
    }
}

if (! function_exists('get_size')) {
    function get_size($url)
    {
        $url = str_replace(' ', '%20', $url);
        $ch  = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }
}
if (! function_exists('delete_folder')) {
    function delete_folder($path)
    {
        $storage_settings = getAdminAllSetting();
        if (isset($storage_settings['storage_setting'])) {

            if ($storage_settings['storage_setting'] == 'local') {
                if (is_dir(Storage::path($path))) {
                    return \File::deleteDirectory(Storage::path($path));
                }
            } else {
                if ($storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key'    => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ],
                    );
                } else if ($storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key'      => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret'   => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region'   => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket'   => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root'     => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url'],
                        ],
                    );
                }
                return Storage::disk($storage_settings['storage_setting'])->deleteDirectory($path);
            }
        }
    }
}
if (! function_exists('delete_directory')) {
    function delete_directory($dir)
    {
        if (! file_exists($dir)) {
            return true;
        }

        if (! is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (! delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
if (! function_exists('currency')) {
    function currency($code = null)
    {
        if ($code == null) {
            $c = Currency::get();
        } else {
            $c = Currency::where('code', $code)->first();
        }
        return $c;
    }
}

// Company Subscription Details
if (! function_exists('SubscriptionDetails')) {
    function SubscriptionDetails($user_id = null)
    {
        $data           = [];
        $data['status'] = false;
        if ($user_id != null) {
            $user = User::find($user_id);
        } elseif (\Auth::check()) {
            $user = \Auth::user();
        }

        if (isset($user) && ! empty($user)) {
            if ($user->type != 'company' && $user->type != 'super admin') {
                $user = User::find($user->created_by);
            }

            if (! empty($user)) {
                if ($user->active_plan != 0) {
                    $data['status']           = true;
                    $data['active_plan']      = $user->active_plan;
                    $data['billing_type']     = $user->billing_type;
                    $data['plan_expire_date'] = $user->plan_expire_date;
                    $data['active_module']    = ActivatedModule();
                    $data['total_user']       = $user->total_user == -1 ? 'Unlimited' : (isset($user->total_user) ? $user->total_user : 'Unlimited');
                    $data['total_workspace']  = $user->total_workspace == -1 ? 'Unlimited' : (isset($user->total_workspace) ? $user->total_workspace : 'Unlimited');
                    $data['seeder_run']       = $user->seeder_run;
                }
            }
        }
        return $data;
    }
}


if (! function_exists('PlanCheck')) {
    function PlanCheck($type = 'User', $id = null)
    {
        if (! empty($id)) {
            $user = User::where('id', $id)->first();
            if ($user->type == 'company') {
                $id = $user->id;
            } else {
                $user = User::where('id', $user->created_by)->first();
                $id   = $user->id;
            }
        } else {
            $user = \Auth::user();
            if ($user->type == 'company') {
                $id = $user->id;
            } else {
                $user = User::where('id', $user->created_by)->first();
                $id   = $user->id;
            }
        }
        if ($type == "User") {
            if ($user->total_user >= 0) {
                if ($user->type == 'company') {
                    $users = User::where('created_by', $id)->where('workspace_id', getActiveWorkSpace())->get();
                } else {
                    $users = User::where('created_by', $user->created_by)->get();
                }
                if ($users->count() >= $user->total_user) {
                    return false;
                } else {
                    return true;
                }
            } elseif ($user->total_user < 0) {
                return true;
            }
        }
        if ($type == "Workspace") {
            if ($user->total_workspace >= 0) {
                $workspace = WorkSpace::where('created_by', $id)->get();

                if ($workspace->count() >= $user->total_workspace) {
                    return false;
                } else {
                    return true;
                }
            } elseif ($user->total_workspace < 0) {
                return true;
            }
        }
    }
}
if (! function_exists('CheckCoupon')) {
    function CheckCoupon($code, $price = 0, $plan_id)
    {
        if (empty($code) || intval($price) <= 0) {
            return $price;
        }

        $coupon = Coupon::where('code', strtoupper($code))
            ->where('is_active', '1')
            ->first();

        if (empty($coupon)) {
            return $price;
        }

        $usedCoupon     = $coupon->used_coupon();
        $userUsedCoupon = \Auth::user()->user_coupon_user($coupon);

        if ($usedCoupon >= $coupon->limit ||
            $userUsedCoupon >= $coupon->limit_per_user ||
            $coupon->minimum_spend > $price ||
            $coupon->maximum_spend < $price ||
            $coupon->expiry_date < date('Y-m-d')) {
            return $price;
        }

        switch ($coupon->type) {
            case 'percentage':
                $discountValue = ($price / 100) * $coupon->discount;
                $finalPrice = $price - $discountValue;
                break;
            case 'flat':
                $finalPrice = $price - $coupon->discount;
                break;
            case 'fixed':
                if ((! empty($coupon->included_module) && in_array($plan_id, explode(',', $coupon->included_module))) ||
                    (empty($coupon->included_module) && ! in_array($plan_id, explode(',', $coupon->excluded_module)))) {
                    $finalPrice = $price - $coupon->discount;
                } else {
                    return $price;
                }
                break;
            default:
                return $price;
        }

        return $finalPrice;
    }
}

if (! function_exists('UserCoupon')) {
    function UserCoupon($code, $orderID, $user_id = null)
    {
        if (! empty($code)) {
            $coupons = Coupon::where('code', strtoupper($code))->where('is_active', '1')->first();
            if ($user_id) {
                $user = User::find($user_id);
            } else {
                $user = \Auth::user();
            }
            if (! empty($coupons)) {
                $userCoupon         = new UserCoupon();
                $userCoupon->user   = $user->id;
                $userCoupon->coupon = $coupons->id;
                $userCoupon->order  = $orderID;
                $userCoupon->save();

                $usedCoupun = $coupons->used_coupon();
                if ($coupons->limit <= $usedCoupun) {
                    $coupons->is_active = 0;
                    $coupons->save();
                }
            }
        }
    }
}

// if Subscription price is 0 then call this
if (! function_exists('DirectAssignPlan')) {
    function DirectAssignPlan($plan_id, $duration, $user_module, $counter, $type, $coupon_code = null, $user_id = null)
    {
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $plan    = Plan::find($plan_id);
        if (empty($user_id)) {
            $user_id = \Auth::user()->id;
        }
        $user       = User::find($user_id);
        $assignPlan = $user->assignPlan($plan->id, $duration, $user_module, $counter, $user_id);
        if ($assignPlan['is_success']) {
            $order = Order::create(
                [
                    'order_id'       => $orderID,
                    'name'           => null,
                    'email'          => null,
                    'card_number'    => null,
                    'card_exp_month' => null,
                    'card_exp_year'  => null,
                    'plan_name'      => ! empty($plan->name) ? $plan->name : 'Basic Package',
                    'plan_id'        => $plan->id,
                    'price'          => 0,
                    'price_currency' => admin_setting('defult_currancy'),
                    'txn_id'         => '',
                    'payment_type'   => ! empty($type) ? $type : "STRIPE",
                    'payment_status' => 'succeeded',
                    'receipt'        => null,
                    'user_id'        => $user_id,
                ],
            );
            if ($coupon_code) {

                UserCoupon($coupon_code, $order);
            }
            return ['is_success' => true];
        } else {
            return ['is_success' => false];
        }
    }
}
if (! function_exists('makeEmailLang')) {
    function makeEmailLang($lang)
    {
        $templates = EmailTemplate::all();
        foreach ($templates as $template) {

            $default_lang = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', 'en')->first();

            $emailTemplateLang            = new EmailTemplateLang();
            $emailTemplateLang->parent_id = $template->id;
            $emailTemplateLang->lang      = $lang;
            $emailTemplateLang->subject   = $default_lang->subject;
            $emailTemplateLang->content   = $default_lang->content;
            $emailTemplateLang->variables = $default_lang->variables;
            $emailTemplateLang->save();
        }
    }
}
if (! function_exists('error_res')) {
    function error_res($msg = "", $args = array())
    {
        $msg       = $msg == "" ? "error" : $msg;
        $msg_id    = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg       = $msg_id == $converted ? $msg : $converted;
        $json      = array(
            'flag' => 0,
            'msg'  => $msg,
        );

        return $json;
    }
}

if (! function_exists('success_res')) {
    function success_res($msg = "", $args = array())
    {
        $msg  = $msg == "" ? "success" : $msg;
        $json = array(
            'flag' => 1,
            'msg'  => $msg,
        );

        return $json;
    }
}

if (! function_exists('GetDeviceType')) {
    function GetDeviceType($user_agent)
    {
        $mobile_regex = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tablet_regex = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';
        if (preg_match_all($mobile_regex, $user_agent)) {
            return 'mobile';
        } else {
            if (preg_match_all($tablet_regex, $user_agent)) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }
    }
}

// Get Cache Size
if (! function_exists('CacheSize')) {
    function CacheSize()
    {
        //start for cache clear
        $file_size = 0;
        foreach (\File::allFiles(storage_path('/framework')) as $file) {
            $file_size += $file->getSize();
        }
        $file_size = number_format($file_size / 1000000, 4);

        return $file_size;
    }
}

if (! function_exists('get_module_img')) {
    function get_module_img($module)
    {
        $url = url("/Modules/" . $module . '/favicon.png');
        return $url;
    }
}

if (! function_exists('sidebar_logo')) {
    function sidebar_logo()
    {
        $admin_settings = getAdminAllSetting();
        if (\Auth::check() && (\Auth::user()->type != 'super admin')) {
            $company_settings = getCompanyAllSetting();

            if ((isset($company_settings['cust_darklayout']) ? $company_settings['cust_darklayout'] : 'off') == 'on') {
                if (! empty($company_settings['logo_light'])) {
                    if (check_file($company_settings['logo_light'])) {
                        return $company_settings['logo_light'];
                    } else {
                        return 'uploads/logo/logo_light.png';
                    }
                } else {
                    if (! empty($admin_settings['logo_light'])) {
                        if (check_file($admin_settings['logo_light'])) {
                            return $admin_settings['logo_light'];
                        } else {
                            return 'uploads/logo/logo_light.png';
                        }
                    } else {
                        return 'uploads/logo/logo_light.png';
                    }
                }
            } else {
                if (! empty($company_settings['logo_dark'])) {
                    if (check_file($company_settings['logo_dark'])) {
                        return $company_settings['logo_dark'];
                    } else {
                        return 'uploads/logo/logo_dark.png';
                    }
                } else {
                    if (! empty($admin_settings['logo_dark'])) {
                        if (check_file($admin_settings['logo_dark'])) {
                            return $admin_settings['logo_dark'];
                        } else {
                            return 'uploads/logo/logo_dark.png';
                        }
                    } else {
                        return 'uploads/logo/logo_dark.png';
                    }
                }
            }
        } else {
            if ((isset($admin_settings['cust_darklayout']) ? $admin_settings['cust_darklayout'] : 'off') == 'on') {
                if (! empty($admin_settings['logo_light'])) {
                    if (check_file($admin_settings['logo_light'])) {
                        return $admin_settings['logo_light'];
                    } else {
                        return 'uploads/logo/logo_light.png';
                    }
                } else {
                    return 'uploads/logo/logo_light.png';
                }
            } else {
                if (! empty($admin_settings['logo_dark'])) {
                    if (check_file($admin_settings['logo_dark'])) {
                        return $admin_settings['logo_dark'];
                    } else {
                        return 'uploads/logo/logo_dark.png';
                    }
                } else {
                    return 'uploads/logo/logo_dark.png';
                }
            }
        }
    }
}

if (! function_exists('light_logo')) {
    function light_logo()
    {
        if (\Auth::check()) {
            $company_settings = getCompanyAllSetting();
            $logo_light       = isset($company_settings['logo_light']) ? $company_settings['logo_light'] : 'uploads/logo/logo_light.png';
        } else {
            $admin_settings = getAdminAllSetting();
            $logo_light     = isset($admin_settings['logo_light']) ? $admin_settings['logo_light'] : 'uploads/logo/logo_light.png';
        }
        if (check_file($logo_light)) {
            return $logo_light;
        } else {
            return 'uploads/logo/logo_dark.png';
        }
    }
}

if (! function_exists('dark_logo')) {
    function dark_logo()
    {
        if (\Auth::check()) {
            $company_settings = getCompanyAllSetting();
            $logo_dark        = isset($company_settings['logo_dark']) ? $company_settings['logo_dark'] : 'uploads/logo/logo_dark.png';
        } else {
            $admin_settings = getAdminAllSetting();
            $logo_dark      = isset($admin_settings['logo_dark']) ? $admin_settings['logo_dark'] : 'uploads/logo/logo_dark.png';
        }
        if (check_file($logo_dark)) {
            return $logo_dark;
        } else {
            return 'uploads/logo/logo_dark.png';
        }
    }
}

if (! function_exists('currency_format')) {
    function currency_format($price, $company_id = null, $workspace = null)
    {

        return number_format($price, company_setting('currency_format', $company_id, $workspace), '.', '');
    }
}

if (! function_exists('currency_format_with_sym')) {

    function currency_format_with_sym($price, $company_id = null, $workspace = null, $with_symbol = true)
    {
        if (! empty($company_id) && empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id);
        } elseif (! empty($company_id) && ! empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id, $workspace);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $symbol_position = 'pre';
        $currancy_symbol = '$';
        $currency_space  = null;
        $format          = '1';
        $number          = explode('.', $price);
        $length          = strlen(trim($number[0]));
        $float_number    = isset($company_settings['float_number']) && $company_settings['float_number'] != 'dot' ? ',' : '.';

        if ($length > 3) {
            $decimal_separator  = isset($company_settings['decimal_separator']) && $company_settings['decimal_separator'] === 'dot' ? ',' : ',';
            $thousand_separator = isset($company_settings['thousand_separator']) && $company_settings['thousand_separator'] === 'dot' ? '.' : ',';
        } else {
            $decimal_separator  = isset($company_settings['decimal_separator']) == 'dot' ? '.' : ',';
            $thousand_separator = isset($company_settings['thousand_separator']) == 'dot' ? '.' : ',';
        }
        if (isset($company_settings['site_currency_symbol_position'])) {
            $symbol_position = $company_settings['site_currency_symbol_position'];
        }
        if (isset($company_settings['defult_currancy_symbol'])) {
            $currancy_symbol = $company_settings['defult_currancy_symbol'];
        }
        if (isset($company_settings['currency_format'])) {
            $format = $company_settings['currency_format'];
        }
        if (isset($company_settings['currency_space'])) {
            $currency_space = isset($company_settings['currency_space']) ? $company_settings['currency_space'] : '';
        }
        if (isset($company_settings['site_currency_symbol_name'])) {
            $defult_currancy        = $company_settings['defult_currancy'];
            $defult_currancy_symbol = $company_settings['defult_currancy_symbol'];
            $currancy_symbol        = $company_settings['site_currency_symbol_name'] == 'symbol' ? $defult_currancy_symbol : $defult_currancy;
        }
        $price = number_format($price, $format, $decimal_separator, $thousand_separator);

        if ($company_settings['float_number'] == 'dot') {
            $price = preg_replace('/' . preg_quote($thousand_separator, '/') . '([^' . preg_quote($thousand_separator, '/') . ']*)$/', $float_number . '$1', $price);
        } else {
            $price = preg_replace('/' . preg_quote($decimal_separator, '/') . '([^' . preg_quote($decimal_separator, '/') . ']*)$/', $float_number . '$1', $price);
        }
        // return (
        //     ($symbol_position == "pre")  ?  $currancy_symbol : '') . ((isset($currency_space) && $currency_space) == 'withspace' ? ' ' : '')
        //     . number_format($price, $format, $decimal_separator, $thousand_separator) . ((isset($currency_space) && $currency_space) == 'withspace' ? ' ' : '') .
        //     (($symbol_position == "post") ?  $currancy_symbol : '');

        if ($with_symbol == true) {
            return (($symbol_position == "pre") ? $currancy_symbol : '') . ($currency_space == 'withspace' ? ' ' : '') . $price . ($currency_space == 'withspace' ? ' ' : '') . (($symbol_position == "post") ? $currancy_symbol : '');
        } else {
            return $price;
        }

    }
}

if (! function_exists('company_date_formate')) {
    function company_date_formate($date, $company_id = null, $workspace = null)
    {

        if (! empty($company_id) && empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id);
        } elseif (! empty($company_id) && ! empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id, $workspace);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $date_formate = ! empty($company_settings['site_date_format']) ? $company_settings['site_date_format'] : 'd-m-y';

        return date($date_formate, strtotime($date));
    }
}

if (! function_exists('super_currency_format_with_sym')) {
    function super_currency_format_with_sym($price)
    {

        $admin_settings  = getAdminAllSetting();
        $symbol_position = 'pre';
        $currency_space  = null;
        $symbol          = '$';
        $format          = '1';
        $number          = explode('.', $price);
        $length          = strlen(trim($number[0]));
        $float_number    = isset($admin_settings['float_number']) && $admin_settings['float_number'] != 'dot' ? ',' : '.';

        if ($length > 3) {
            $decimal_separator  = isset($admin_settings['decimal_separator']) && $admin_settings['decimal_separator'] === 'dot' ? ',' : ',';
            $thousand_separator = isset($admin_settings['thousand_separator']) && $admin_settings['thousand_separator'] === 'dot' ? '.' : ',';
        } else {
            $decimal_separator  = isset($admin_settings['decimal_separator']) == 'dot' ? '.' : ',';
            $thousand_separator = isset($admin_settings['thousand_separator']) == 'dot' ? '.' : ',';
        }

        if (isset($admin_settings['site_currency_symbol_position']) && $admin_settings['site_currency_symbol_position'] == "post") {
            $symbol_position = 'post';
        }

        if (isset($admin_settings['defult_currancy_symbol'])) {
            $symbol = $admin_settings['defult_currancy_symbol'];
        }

        if (isset($admin_settings['currency_format'])) {
            $format = $admin_settings['currency_format'];
        }

        if (isset($admin_settings['currency_space'])) {
            $currency_space = isset($admin_settings['currency_space']) ? $admin_settings['currency_space'] : '';
        }
        if (isset($admin_settings['site_currency_symbol_name'])) {
            $defult_currancy        = $admin_settings['defult_currancy'];
            $defult_currancy_symbol = $admin_settings['defult_currancy_symbol'];
            $symbol                 = $admin_settings['site_currency_symbol_name'] == 'symbol' ? $defult_currancy_symbol : $defult_currancy;
        }
        $price = number_format($price, $format, $decimal_separator, $thousand_separator);

        if ($float_number == 'dot') {
            $price = preg_replace('/' . preg_quote($thousand_separator, '/') . '([^' . preg_quote($thousand_separator, '/') . ']*)$/', $float_number . '$1', $price);
        } else {
            $price = preg_replace('/' . preg_quote($decimal_separator, '/') . '([^' . preg_quote($decimal_separator, '/') . ']*)$/', $float_number . '$1', $price);
        }
        // return (
        //     ($symbol_position == "pre")  ?  $symbol : '') . ((isset($currency_space) && $currency_space) == 'withspace' ? ' ' : '')
        //     . number_format($price, $format, $decimal_separator, $thousand_separator) . ((isset($currency_space) && $currency_space) == 'withspace' ? ' ' : '') .
        //     (($symbol_position == "post") ?  $symbol : '');
        return (($symbol_position == "pre") ? $symbol : '') . ($currency_space == 'withspace' ? ' ' : '') . $price . ($currency_space == 'withspace' ? ' ' : '') . (($symbol_position == "post") ? $symbol : '');

    }
}
if (! function_exists('company_datetime_formate')) {
    function company_datetime_formate($date, $company_id = null, $workspace = null)
    {
        $company_settings = getCompanyAllSetting($company_id, $workspace);
        $date_formate     = ! empty($company_settings['site_date_format']) ? $company_settings['site_date_format'] : 'd-m-y';
        $time_formate     = ! empty($company_settings['site_time_format']) ? $company_settings['site_time_format'] : 'H:i';
        return date($date_formate . ' ' . $time_formate, strtotime($date));
    }
}
if (! function_exists('company_Time_formate')) {
    function company_Time_formate($time, $company_id = null, $workspace = null)
    {
        if (! empty($company_id) && empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id);
        } elseif (! empty($company_id) && ! empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id, $workspace);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $time_formate = ! empty($company_settings['site_time_format']) ? $company_settings['site_time_format'] : 'H:i';
        return date($time_formate, strtotime($time));
    }
}
// module price name
if (! function_exists('ModulePriceByName')) {
    function ModulePriceByName($module_name)
    {
        static $addons = [];
        static $resultArray = [];
        if (count($addons) == 0 && count($resultArray) == 0) {
            $addons      = AddOn::all()->toArray();
            $resultArray = array_reduce($addons, function ($carry, $item) {
                // Check if both "module" and "name" keys exist in the current item
                if (isset($item['module'])) {
                    // Add a new key-value pair to the result array
                    $carry[$item['module']]['monthly_price'] = $item['monthly_price'];
                    $carry[$item['module']]['yearly_price'] = $item['yearly_price'];
                }
                return $carry;
            }, []);
        }

        $module                = Module::find($module_name);
        $data                  = [];
        $data['monthly_price'] = 0;
        $data['yearly_price']  = 0;

        if (! empty($module)) {
            $path = $module->getPath() . '/module.json';
            $json = json_decode(file_get_contents($path), true);

            $data['monthly_price'] = (isset($json['monthly_price']) && ! empty($json['monthly_price'])) ? $json['monthly_price'] : 0;
            $data['yearly_price']  = (isset($json['yearly_price']) && ! empty($json['yearly_price'])) ? $json['yearly_price'] : 0;
        }

        if (isset($resultArray)) {
            $data['monthly_price'] = isset($resultArray[$module_name]['monthly_price']) ? $resultArray[$module_name]['monthly_price'] : $data['monthly_price'];
            $data['yearly_price']  = isset($resultArray[$module_name]['yearly_price']) ? $resultArray[$module_name]['yearly_price'] : $data['yearly_price'];
        }

        return $data;
    }
}
// invoice template Data

if (! function_exists('templateData')) {
    function templateData()
    {
        $arr              = [];
        $arr['colors']    = [
            '003580',
            '666666',
            '6676ef',
            'f50102',
            'f9b034',
            'fbdd03',
            'c1d82f',
            '37a4e4',
            '8a7966',
            '6a737b',
            '050f2c',
            '0e3666',
            '3baeff',
            '3368e6',
            'b84592',
            'f64f81',
            'f66c5f',
            'fac168',
            '46de98',
            '40c7d0',
            'be0028',
            '2f9f45',
            '371676',
            '52325d',
            '511378',
            '0f3866',
            '48c0b6',
            '297cc0',
            'ffffff',
            '000',
        ];
        $arr['templates'] = [
            "template1"  => "New York",
            "template2"  => "Toronto",
            "template3"  => "Rio",
            "template4"  => "London",
            "template5"  => "Istanbul",
            "template6"  => "Mumbai",
            "template7"  => "Hong Kong",
            "template8"  => "Tokyo",
            "template9"  => "Sydney",
            "template10" => "Paris",
        ];
        return $arr;
    }
}
if (! function_exists('AnnualLeaveCycle')) {
    function AnnualLeaveCycle()
    {
        $start_date = date('Y-m-d', strtotime(date('Y') . '-01-01 -1 day'));
        $end_date   = date('Y-m-d', strtotime(date('Y') . '-12-31 +1 day'));

        $date['start_date'] = $start_date;
        $date['end_date']   = $end_date;

        return $date;
    }
}

// time tracker
if (! function_exists('second_to_time')) {
    function second_to_time($seconds = 0)
    {
        $H    = floor($seconds / 3600);
        $i    = ($seconds / 60) % 60;
        $s    = $seconds % 60;
        $time = sprintf("%02d:%02d:%02d", $H, $i, $s);
        return $time;
    }
}

if (! function_exists('genericGetContacts')) {
    function genericGetContacts()
    {
        $user             = Auth::user();
        $existingContacts = User::where('created_by', '=', $user->id)
            ->get()
            ->map(function ($contact) {
                $name = $contact->name;

                // $companyName = $contact->company_name ?? null;
                $address_1 = $contact->address_1 ?? null;

                // Concatenate the name and company_name if available
                $displayName = $address_1 ? "$name ($address_1)" : $name;
                if ($displayName != '' && ! empty($displayName)) {

                    return [
                        'id'   => $contact->id,
                        'name' => $displayName,
                        'type' => $contact->type,
                    ];
                }
            });

        return $existingContacts;
    }
}

if (! function_exists('site_money_format')) {
    function site_money_format()
    {
        $company_settings = getCompanyAllSetting();
        if (isset($company_settings['defult_currancy']) && $company_settings['defult_currancy'] == "EUR") {
            return "de_DE";
        } else {
            return "en_US";
        }
    }
}

if (! function_exists('export_money_format')) {
    function export_money_format($amount = "")
    {
        return isset($amount) ? number_format((float) $amount, 2, '.', '') : '0';
    }
}

if (! function_exists('get_file_name')) {
    function get_file_name($estimation_id = "")
    {
        $company_details = getCompanyAllSetting();
        $company_name    = $company_details['company_name'];
        $estimation      = ProjectEstimation::whereId($estimation_id)->first();
        $project         = $estimation->project();
        $file_name       = '';
        if (isset($estimation) && ! empty($estimation)) {
            $file_name .= $estimation->title . ' - ' . $project->name;
        }
        if (isset($project->construction_detail->address_1)) {
            $file_name .= ' - ' . $project->construction_detail->address_1;
        }
        if (isset($project->construction_detail->city)) {
            $file_name .= ' - ' . $project->construction_detail->city;
        }
        if (isset($estimation) && ! empty($estimation)) {
            $file_name .= ' - #1' . $estimation->id . ' - ' . $company_name;
        }
        return str_replace("/", " ", $file_name);
    }
}

if (! function_exists('priceFormat')) {
    function priceFormat($settings, $price)
    {
        return (($settings['site_currency_symbol_position'] == "pre") ? $settings['defult_currancy_symbol'] : '') . number_format($price, 2) . (($settings['site_currency_symbol_position'] == "post") ? $settings['defult_currancy_symbol'] : '');
    }
}

if (! function_exists('getFontColor')) {
    function getFontColor($color_code)
    {
        $rgb = hex2rgb($color_code);
        $R   = $G = $B = $C = $L = $color = '';

        $R = (floor($rgb[0]));
        $G = (floor($rgb[1]));
        $B = (floor($rgb[2]));

        $C = [
            $R / 255,
            $G / 255,
            $B / 255,
        ];

        for ($i = 0; $i < count($C); ++$i) {
            if ($C[$i] <= 0.03928) {
                $C[$i] = $C[$i] / 12.92;
            } else {
                $C[$i] = pow(($C[$i] + 0.055) / 1.055, 2.4);
            }
        }

        $L = 0.2126 * $C[0] + 0.7152 * $C[1] + 0.0722 * $C[2];

        if ($L > 0.179) {
            $color = 'black';
        } else {
            $color = 'white';
        }

        return $color;
    }
}
if (! function_exists('hex2rgb')) {
    function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array(
            $r,
            $g,
            $b,
        );

        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }
}

if (! function_exists('current_controller')) {
    function current_controller()
    {
        return str_replace("controller", "", strtolower(strtok(substr(strrchr(request()->route()->getActionName(), '\\'), 1), '@')));
    }
}

if (! function_exists('current_method')) {
    function current_method()
    {
        return request()->route()->getActionMethod();
    }
}

if (! function_exists('get_group_pos')) {
    function get_group_pos($item_pos = "")
    {

        if (strpos($item_pos, '.') !== false) {
            $parts = explode('.', $item_pos);
            return $parts[0];
        }

        return $item_pos;
    }
}

if (! function_exists('getNotificationTemplateData')) {
    function getNotificationTemplateData($slug)
    {
        $notification_template = Content::where('slug', $slug)->first();
        $data                  = '';
        if (! empty($notification_template)) {
            $curr_noti_tempLang = ContentTemplateLang::where('parent_id', '=', $notification_template->id)->where('lang', Auth::user()->lang)->where('created_by', '=', Auth::user()->id)->first();
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = ContentTemplateLang::where('parent_id', '=', $notification_template->id)->where('lang', Auth::user()->lang)->first();
            }
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = ContentTemplateLang::where('parent_id', '=', $notification_template->id)->where('lang', 'en')->first();
            }

            if (! empty($curr_noti_tempLang) && ! empty($curr_noti_tempLang->content)) {
                $data = $curr_noti_tempLang->content;
                return $data;
            }
        }

        return $data;
    }
}

if (! function_exists('getNotificationTemplateDataByID')) {
    function getNotificationTemplateDataByID($ids)
    {
        $data                  = array();
        $notification_template = Content::whereIn('id', $ids)->get();
        if (! empty($notification_template)) {
            foreach ($notification_template as $row) {
                $curr_noti_tempLang = ContentTemplateLang::where('parent_id', '=', $row->id)->where('lang', \Auth::user()->lang)->where('created_by', '=', creatorId())->first();
                if (empty($curr_noti_tempLang)) {
                    $curr_noti_tempLang = ContentTemplateLang::where('parent_id', '=', $row->id)->where('lang', Auth::user()->lang)->first();
                }
                if (empty($curr_noti_tempLang)) {
                    $curr_noti_tempLang = ContentTemplateLang::where('parent_id', '=', $row->id)->where('lang', 'en')->first();
                }
                if (! empty($curr_noti_tempLang) && ! empty($curr_noti_tempLang->content)) {
                    $data[] = array(
                        'id'         => $row->id,
                        'content_id' => $curr_noti_tempLang->id,
                        'content'    => $curr_noti_tempLang->content,
                    );
                }
            }
        }
        return $data;
    }
}

if (! function_exists('mail_to_admin')) {
    function mail_to_admin($msg = "", $ai_key = "")
    {
        $return = "";
        $data   = array('error_message' => json_encode($msg), 'ai_key' => $ai_key);

        $setconfing           = SetConfigEmail();
        $smtp_error           = [];
        $smtp_error['status'] = true;
        $smtp_error['msg']    = '';
        if ($setconfing == true) {
            try {
                Mail::send(['text' => 'taskly::email.mail'], $data, function ($message) {
                    $message->to('ajaz.angelinfotech@gmail.com', 'Harsh Sanghani')->subject
                    ('Neu-west CRM Queue Error');
                });
            } catch (\Exception $e) {
                $smtp_error['status'] = false;
                $smtp_error['msg']    = $e->getMessage();
            }
        } else {
            $smtp_error['status'] = false;
            $smtp_error['msg']    = __('Something went wrong please try again ');
        }


        return true;
    }
}

if (! function_exists('trimMean')) {
    function trimMean($data, $percent)
    {
        // Check if the percentage value is valid
        if ($percent < 0 || $percent > 1) {
            throw new InvalidArgumentException('Percentage must be between 0 and 1.');
        }

        // Sort the data
        sort($data);

        // Determine the total number of data points
        $total = count($data);

        // Calculate the number of data points to be removed from each side
        $numToRemove = (int) floor($total * $percent / 2);

        // Remove the smallest and largest values
        $trimmedData = array_slice($data, $numToRemove, $total - 2 * $numToRemove);

        // Calculate the average of the remaining values
        $mean = array_sum($trimmedData) / count($trimmedData);

        return round($mean, 2);
    }
}

if (! function_exists('is_image')) {
    function is_image($file_name = "")
    {
        $return = false;

        if ($file_name != '') {
            $ext             = pathinfo($file_name, PATHINFO_EXTENSION);
            $supported_image = array('gif', 'jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'heic', 'HEIC');
            if (in_array($ext, $supported_image)) {
                $return = true;
            }
        }

        return $return;
    }
}

if (! function_exists('ai_notification_templates')) {
    function ai_notification_templates()
    {
        return Content::where('is_ai', 1)->get();
    }
}

if (! function_exists('ai_models')) {
    function ai_models()
    {
        return AiModel::where('status', 1)->get();
    }
}