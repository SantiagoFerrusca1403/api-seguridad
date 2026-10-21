<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\User;

class DocumentoPolicy
{
    public function view(User $user, Documento $documento): bool
    {
        return $documento->user_id === $user->id;
    }
}
