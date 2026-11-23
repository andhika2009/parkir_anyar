<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_hitung_biaya_keluar');

        DB::unprepared('
            CREATE TRIGGER trg_hitung_biaya_keluar
            BEFORE UPDATE ON tb_transaksi
            FOR EACH ROW
            BEGIN
                IF NEW.waktu_keluar IS NOT NULL
                   AND (OLD.waktu_keluar IS NULL OR NEW.waktu_keluar <> OLD.waktu_keluar) THEN

                    SET NEW.durasi_jam = GREATEST(1, CEIL(TIMESTAMPDIFF(MINUTE, NEW.waktu_masuk, NEW.waktu_keluar) / 60));

                    SET NEW.biaya_total = NEW.durasi_jam * (
                        SELECT tarif_per_jam FROM tb_tarif WHERE id_tarif = NEW.id_tarif
                    );
                END IF;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_hitung_biaya_keluar');
    }
};