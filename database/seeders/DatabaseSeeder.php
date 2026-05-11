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
        // Employees (per data gathering: Juan Dela Cruz - cashier/storekeeper, Maria Garcia - staff)
        Employee::create([
            'first_name'     => 'Juan',
            'middle_name'    => 'Dela',
            'last_name'      => 'Cruz',
            'contact_number' => '09123456789',
            'address'        => 'Bunawan, Davao City',
            'role'           => 'cashier',
        ]);

        Employee::create([
            'first_name'     => 'Maria',
            'middle_name'    => 'Garcia',
            'last_name'      => 'Lopez',
            'contact_number' => '09987654321',
            'address'        => 'Bunawan, Davao City',
            'role'           => 'staff',
        ]);

        // Suppliers (per data gathering document)
        Supplier::create([
            'supplier_name'  => 'Local Poultry Farm',
            'contact_number' => '02-1234567',
            'address'        => 'Digos City',
        ]);

        Supplier::create([
            'supplier_name'  => 'Metro Food Supplies Inc.',
            'contact_number' => '02-7654321',
            'address'        => 'Digos City',
        ]);

        // Raw materials (prices and stock per prototype mockup)
        $rawProducts = [
            ['product_name' => 'Whole Chicken', 'unit' => 'pc',     'price' => 180.00],
            ['product_name' => 'Charcoal',       'unit' => 'kg',     'price' =>  30.00],
            ['product_name' => 'BBQ Marinade',   'unit' => 'bottle', 'price' =>  25.00],
            ['product_name' => 'Pork Belly',     'unit' => 'kg',     'price' => 250.00],
        ];

        $rawQty = [30, 100, 30, 80];

        foreach ($rawProducts as $i => $data) {
            $product = Product::create(array_merge($data, ['category' => 'raw']));
            Inventory::create(['product_id' => $product->id, 'quantity_on_hand' => $rawQty[$i]]);
        }

        // Finished products (prices per prototype mockup: ₱250, ₱140, ₱150)
        $finishedProducts = [
            ['product_name' => 'Whole Lechon Manok', 'unit' => 'pc', 'price' => 250.00],
            ['product_name' => 'Half Lechon Manok',  'unit' => 'pc', 'price' => 140.00],
            ['product_name' => 'Liempo',              'unit' => 'pc', 'price' => 150.00],
        ];

        $finishedQty = [60, 60, 100];

        foreach ($finishedProducts as $i => $data) {
            $product = Product::create(array_merge($data, ['category' => 'finished']));
            Inventory::create(['product_id' => $product->id, 'quantity_on_hand' => $finishedQty[$i]]);
        }
    }
}
