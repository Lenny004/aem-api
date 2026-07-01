-- =====================================================================
-- DDL de referencia — API de Gestión de Infraestructura Comercial (AEM)
-- Diseñado desde cero (la cátedra no entregó un script DDL base).
-- Jerarquía: companys (1) -> enterprises (N) -> branchs (N)
-- =====================================================================

-- ---------------------------------------------------------------------
-- Tabla: companys (Holding / Consorcio)
-- ---------------------------------------------------------------------
CREATE TABLE companys (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    doc_number VARCHAR(20) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(20) NULL,
    companys_status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT now(),
    updated_at TIMESTAMP NOT NULL DEFAULT now(),
    deleted_at TIMESTAMP NULL,

    CONSTRAINT uq_companys_doc_number UNIQUE (doc_number),
    CONSTRAINT chk_companys_status CHECK (companys_status IN ('active', 'inactive'))
);

COMMENT ON TABLE companys IS 'Holding / Consorcio: entidad jurídica corporativa principal.';
COMMENT ON COLUMN companys.doc_number IS 'Identificador fiscal/registro único del Holding.';

-- ---------------------------------------------------------------------
-- Tabla: enterprises (Empresas Asociadas)
-- ---------------------------------------------------------------------
CREATE TABLE enterprises (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    doc_number VARCHAR(20) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(20) NULL,
    enterprises_status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT now(),
    updated_at TIMESTAMP NOT NULL DEFAULT now(),
    deleted_at TIMESTAMP NULL,

    CONSTRAINT uq_enterprises_doc_number UNIQUE (doc_number),
    CONSTRAINT chk_enterprises_status CHECK (enterprises_status IN ('active', 'inactive')),

    CONSTRAINT fk_enterprises_company
        FOREIGN KEY (company_id) REFERENCES companys (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

COMMENT ON TABLE enterprises IS 'Empresas Asociadas: unidades de negocio o marcas que pertenecen a un Holding.';

CREATE INDEX idx_enterprises_company_id ON enterprises (company_id);
CREATE INDEX idx_enterprises_status ON enterprises (enterprises_status);

-- ---------------------------------------------------------------------
-- Tabla: branchs (Sucursales)
-- ---------------------------------------------------------------------
CREATE TABLE branchs (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    enterprise_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    address VARCHAR(255) NOT NULL,
    municipality_codigo VARCHAR(10) NOT NULL,
    phone VARCHAR(20),
    branchs_status VARCHAR(20)  NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT now(),
    updated_at TIMESTAMP NOT NULL DEFAULT now(),
    deleted_at TIMESTAMP,

    CONSTRAINT chk_branchs_status CHECK (branchs_status IN ('active', 'inactive')),

    -- Catálogo de los 44 municipios de El Salvador
    CONSTRAINT chk_branchs_municipality_codigo CHECK (municipality_codigo IN (
        'AH-01','AH-02','AH-03', -- Ahuachapán
        'CA-01','CA-02', -- Cabañas
        'CH-01','CH-02','CH-03', -- Chalatenango
        'CU-01','CU-02', -- Cuscatlán
        'LL-01','LL-02','LL-03','LL-04','LL-05','LL-06', -- La Libertad
        'PA-01','PA-02','PA-03', -- La Paz
        'UN-01','UN-02', -- La Unión
        'MO-01','MO-02', -- Morazán
        'SM-01','SM-02','SM-03', -- San Miguel
        'SS-01','SS-02','SS-03','SS-04','SS-05', -- San Salvador
        'SV-01','SV-02', -- San Vicente
        'SA-01','SA-02','SA-03','SA-04', -- Santa Ana
        'SO-01','SO-02','SO-03','SO-04', -- Sonsonate
        'US-01','US-02','US-03' -- Usulután
    )),

    CONSTRAINT fk_branchs_enterprise
        FOREIGN KEY (enterprise_id) REFERENCES enterprises (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

COMMENT ON TABLE branchs IS 'Sucursales: puntos físicos de recolección de paquetes u operación, asignados a una enterprise.';
COMMENT ON COLUMN branchs.municipality_codigo IS 'Código de municipio (catálogo de 44 municipios, reforma territorial 2024).';

CREATE INDEX idx_branchs_enterprise_id ON branchs (enterprise_id);
CREATE INDEX idx_branchs_status ON branchs (branchs_status);
CREATE INDEX idx_branchs_municipality_codigo ON branchs (municipality_codigo);
CREATE INDEX idx_branchs_enterprise_municipality ON branchs (enterprise_id, municipality_codigo);

-- ---------------------------------------------------------------------
-- Índice de companys_status (companys no tiene FK propia, se agrega al final por orden de lectura)
-- ---------------------------------------------------------------------
CREATE INDEX idx_companys_status ON companys (companys_status);
