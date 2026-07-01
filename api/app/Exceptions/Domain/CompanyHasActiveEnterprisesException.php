<?php

namespace App\Exceptions\Domain;

class CompanyHasActiveEnterprisesException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            "La compañía con id {$id} tiene empresas asociadas activas; desactívalas o elimínalas antes de eliminar la compañía.",
            409
        );
    }
}
