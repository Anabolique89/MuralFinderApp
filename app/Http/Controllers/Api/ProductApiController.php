<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductApiController extends ApiBaseController
{
    public function index()
    {
        $products = Product::all();
        return $this->sendSuccess($products, "Products fetched");
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image_url' => 'nullable|url',
            'affiliate_link' => 'required|url',
            'category' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $product = Product::create($request->all());

        return $this->sendSuccess($product, "created", 201);
    }

    public function show(Product $product)
    {
        return $this->sendSuccess($product, "product fetched");
    }

    public function update(Request $request, Product $product)
    {

        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image_url' => 'nullable|url',
            'affiliate_link' => 'required|url',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }



        $product->update($request->all());

        return $this->sendSuccess($product, "updated");
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return $this->sendSuccess(null, "Product deleted");
    }
}
