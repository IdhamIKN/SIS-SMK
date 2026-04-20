<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gtks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('kd_guru', 10)->unique();
            $table->string('nip')->nullable();
            $table->string('nuptk')->nullable();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('no_hp')->nullable();
            $table->string('foto')->nullable();
            $table->string('mata_pelajaran')->nullable();
            $table->string('jabatan');
            $table->boolean('status_aktif')->default(true);

            // Permission fields
            $table->boolean('acc_absen')->default(false);
            $table->boolean('acc_kurikulum')->default(false);
            $table->boolean('acc_jurnal')->default(false);
            $table->boolean('acc_bk')->default(false);
            $table->boolean('guru_piket')->default(false);
            $table->boolean('acc_profil')->default(false);
            $table->boolean('group_acc')->default(false);
            $table->enum('view_siswa', ['limit', 'full'])->default('limit');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gtks');
    }
};
