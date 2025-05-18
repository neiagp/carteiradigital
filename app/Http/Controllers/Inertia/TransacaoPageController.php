<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Models\Transacao;
use Inertia\Inertia;

class TransacaoPageController extends Controller
{

    /**
     * Exibe a página de transações do usuário autenticado.
     *
     * @return \Inertia\Response
     */
    public function transacoes() {
        $user = auth()->user();
        return Inertia::render('Transacoes/Transacoes', [
            'transacoes' => Transacao::with(['remetente','destinatario'])
                ->where(fn($q)=>$q->where('remetente_id',$user->id)
                                   ->orWhere('destinatario_id',$user->id))
                ->latest()->get(),
        ]);
    }

    /**
     * Exibe a página de transações do usuário autenticado.
     *
     * @return \Inertia\Response
     */
    public function dashboard() {
        $user = auth()->user();
        error_log('dados '.$user->id);
        return Inertia::render('Dashboard', [
            'transacoes' => Transacao::with(['remetente','destinatario'])
                ->where(fn($q)=>$q->where('remetente_id',$user->id)
                                   ->orWhere('destinatario_id',$user->id))
                ->latest()->get(),
        ]);
    }
    
}
