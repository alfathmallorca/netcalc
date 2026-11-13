<?php

namespace App\Support;

class NetworkLesson
{
    public static function explain(string $commands): array
    {
        $steps = [];

        foreach (explode("\n", $commands) as $command) {
            [$purpose, $check, $expected] = match (true) {
                $command === 'enable' => ['Masuk ke privileged EXEC untuk mengelola perangkat.', 'Perhatikan prompt CLI.', 'Prompt berubah dari > menjadi #. Password diminta jika sudah diatur.'],
                $command === 'configure terminal' => ['Masuk ke mode konfigurasi global.', 'Perhatikan prompt CLI.', 'Prompt menampilkan (config)#.'],
                str_starts_with($command, 'hostname ') => ['Mengganti nama perangkat agar mudah dikenali.', 'Perhatikan prompt CLI atau jalankan show running-config setelah end.', 'Nama pada prompt mengikuti hostname yang dimasukkan.'],
                str_starts_with($command, 'vlan ') => ['Membuat atau memilih VLAN untuk memisahkan jaringan secara logis.', 'Jalankan show vlan brief dari privileged EXEC.', 'VLAN yang dipilih muncul dalam daftar VLAN.'],
                str_starts_with($command, 'name ') => ['Memberi nama pada VLAN yang sedang dipilih.', 'Jalankan show vlan brief dari privileged EXEC.', 'Kolom Name menampilkan nama VLAN tersebut.'],
                str_starts_with($command, 'interface ') => ['Memilih interface yang akan dikonfigurasi. Nama port harus tersedia di perangkat.', 'Perhatikan prompt CLI.', 'Prompt menampilkan (config-if)# untuk interface yang dipilih.'],
                $command === 'switchport mode access' => ['Menetapkan port sebagai access untuk perangkat akhir, seperti PC.', 'Jalankan show interfaces switchport dari privileged EXEC.', 'Administrative Mode pada port terkait menampilkan static access.'],
                str_starts_with($command, 'switchport access vlan ') => ['Menempatkan port access ke VLAN yang dipilih.', 'Jalankan show vlan brief dari privileged EXEC.', 'Port access tercantum pada VLAN yang sesuai.'],
                $command === 'switchport mode trunk' => ['Menetapkan uplink sebagai trunk untuk membawa trafik VLAN antarperangkat.', 'Jalankan show interfaces trunk dari privileged EXEC.', 'Port muncul sebagai trunk ketika link dan konfigurasi kedua ujung mendukung trunking.'],
                str_starts_with($command, 'switchport trunk allowed vlan add ') => ['Menambahkan VLAN ke daftar VLAN yang diizinkan melewati trunk, tanpa menghapus daftar sebelumnya.', 'Jalankan show interfaces trunk dari privileged EXEC.', 'VLAN terdapat pada daftar allowed VLAN. VLAN juga perlu tersedia di kedua ujung trunk.'],
                $command === 'no shutdown' => ['Mengaktifkan interface secara administratif.', 'Router: show ip interface brief. Switch: show interfaces status.', 'Interface tidak lagi administratively down. Status up tetap memerlukan kabel dan perangkat lawan yang aktif.'],
                str_starts_with($command, 'ip address ') => ['Memberikan alamat IPv4 dan subnet mask pada interface router.', 'Jalankan show ip interface brief dari privileged EXEC.', 'IP-Address pada interface terkait sama dengan IP yang dikonfigurasi.'],
                str_starts_with($command, 'ip route ') => ['Menentukan next hop untuk mencapai jaringan tujuan melalui static route.', 'Jalankan show ip route dari privileged EXEC.', 'Route bertanda S (atau S* untuk default route) terlihat jika route terpasang dan next hop dapat dijangkau. Siapkan juga route balik.'],
                $command === 'exit' => ['Kembali dari konfigurasi VLAN atau interface ke konfigurasi global.', 'Perhatikan prompt CLI.', 'Prompt kembali menampilkan (config)#.'],
                $command === 'end' => ['Keluar dari mode konfigurasi dan kembali ke privileged EXEC.', 'Perhatikan prompt CLI.', 'Prompt kembali ke hostname diikuti #.'],
                $command === 'copy running-config startup-config' => ['Menyimpan konfigurasi aktif sebagai konfigurasi awal setelah perangkat dinyalakan ulang.', 'Tekan Enter saat diminta Destination filename, lalu jalankan show startup-config.', 'Proses penyimpanan selesai dan startup-config memuat perubahan.'],
                default => ['Tinjau perintah ini pada dokumentasi perangkat.', 'Gunakan bantuan ? pada CLI.', 'Sintaks sesuai model dan versi IOS.'],
            };

            $steps[] = compact('command', 'purpose', 'check', 'expected');
        }

        return $steps;
    }
}
