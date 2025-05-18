<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{

    /**
     * The name of the "table" associated with the model.
     *
     * @var list<string>
     */
    protected $table = 'transacoes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'remetente_id',
        'destinatario_id',
        'valor',
        'tipo',
        'revertida',
    ];

    /**
     * Defines the "belongs to" relationship with the sender user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function remetente()
    {
        return $this->belongsTo(User::class, 'remetente_id');
    }

    /**
     * Defines the "belongs to" relationship with the recipient user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function destinatario()
    {
        return $this->belongsTo(User::class, 'destinatario_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'revertida' => 'boolean',
            'destinatario_id' => 'integer',
            'remetente_id' => 'integer',
            'tipo' => 'string',
        ];
    }
}
