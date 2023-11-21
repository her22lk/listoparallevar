<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class Roles extends Seeder
{
    public function run(): void
    {


        $adminRole = Role::create(['name' => 'admin']);
        Role::create(['name' => 'person']);
        Role::create(['name' => 'business']);

        $permissions = Permission::pluck('id', 'id')->all();
        $adminRole->syncPermissions($permissions);


    }

}
