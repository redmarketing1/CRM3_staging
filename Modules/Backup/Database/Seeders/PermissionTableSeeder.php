<?php

namespace Modules\Backup\Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        // Unguard the model to allow mass assignment
        Model::unguard();

        // Clear the cache to ensure that any cached permissions are removed
        Artisan::call('cache:clear');

        $module = 'Backup';

        $permissions = [
            'backup.view',
            'backup.create',
            'backup.settings',
        ];

        /**
         * Get the 'Super Admin' role
         */
        $superAdminRole = Role::where('name', 'super admin')->first();

        // Check if the role exists before proceeding
        if (! $superAdminRole) {
            $this->command->error('Role "Super Admin" not found.');
            return;
        }

        foreach ($permissions as $permissionName) {

            /**
             * @var mixed
             */
            $existingPermission = Permission::where('name', $permissionName)
                ->where('module', $module)
                ->first();

            if (! $existingPermission) {
                // Create a new permission using Eloquent
                $newPermission = Permission::create([
                    'name'       => $permissionName,
                    'guard_name' => 'web',
                    'module'     => $module,
                    'created_by' => 0,
                ]);

                /**
                 * Assign the permission to the 'Super Admin' role if it doesn't already have it
                 */
                if (! $superAdminRole->hasPermission($newPermission)) {
                    $superAdminRole->givePermission($newPermission);
                }
            }
        }

        $this->command->info('Permissions have been seeded and assigned to the "Super Admin" role.');
    }
}