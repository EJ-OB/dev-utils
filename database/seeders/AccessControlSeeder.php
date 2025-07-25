<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SystemRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $this->createUsers();

        $this->createRoles();

        $this->createPermissions();

        $this->assignPermissions();

        $this->assignRoles();
    }

    public function createUsers(): void
    {
        $domain = Config::string('app.domain');

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => "superadmin$domain",
            'system' => true,
        ]);

        //        User::factory()->count(10)->create();
    }

    public function createRoles(): void
    {
        foreach (SystemRoleEnum::values() as $role) {
            Role::create(['name' => $role]);
        }
    }

    public function createPermissions(): void
    {
        Artisan::call('shield:generate', [
            '--panel' => 'admin',
            '--resource' => 'UserResource,RoleResource',
            '--ignore-existing-policies' => true,
        ]);
    }

    private function assignPermissions(): void
    {
        Role::where('name', SystemRoleEnum::ADMIN->value)
            ->firstOrFail()
            ->givePermissionTo(Permission::all());

        Role::where('name', SystemRoleEnum::USER->value)
            ->firstOrFail()
            ->givePermissionTo(
                Permission::where('name', 'like', 'view_%')
                    ->get()
                    ->reject(function (Permission $permission) {
                        return Str::of($permission->name)->contains([
                            'user',
                            'role',
                        ]);
                    })
            );
    }

    public function assignRoles(): void
    {
        Artisan::call('shield:super-admin', [
            '--user' => 1,
        ]);

        User::whereNot('id', 1)->each(function (User $user) {
            $user->assignRole(
                Role::whereNot('name', SystemRoleEnum::SUPER_ADMIN->value)
                    ->inRandomOrder()
                    ->firstOrFail()
            );
        });
    }
}
