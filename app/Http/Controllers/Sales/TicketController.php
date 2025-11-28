<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\Ticket;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function purchaseInShop(Request $request)
    {
        /* EXPECTED DATA
        {
            "items": [
                {
                    "name": "Coca de 600",
                    "price": 22.00,
                    "quantity": 50
                },
                {
                    "name": "Chetos",
                    "price": 20.00,
                    "quantity": 1
                }
            ],
            "totalAmount": 44.00,
            "customer": null
        }
        */
        $user = $request->user();

        $purchaseInShop = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer',
            'totalAmount' => 'required|numeric',
            'customer' => 'nullable|array',
        ]);

        if ($purchaseInShop->fails()) {
            return response()->json([
                'result' => false,
                'msg' => 'Error de validación.',
                'data' => $purchaseInShop->errors()
            ], 422);
        }

        $person = $user->person;
        $branch = $user->branch;

        $cashierData = [
            'firstName' => $person?->firstName ?? 'N/A',
            'lastName' => $person?->lastName ?? 'N/A',
            'employeeID' => $user?->id,
            'branch' => $branch?->branchName ?? 'Sucursal no asignada',
        ];

        $ticketID = sprintf('TCK-%s', strtoupper(Str::random(10)));

        $ticketData = [
            'ticketID' => $ticketID,
            'items' => $request->input('items'),
            'totalAmount' => (float) $request->input('totalAmount'),
            'cashier' => $cashierData,
            'customer' => $request->input('customer'),
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

    public function purchaseInWeb (Request $request){}
}
