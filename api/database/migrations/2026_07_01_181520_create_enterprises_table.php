<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enterprises', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 150);
            $table->string('doc_number', 20);
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('enterprises_status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('doc_number', 'uq_enterprises_doc_number');
            $table->index('enterprises_status', 'idx_enterprises_status');
            $table->index('company_id', 'idx_enterprises_company_id');

            $table->foreign('company_id', 'fk_enterprises_company')
                ->references('id')->on('companys')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        DB::statement("
            ALTER TABLE enterprises
            ADD CONSTRAINT chk_enterprises_status
            CHECK (enterprises_status IN ('active', 'inactive'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enterprises');
    }
};
