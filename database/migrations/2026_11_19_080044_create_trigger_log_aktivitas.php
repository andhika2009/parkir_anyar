<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->down();

        DB::unprepared('
            CREATE TRIGGER trg_log_transaksi_masuk
            AFTER INSERT ON tb_transaksi
            FOR EACH ROW
            INSERT INTO log_aktivitas (user_id, aktivitas, ip_address, created_at, updated_at)
            VALUES (NEW.id_user, CONCAT("Kendaraan masuk (transaksi #", NEW.id_transaksi, ")"), "trigger", NOW(), NOW())
        ');

        DB::unprepared('
            CREATE TRIGGER trg_log_transaksi_keluar
            AFTER UPDATE ON tb_transaksi
            FOR EACH ROW
            BEGIN
                IF NEW.waktu_keluar IS NOT NULL AND OLD.waktu_keluar IS NULL THEN
                    INSERT INTO log_aktivitas (user_id, aktivitas, ip_address, created_at, updated_at)
                    VALUES (NEW.id_user, CONCAT("Kendaraan keluar (transaksi #", NEW.id_transaksi, "), biaya Rp", NEW.biaya_total), "trigger", NOW(), NOW());
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER trg_log_kendaraan_update
            AFTER UPDATE ON tb_kendaraan
            FOR EACH ROW
            INSERT INTO log_aktivitas (user_id, aktivitas, ip_address, created_at, updated_at)
            VALUES (NEW.id_user, CONCAT("Data kendaraan diubah: ", OLD.plat_nomer, " -> ", NEW.plat_nomer), "trigger", NOW(), NOW())
        ');

        DB::unprepared('
            CREATE TRIGGER trg_log_tarif_update
            AFTER UPDATE ON tb_tarif
            FOR EACH ROW
            INSERT INTO log_aktivitas (user_id, aktivitas, ip_address, created_at, updated_at)
            VALUES (NULL, CONCAT("Tarif ", NEW.jenis_kendaraan, " diubah: ", OLD.tarif_per_jam, " -> ", NEW.tarif_per_jam), "trigger", NOW(), NOW())
        ');

        DB::unprepared('
            CREATE TRIGGER trg_log_user_update
            AFTER UPDATE ON tb_users
            FOR EACH ROW
            INSERT INTO log_aktivitas (user_id, aktivitas, ip_address, created_at, updated_at)
            VALUES (NULL, CONCAT("Data user diubah: ", NEW.user_name), "trigger", NOW(), NOW())
        ');
    }

    public function down(): void
    {
        foreach ([
            'trg_log_transaksi_masuk',
            'trg_log_transaksi_keluar',
            'trg_log_kendaraan_update',
            'trg_log_tarif_update',
            'trg_log_user_update',
        ] as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS $trigger");
        }
    }
};