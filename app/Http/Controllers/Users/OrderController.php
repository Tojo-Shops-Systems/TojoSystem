<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\Ticket;
use Illuminate\Support\Facades\Validator;
use App\Events\NewWebOrder;

class OrderController extends Controller
{
    public function store(Request $request){
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer',
            'totalAmount' => 'required|numeric',
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ticket = Ticket::create([
            'items' => $request->items,
            'totalAmount' => $request->totalAmount,
            'customer' => $customer,
            'cashier' => null,
            'status' => 'pending',
            'branch_id' => $request->branch_id, 
            'date' => now()
        ]);

        event(new NewWebOrder($ticket));

        return response()->json([
            'message' => 'Pedido (Ticket) creado con éxito. Esperando preparación.',
            'ticket' => $ticket
        ], 201);
    }
}
