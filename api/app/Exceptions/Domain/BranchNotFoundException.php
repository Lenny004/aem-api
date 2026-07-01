<?php

namespace App\Exceptions\Domain;

class BranchNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("No se encontró la sucursal con id {$id}.", 404);
    }
}
