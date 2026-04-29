<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('inventory')->orderBy('product_name')->get();

        $totalProducts = $products->count();
        $rawCount      = $products->where('category', 'raw')->count();
        $finishedCount = $products->where('category', 'finished')->count();

        return view('products.index', compact('products', 'totalProducts', 'rawCount', 'finishedCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:100',
            'category'     => 'required|in:raw,finished',
            'unit'         => 'required|string|max:20',
            'price'        => 'required|numeric|min:0',
        ]);

        $product = Product::create($request->only(['product_name', 'category', 'unit', 'price']));

        Inventory::firstOrCreate(
            ['product_id' => $product->id],
            ['quantity_on_hand' => 0, 'border_point' => 10]
        );

        return redirect()->route('products.index')->with('success', "Product \"{$product->product_name}\" added!");
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_name' => 'required|string|max:100',
            'category'     => 'required|in:raw,finished',
            'unit'         => 'required|string|max:20',
            'price'        => 'required|numeric|min:0',
        ]);

        $product->update($request->only(['product_name', 'category', 'unit', 'price']));

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }
}
