<?php

namespace App\Exceptions\Domain;

class CompanyInactiveException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            "La compañía con id {$id} está inactiva; no se pueden crear empresas asociadas nuevas bajo ella.",
            409
        );
    }
}
