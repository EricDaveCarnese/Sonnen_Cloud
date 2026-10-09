<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $items = [

            // Hot Drinks
            ['Sikwate',                        'Hot Drinks', 25.00],
            ['Kopiko Brown',                   'Hot Drinks', 25.00],
            ['Kopiko Black',                   'Hot Drinks', 25.00],
            ['Kopiko Blanca',                  'Hot Drinks', 25.00],
            ['Kopiko L.A. Coffee',             'Hot Drinks', 25.00],
            ['Kopiko Cappuccino',              'Hot Drinks', 25.00],
            ['Kopiko Café Mocha',              'Hot Drinks', 25.00],
            ['Kopiko Double Cups',             'Hot Drinks', 25.00],
            ['Nescafe Original',               'Hot Drinks', 25.00],
            ['Nescafe Creamy White',           'Hot Drinks', 25.00],
            ['Nescafe Creamy Latte',           'Hot Drinks', 25.00],
            ['Nescafe Sugarfree Original',     'Hot Drinks', 25.00],
            ['Nescafe Sugarfree Creamy White', 'Hot Drinks', 25.00],
            ['Bearbrand Swak',                 'Hot Drinks', 25.00],
            ['Bearbrand Adultplus',            'Hot Drinks', 25.00],
            ['Birch Tree',                     'Hot Drinks', 25.00],
            ['Bearbrand Chocolate',            'Hot Drinks', 25.00],
            ['Birch Tree Chocolate',           'Hot Drinks', 25.00],
            ['Milo',                           'Hot Drinks', 25.00],

            // Cold Drinks
            ['Orange Juice',         'Cold Drinks', 20.00],
            ['Pineapple Juice',      'Cold Drinks', 20.00],
            ['Mango Juice',          'Cold Drinks', 20.00],
            ['Apple Iced Tea',       'Cold Drinks', 20.00],
            ['Lemon Iced Tea',       'Cold Drinks', 20.00],
            ['Peach Iced Tea',       'Cold Drinks', 20.00],
            ['Nature Spring 250ml',  'Cold Drinks', 20.00],
            ['Nature Spring 500ml',  'Cold Drinks', 25.00],
            ['Nature Spring 1000ml', 'Cold Drinks', 35.00],
            ['Nature Spring 1.5L',   'Cold Drinks', 45.00],

            // Snacks
            ['Nagaraya',   'Snacks', 20.00],
            ['Fishda',     'Snacks', 20.00],
            ['Cheezy',     'Snacks', 20.00],
            ['Mangjuan',   'Snacks', 20.00],
            ['Cracklings', 'Snacks', 20.00],
            ['Clover',     'Snacks', 20.00],
            ['Piattos',    'Snacks', 25.00],
            ['Nova',       'Snacks', 25.00],

            // Food Add-ons
            ['Pancit Canton',           'Food Add-ons', 25.00],
            ['Extra Big Pancit Canton', 'Food Add-ons', 35.00],
            ['Plain Rice',              'Food Add-ons', 15.00],
            ['Fried Rice',              'Food Add-ons', 20.00],
            ['Suman (Malagkit)',        'Food Add-ons', 10.00],
        ];

        $now = now();

        foreach ($items as [$name, $category, $price]) {
            // Idempotent: only insert if not already present
            $exists = DB::table('menu_items')->where('item_name', $name)->exists();
            if ($exists) {
                continue;
            }

            DB::table('menu_items')->insert([
                'item_name'                => $name,
                'category'                 => $category,
                'unit_price'               => $price,
                'inventory_item_id'        => null,
                'ingredient_qty_per_order' => 1.00,
                'created_at'               => $now,
                'updated_at'               => $now,
            ]);
        }
    }

    public function down(): void
    {
        $names = [
            'Tapsilog (Beef)', 'Tapsilog (Pork)', 'Porksilog', 'Chickensilog',
            'Longsilog', 'Bangsilog', 'Cornsilog', 'Hotsilog',
            'Sisilog (Chicken)', 'Sisilog (Pork)', 'Tosilog',
            'Sikwate', 'Kopiko Brown', 'Kopiko Black', 'Kopiko Blanca',
            'Kopiko L.A. Coffee', 'Kopiko Cappuccino', 'Kopiko Café Mocha',
            'Kopiko Double Cups', 'Nescafe Original', 'Nescafe Creamy White',
            'Nescafe Creamy Latte', 'Nescafe Sugarfree Original',
            'Nescafe Sugarfree Creamy White', 'Bearbrand Swak', 'Bearbrand Adultplus',
            'Birch Tree', 'Bearbrand Chocolate', 'Birch Tree Chocolate', 'Milo',
            'Orange Juice', 'Pineapple Juice', 'Mango Juice',
            'Apple Iced Tea', 'Lemon Iced Tea', 'Peach Iced Tea',
            'Nature Spring 250ml', 'Nature Spring 500ml', 'Nature Spring 1000ml',
            'Nature Spring 1.5L',
            'Nagaraya', 'Fishda', 'Cheezy', 'Mangjuan', 'Cracklings',
            'Clover', 'Piattos', 'Nova',
            'Pancit Canton', 'Extra Big Pancit Canton', 'Plain Rice',
            'Fried Rice', 'Suman (Malagkit)',
        ];

        DB::table('menu_items')->whereIn('item_name', $names)->delete();
    }
};