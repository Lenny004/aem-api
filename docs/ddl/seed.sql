-- =====================================================================
-- Datos de prueba mínimos para validar el esquema de forma manual.
-- Jerarquía: 2 companys -> 3 enterprises -> 5 branchs
-- =====================================================================

INSERT INTO companys (name, doc_number, email, phone) VALUES
    ('Grupo Logístico Cuscatlán', 'CO-0001', 'contacto@cuscatlan-holding.sv', '2200-1000'),
    ('Consorcio Pacífico', 'CO-0002', 'contacto@pacifico-holding.sv', '2200-2000');

INSERT INTO enterprises (company_id, name, doc_number, email, phone) VALUES
    (1, 'Envíos Rápidos SA', 'EN-0001', 'contacto@enviosrapidos.sv', '2200-1100'),
    (1, 'Paquetería Express', 'EN-0002', 'contacto@paqueteriaexpress.sv', '2200-1200'),
    (2, 'Distribuidora del Pacífico', 'EN-0003', 'contacto@distripacifico.sv', '2200-2100');

INSERT INTO branchs (enterprise_id, name, address, municipality_codigo, phone) VALUES
    (1, 'Sucursal San Salvador Centro', 'Calle Arce, San Salvador', 'SS-04', '2200-1101'),
    (1, 'Sucursal Soyapango',          'Blvd. del Ejército, Soyapango', 'SS-03', '2200-1102'),
    (2, 'Sucursal Santa Ana',          'Av. Independencia, Santa Ana', 'SA-02', '2200-1201'),
    (3, 'Sucursal La Libertad',        'Calle El Pedregal, La Libertad', 'LL-06', '2200-2101'),
    (3, 'Sucursal Sonsonate',          'Calle Principal, Sonsonate', 'SO-02', '2200-2102');
