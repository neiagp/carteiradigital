<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransacaoRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Transacao;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransacaoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Realiza um depósito na conta do usuário autenticado.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function depositar(TransacaoRequest $request)
    {
        $user = $request->user();

        Log::info("Depósito realizado", [
            'usuario_id' => $user->id,
            'valor' => $request->valor,
        ]);

        DB::transaction(function () use ($user, $request) {

            Transacao::create([
                'remetente_id' => $user->id,
                'destinatario_id' => $user->id,
                'valor' => $request->valor,
                'tipo' => 'deposito',
            ]);
        });

        return response()->json(['mensagem' => 'Depósito realizado com sucesso.']);
    }

    /**
     * Realiza uma transferência entre usuários.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transferir(TransacaoRequest $request)
    {
        $remetente = $request->user();
        $destinatario = User::find($request->destinatario_id);

        if ($remetente->id === $destinatario->id) {
            return response()->json(['erro' => 'Não é possível transferir para si mesmo.'], 422);
        }

        $saldoAtual = $this->calcularSaldoPorId($remetente->id);
        if ($saldoAtual < $request->valor) {
            return response()->json(['erro' => 'Saldo insuficiente. Realize um novo depósito.'], 422);
        }

        Log::info("Transferência realizada", [
            'de' => $remetente->id,
            'para' => $destinatario->id,
            'valor' => $request->valor,
        ]);

        DB::transaction(function () use ($remetente, $destinatario, $request) {

            Transacao::create([
                'remetente_id' => $remetente->id,
                'destinatario_id' => $destinatario->id,
                'valor' => $request->valor,
                'tipo' => 'transferencia',
            ]);
        });

        return response()->json(['mensagem' => 'Transferência realizada com sucesso.']);
    }

    /**
     * Reverte uma transação.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reverter($id)
    {
        $user = auth()->user();
        $transacao = Transacao::findOrFail($id);

        $this->authorize('reverter', $transacao);

        if ($transacao->revertida) {
            return response()->json(['erro' => 'Transação já foi revertida. Atualize a tela.'], 422);
        }

        Log::warning("Transação revertida", [
            'transacao_id' => $transacao->id,
            'revertida_por' => $user->id,
        ]);

        DB::transaction(function () use ($transacao) {
            $transacao->update(['revertida' => true]);
        });

        return response()->json(['mensagem' => 'Transação revertida com sucesso.']);
    }

    /**
     * Calcula o saldo do usuário.
     *
     * @param int $userId
     * @return float
     */
    public function calcularSaldoPorId(int $userId): float
    {
        $creditos = Transacao::where('destinatario_id', $userId)
            ->where('tipo', 'deposito')
            ->where('revertida', false)
            ->sum('valor');

        $debitos = Transacao::where('remetente_id', $userId)
            ->where('tipo', 'transferencia')
            ->where('revertida', false)
            ->sum('valor');

        return $creditos - $debitos;
    }

}
