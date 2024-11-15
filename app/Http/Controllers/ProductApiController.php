<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends ApiBaseController
{
    public function index()
    {
        $products = Product::all();
        return $this->sendSuccess($products, "Products fetched");
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image_url' => 'nullable|url',
            'affiliate_link' => 'required|url',
        ]);

        $product = Product::create($validatedData);

        return $this->sendSuccess($product, "created", 201);
    }

    public function show(Product $product)
    {
        return $this->sendSuccess($product, "product fetched");
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'image_url' => 'nullable|url',
            'affiliate_link' => 'sometimes|url',
        ]);

        $product->update($validatedData);

        return $this->sendSuccess($product, "updated");
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return $this->sendSuccess(null, "Product deleted");
    }
}
