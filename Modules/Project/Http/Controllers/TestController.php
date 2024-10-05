<?php

namespace Modules\Project\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class TestController extends Controller
{

    public function AddTeamTeamPermissions(){
        Model::unguard();
        Artisan::call('cache:clear');
        // $remove_old_invite_permission = Permission::where('name','project invite user')->first();
        // $remove_old_invite_permission->delete();

        // $remove_old_team_member_remove = Permission::where('name','team member remove')->first();
        // $remove_old_team_member_remove->delete();

        $new_team_permissions = [
            'team member manage',
            'team member view',
        ];

        foreach ($new_team_permissions as $key => $value)
        { 
            $company_role = Role::where('name','company')->first();

            $per = Permission::create(
                [
                    'name' => $value,
                    'guard_name' => 'web',
                    'module' => 'Taskly',
                    'created_by' => 0,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s')
                ]);
                if(!$company_role->hasPermission($value))
                {
                    $company_role->givePermission($per);
                }
        }
        return 'success';
    }

    public function AddClientermissions(){
        Model::unguard();
        Artisan::call('cache:clear');
        
        $remove_old_client_member_remove = Permission::where('name','team client remove')->first();
        $remove_old_client_member_remove->delete();

        $new_team_permissions = [
            'client manage',
            'client view',
            'client name',
            'client address',
            'client contact details',
            'client tax number',
            'client notes',
            'client invoice name',
            'client invoice address',
            'client invoice contact details',
            'client invoice tax number',
            'client invoice notes',
        ];

        foreach ($new_team_permissions as $key => $value)
        { 
            $company_role = Role::where('name','company')->first();

            $per = Permission::create(
                [
                    'name' => $value,
                    'guard_name' => 'web',
                    'module' => 'Taskly',
                    'created_by' => 0,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s')
                ]);
                if(!$company_role->hasPermission($value))
                {
                    $company_role->givePermission($per);
                }
        }
        return 'success';
    }

    public function AddFilesPermissions(){
        Model::unguard();
        Artisan::call('cache:clear');
        
        $new_team_permissions = [
            'files manage',
            'files view',
            'files delete',
            'files download',
        ];

        foreach ($new_team_permissions as $key => $value)
        { 
            $company_role = Role::where('name','company')->first();

            $per = Permission::create(
                [
                    'name' => $value,
                    'guard_name' => 'web',
                    'module' => 'Taskly',
                    'created_by' => 0,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s')
                ]);
                if(!$company_role->hasPermission($value))
                {
                    $company_role->givePermission($per);
                }
        }
        return 'success';
    }

    public function AddProjectProgressPermissions(){
        Model::unguard();
        Artisan::call('cache:clear');
        
        $new_team_permissions = [
            'progress manage',
            'progress view',
            'progress create internal progress',
            'progress create client progress',
        ];

        foreach ($new_team_permissions as $key => $value)
        { 
            $company_role = Role::where('name','company')->first();

            $per = Permission::create(
                [
                    'name' => $value,
                    'guard_name' => 'web',
                    'module' => 'Taskly',
                    'created_by' => 0,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s')
                ]);
                if(!$company_role->hasPermission($value))
                {
                    $company_role->givePermission($per);
                }
        }
        return 'success';
    }

}