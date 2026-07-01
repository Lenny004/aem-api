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
        Schema::create('companys', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('doc_number', 20);
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('companys_status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('doc_number', 'uq_companys_doc_number');
            $table->index('companys_status', 'idx_companys_status');
        });

        DB::statement("
            ALTER TABLE companys
            ADD CONSTRAINT chk_companys_status
            CHECK (companys_status IN ('active', 'inactive'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companys');
    }
};
