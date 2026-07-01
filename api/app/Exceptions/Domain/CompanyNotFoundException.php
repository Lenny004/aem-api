<?php

namespace App\Exceptions\Domain;

class CompanyNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("No se encontró la compañía con id {$id}.", 404);
    }
}

