<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\Ticket;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User\Cart;
use Illuminate\Support\Facades\Http;

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

    public function purchaseInWeb(Request $request)
    {
        /* EXPECTED DATA
        {
            "result": true,
            "msg": "Carrito obtenido exitosamente",
            "data": {
                "customer": 2, // Customer ID
                "items": [...],
                "id": "693795822f761beedb07f402" // Cart ID
            },
            "branch_id": "..." // Expected in request
        }
        */

        $validator = Validator::make($request->all(), [
            'data.id' => 'required',
            'data.customer' => 'required',
            'data.items' => 'required|array',
            'branch_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'msg' => 'Datos incompletos para procesar la compra.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->input('data');
        $branchId = $request->input('branch_id');

        // Lookup Customer
        $customerModel = \App\Models\User\Customer::find($data['customer']);
        $customerData = [
            'id' => $data['customer'],
            'name' => $customerModel ? $customerModel->name : 'N/A'
        ];

        // Generate Ticket ID
        $ticketID = sprintf('TCK-WEB-%s', strtoupper(Str::random(10)));

        // Create Ticket
        $ticket = Ticket::create([
            'ticketID' => $ticketID,
            'items' => $data['items'],
            'totalAmount' => collect($data['items'])->sum(function($item) {
                return $item['price'] * $item['quantity'];
            }),
            'cashier' => 'Website', // Or specific system user
            'customer' => $customerData,
            'status' => 'Pending',
            'branch_id' => $branchId,
            'date' => now()
        ]);

        // Delete Cart
        Cart::destroy($data['id']);

        // Notification to external API
        try {
            Http::post('http://142.93.28.165:3000/api/notify', [
                'targetId' => $branchId,
                'data' => [
                    'msg' => $ticket,
                    'total' => $ticket->totalAmount
                ]
            ]);
        } catch (\Exception $e) {
            // Log error or ignore as it shouldn't block the response
            // Log::error('Notification API failed: ' . $e->getMessage());
        }

        return response()->json([
            'result' => true,
            'msg' => 'Compra web procesada correctamente. Ticket generado.',
            'data' => $ticket
        ], 201);
    }
}
