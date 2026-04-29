<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('stockIns')->orderBy('supplier_name')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name'  => 'required|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'address'        => 'nullable|string',
        ]);

        Supplier::create($request->only(['supplier_name', 'contact_number', 'address']));

        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully!');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'supplier_name'  => 'required|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'address'        => 'nullable|string',
        ]);

        $supplier->update($request->only(['supplier_name', 'contact_number', 'address']));

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }
}
