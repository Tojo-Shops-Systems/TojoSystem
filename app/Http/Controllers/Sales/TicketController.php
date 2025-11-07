<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\Ticket;
use App\Models\User\User;
use App\Models\User\Person;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function purchaseInShop(Request $request)
    {
        $purchaseInShop = Validator::make($request->all(), [
            'ticketID' => 'required|string|unique:tickets,ticketID',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer',
            'totalAmount' => 'required|numeric',
            'cashier' => 'required|array',
            'customer' => 'nullable|array',
        ]);

        if ($purchaseInShop->fails()) {
            return response()->json([
                'result' => false,
                'msg' => 'Error de validación.',
                'data' => $purchaseInShop->errors()
            ], 422);
        }

        $ticketData = [
            'ticketID' => $request->input('ticketID'),
            'items' => $request->input('items'),
            'totalAmount' => $request->input('totalAmount'),
            'cashier' => $request->input('cashier'), // Solo sera null si fue para recoger en tienda, sera hasta que coloque el producto en el gabinete
            'customer' => $request->input('customer'), // Será null si no viene, se podra llenar si es producto del pedido en linea para recoger en tienda
            'status' => 'Complete',
            'date' => now()
        ];

        $ticket = Ticket::create($ticketData);

        return response()->json([
            'result' => true,
            'msg' => 'Ticket registrado con éxito',
            'data' => $ticket
        ], 201);
    }
}
