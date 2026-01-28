<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'Dashboard', 'slug' => 'dashboard'],
            ['name' => 'User Types & Roles', 'slug' => 'user_roles'],
            ['name' => 'Workers', 'slug' => 'workers'],
            ['name' => 'Customers', 'slug' => 'customers'],
            ['name' => 'Service Categories', 'slug' => 'service_categories'],
            ['name' => 'Orders / Booking', 'slug' => 'orders'],
            ['name' => 'Withdrawal', 'slug' => 'withdrawal'],
            ['name' => 'Transactions', 'slug' => 'transactions'],
            ['name' => 'Referrals', 'slug' => 'referrals'],
            ['name' => 'Location & Zone', 'slug' => 'location_zone'],
            ['name' => 'Commission', 'slug' => 'commission'],
            ['name' => 'Settings', 'slug' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                ['name' => $permission['name']]
            );
        }
    }
}
