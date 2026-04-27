<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Inventory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // EMPLOYEES
        // =========================
        Employee::create([
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'contact_number' => '09123456789',
            'address' => '123 Main St, Manila',
            'role' => 'cashier'
        ]);

        Employee::create([
            'first_name' => 'Maria',
            'middle_name' => 'Garcia',
            'last_name' => 'Lopez',
            'contact_number' => '09987654321',
            'address' => '456 Second Ave, Quezon City',
            'role' => 'staff'
        ]);

        // =========================
        // SUPPLIERS
        // =========================
        Supplier::create([
            'supplier_name' => 'Local Poultry Farm',
            'contact_number' => '02-1234567',
            'address' => 'Digos City'
        ]);

        Supplier::create([
            'supplier_name' => 'Metro Food Supplies Inc.',
            'contact_number' => '02-7654321',
            'address' => 'Digos City'
        ]);

        // =========================
        // RAW PRODUCTS ONLY
        // =========================

        $whole_chicken = Product::create([
            'product_name' => 'Whole Chicken',
            'category' => 'raw',
            'unit' => 'pc',
            'price' => 180.00
        ]);

        $charcoal = Product::create([
            'product_name' => 'Charcoal',
            'category' => 'raw',
            'unit' => 'kg',
            'price' => 30.00
        ]);

        $rice = Product::create([
            'product_name' => 'Rice',
            'category' => 'raw',
            'unit' => 'kg',
            'price' => 60.00
        ]);

        $marinade = Product::create([
            'product_name' => 'BBQ Marinade',
            'category' => 'raw',
            'unit' => 'bottle',
            'price' => 25.00
        ]);

        // =========================
        // FINISHED PRODUCTS
        // =========================

        $lechon_manok = Product::create([
            'product_name' => 'Lechon Manok (Whole)',
            'category' => 'finished',
            'unit' => 'pc',
            'price' => 450.00
        ]);

        $manok_quarter = Product::create([
            'product_name' => 'Lechon Manok Quarter',
            'category' => 'finished',
            'unit' => 'pc',
            'price' => 140.00
        ]);

        $manok_sauce = Product::create([
            'product_name' => 'Special Sauce',
            'category' => 'finished',
            'unit' => 'bottle',
            'price' => 50.00
        ]);

        // =========================
        // INVENTORY
        // =========================

        Inventory::create([
            'product_id' => $whole_chicken->id,
            'quantity_on_hand' => 50
        ]);

        Inventory::create([
            'product_id' => $charcoal->id,
            'quantity_on_hand' => 100
        ]);

        Inventory::create([
            'product_id' => $rice->id,
            'quantity_on_hand' => 200
        ]);

        Inventory::create([
            'product_id' => $marinade->id,
            'quantity_on_hand' => 40
        ]);

        Inventory::create([
            'product_id' => $lechon_manok->id,
            'quantity_on_hand' => 20
        ]);

        Inventory::create([
            'product_id' => $manok_quarter->id,
            'quantity_on_hand' => 60
        ]);

        Inventory::create([
            'product_id' => $manok_sauce->id,
            'quantity_on_hand' => 100
        ]);
    }
}