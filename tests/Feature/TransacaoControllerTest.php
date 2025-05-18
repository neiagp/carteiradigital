<?php

namespace Tests\Feature;

use App\Models\Transacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransacaoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_depositar()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/depositar', [
            'valor' => 100.00,
        ]);

        $response->assertStatus(200)
            ->assertJson(['mensagem' => 'Depósito realizado com sucesso.']);

        $this->assertDatabaseHas('transacaos', [
            'remetente_id' => $user->id,
            'destinatario_id' => $user->id,
            'valor' => 100.00,
            'tipo' => 'deposito',
        ]);
    }

    public function test_transferir()
    {
        $remetente = User::factory()->create();
        $destinatario = User::factory()->create();
        $this->actingAs($remetente);

        // Simula saldo inicial
        Transacao::factory()->create([
            'remetente_id' => $remetente->id,
            'destinatario_id' => $remetente->id,
            'valor' => 200.00,
            'tipo' => 'deposito',
        ]);

        $response = $this->postJson('/api/transferir', [
            'destinatario_id' => $destinatario->id,
            'valor' => 50.00,
        ]);

        $response->assertStatus(200)
            ->assertJson(['mensagem' => 'Transferência realizada com sucesso.']);

        $this->assertDatabaseHas('transacaos', [
            'remetente_id' => $remetente->id,
            'destinatario_id' => $destinatario->id,
            'valor' => 50.00,
            'tipo' => 'transferencia',
        ]);
    }

    public function test_reverter()
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

        $response = $this->postJson("/api/reverter/{$transacao->id}");

        $response->assertStatus(200)
            ->assertJson(['mensagem' => 'Transação revertida com sucesso.']);

        $this->assertDatabaseHas('transacaos', [
            'id' => $transacao->id,
            'revertida' => true,
        ]);
    }

    public function test_calcular_saldo_por_id()
    {
        $user = User::factory()->create();

        // Simula transações
        Transacao::factory()->create([
            'remetente_id' => $user->id,
            'destinatario_id' => $user->id,
            'valor' => 200.00,
            'tipo' => 'deposito',
        ]);

        Transacao::factory()->create([
            'remetente_id' => $user->id,
            'destinatario_id' => User::factory()->create()->id,
            'valor' => 50.00,
            'tipo' => 'transferencia',
        ]);

        $saldo = app('App\Http\Controllers\Api\TransacaoController')->calcularSaldoPorId($user->id);

        $this->assertEquals(150.00, $saldo);
    }
}
