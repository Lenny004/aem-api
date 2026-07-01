<?php

namespace App\Exceptions\Domain;

class EnterpriseHasActiveBranchsException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            "La empresa asociada con id {$id} tiene sucursales activas; desactívalas o elimínalas antes de eliminar la empresa.",
            409
        );
    }
}
