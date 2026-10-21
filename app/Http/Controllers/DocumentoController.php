<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Support\Facades\Gate;

class DocumentoController extends Controller
{
    public function show(Documento $documento)
    {
        Gate::authorize('view', $documento);

        return response()->json([
            'id' => $documento->id,
            'titulo' => $documento->titulo,
            'contenido' => $documento->contenido,
        ]);
    }
}
