<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\GabineteStatusUpdated;
use Symfony\Component\Process\Process;

class GabineteController extends Controller
{
    public function iniciarAperturaGabinete(Request $request) {
        
    }

    public function recibirNotificacionHardware(Request $request, $ticketId)
    {
        $status = $request->input('status');
        event(new GabineteStatusUpdated($ticketId, $status));
        return response()->json(['status' => 'aviso recibido']);
    }

    public function getOrdersPending (Request $request){
    }
}