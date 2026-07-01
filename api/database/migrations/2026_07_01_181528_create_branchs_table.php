<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de los 44 municipios de El Salvador (Formato creado para prueba))
     * coinciden exactamente con docs/ddl/schema.sql.
     */
    private const MUNICIPALITY_CODES = [
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
    ];

    public function up(): void
    {
        Schema::create('branchs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enterprise_id');
            $table->string('name', 150);
            $table->string('address', 255);
            $table->string('municipality_codigo', 10);
            $table->string('phone', 20)->nullable();
            $table->string('branchs_status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('enterprise_id', 'idx_branchs_enterprise_id');
            $table->index('branchs_status', 'idx_branchs_status');
            $table->index('municipality_codigo', 'idx_branchs_municipality_codigo');
            $table->index(['enterprise_id', 'municipality_codigo'], 'idx_branchs_enterprise_municipality');

            $table->foreign('enterprise_id', 'fk_branchs_enterprise')
                ->references('id')->on('enterprises')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        DB::statement("
            ALTER TABLE branchs
            ADD CONSTRAINT chk_branchs_status
            CHECK (branchs_status IN ('active', 'inactive', 'suspended'))
        ");

        $codes = implode(',', array_map(
            fn (string $code) => "'{$code}'",
            self::MUNICIPALITY_CODES
        ));

        DB::statement("
            ALTER TABLE branchs
            ADD CONSTRAINT chk_branchs_municipality_codigo
            CHECK (municipality_codigo IN ({$codes}))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('branchs');
    }
};
