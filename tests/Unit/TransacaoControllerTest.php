<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\TransacaoController;
use App\Models\Transacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransacaoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_calcular_saldo_por_id()
    {
        $user = User::factory()->create();

        // Simula transações
        Transacao::factory()->create([
            'remetente_id' => $user->id,
            'destinatario_id' => $user->id,
            'valor' => 200.00,
            'tipo' => 'deposito',
            'revertida' => false,
        ]);

        Transacao::factory()->create([
            'remetente_id' => $user->id,
            'destinatario_id' => User::factory()->create()->id,
            'valor' => 50.00,
            'tipo' => 'transferencia',
            'revertida' => false,
        ]);

        $controller = new TransacaoController();
        $saldo = $controller->calcularSaldoPorId($user->id);

        $this->assertEquals(150.00, $saldo);
    }

    public function test_reverter_transacao()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $transacao = Transacao::factory()->create([
            'remetente_id' => $user->id,
            'destinatario_id' => $user->id,
            'valor' => 100.00,
            'tipo' => 'deposito',
            'revertida' => false,
        ]);

        $controller = new TransacaoController();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) use ($transacao) {
                $callback();
                $transacao->update(['revertida' => true]);
            });

        $response = $controller->reverter($transacao->id);

        $this->assertTrue($transacao->fresh()->revertida);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Transação revertida com sucesso.', $response->getData()->mensagem);
    }
}
