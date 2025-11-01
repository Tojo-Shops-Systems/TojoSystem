<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\Ticket;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'ticketID' => 'required|string|unique:tickets,ticketID',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer',
            'totalAmount' => 'required|numeric',
            'cashier' => 'nullable|array',
            'customer' => 'nullable|array',
        ]);

        $ticketData = [
            'ticketID' => $request->input('ticketID'),
            'items' => $request->input('items'),
            'totalAmount' => $request->input('totalAmount'),
            'cashier' => $request->input('cashier'), // Solo sera null si fue para recoger en tienda, sera hasta que coloque el producto en el gabinete
            'customer' => $request->input('customer'), // Será null si no viene, se podra llenar si es producto del pedido en linea para recoger en tienda
            'date' => now()
        ];

        $ticket = Ticket::create($ticketData);

        return response()->json($ticket, 201);
    }
}
