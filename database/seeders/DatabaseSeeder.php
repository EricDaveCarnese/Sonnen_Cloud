<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\InventoryItem;
use App\Models\MenuItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. System Users
        User::create([
            'fullname' => 'Geraldine Respeusto',
            'username' => 'owner',
            'password' => Hash::make('password123'),
            'role' => 'owner_manager',
            'status' => 'active'
        ]);

        User::create([
            'fullname' => 'Frontdesk Admin',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => 'admin', 
            'status' => 'active'
        ]);

        User::create([
            'fullname' => 'Inventory Staff',
            'username' => 'ops', 
            'password' => Hash::make('password123'),
            'role' => 'operations', 
            'status' => 'active'
        ]);

        // 2. Inventory Items
        $coffee = InventoryItem::create([
            'item_name' => 'Highland Coffee Beans',
            'category' => 'Beverage Stock',
            'quantity_on_hand' => 20.00, 
            'unit_of_measure' => 'kg', 
            'reorder_level' => 5.00
        ]);

        $rice = InventoryItem::create([
            'item_name' => 'Jasmine Rice',
            'category' => 'Raw Ingredient',
            'quantity_on_hand' => 50.00, 
            'unit_of_measure' => 'kg', 
            'reorder_level' => 10.00
        ]);

        // 3. Menu Items
        MenuItem::create([
            'item_name' => 'Brewed Highland Coffee',
            'category' => 'Beverage',
            'unit_price' => 120.00,
            'inventory_item_id' => $coffee->id,
            'ingredient_qty_per_order' => 0.02
        ]);

        MenuItem::create([
            'item_name' => 'Native Chicken Rice Bowl',
            'category' => 'Main Course',
            'unit_price' => 250.00,
            'inventory_item_id' => $rice->id,
            'ingredient_qty_per_order' => 0.20
        ]);

        // 4. Supplier
        Supplier::create([
            'supplier_name' => 'Marilog Farms Co.',
            'contact_person' => 'Juan Dela Cruz',
            'phone_number' => '09171234567',
            'email' => 'juan@marilogfarms.ph',
            'address' => 'Marilog District, Davao City'
        ]);
    }
}