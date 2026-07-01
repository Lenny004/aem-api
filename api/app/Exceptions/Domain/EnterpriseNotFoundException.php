<?php

namespace App\Exceptions\Domain;

class EnterpriseNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("No se encontró la empresa asociada con id {$id}.", 404);
    }
}
