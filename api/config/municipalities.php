<?php

// Catálogo oficial de 44 municipios de El Salvador (reforma territorial 2024).
// Debe coincidir con el CHECK de Postgres en create_branchs_table
// y con la validación Rule::in() de los Form Requests de Branch.
return [
    'codes' => [
        'AH-01', 'AH-02', 'AH-03',
        'CA-01', 'CA-02',
        'CH-01', 'CH-02', 'CH-03',
        'CU-01', 'CU-02',
        'LL-01', 'LL-02', 'LL-03', 'LL-04', 'LL-05', 'LL-06',
        'PA-01', 'PA-02', 'PA-03',
        'UN-01', 'UN-02',
        'MO-01', 'MO-02',
        'SM-01', 'SM-02', 'SM-03',
        'SS-01', 'SS-02', 'SS-03', 'SS-04', 'SS-05',
        'SV-01', 'SV-02',
        'SA-01', 'SA-02', 'SA-03', 'SA-04',
        'SO-01', 'SO-02', 'SO-03', 'SO-04',
        'US-01', 'US-02', 'US-03',
    ],
];
