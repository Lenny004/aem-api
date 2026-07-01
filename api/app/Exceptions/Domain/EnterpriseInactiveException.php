<?php

namespace App\Exceptions\Domain;

class EnterpriseInactiveException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            "La empresa asociada con id {$id} está inactiva; no se pueden crear sucursales nuevas bajo ella.",
            409
        );
    }
}
