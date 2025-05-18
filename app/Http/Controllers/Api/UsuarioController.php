<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{

    /**
     * Lista os usuários cadastrados, exceto o usuário autenticado.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listar(Request $request)
    {
        $user = $request->user();

        $usuarios = User::query()
            ->where('id', '!=', $user->id)
            ->when($request->has('q'), fn ($q) =>
                $q->where(function ($s) use ($request) {
                    $s->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('email', 'like', '%' . $request->q . '%');
                })
            )
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($usuarios);
    }
}
