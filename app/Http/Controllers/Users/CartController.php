<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\Cart;

class CartController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'cartID' => 'required|string|unique:carts,cartID',
            'items' => 'required|array',
            'items.*.productID' => 'required|string',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer',
            'customer' => 'required|array',
        ]);

        $cartData = [
            'cartID' => $request->input('cartID'),
            'items' => $request->input('items'),
            'customer' => $request->input('customer'),
            'date' => now()
        ];

        $cart = Cart::create($cartData);

        return response()->json($cart, 201);
    }
}
