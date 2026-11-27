<?php
// ORTAK DOSYA — DEĞİŞTİRMEYİN. Müşteri ve olay üretimi öğrenci numarasına göre sabittir.
declare(strict_types=1);

function urunListesi(): array {
    return [
        'tost'      => ['ad' => 'Tost',      'fiyat' => 60,  'stok' => 6],
        'hamburger' => ['ad' => 'Hamburger', 'fiyat' => 100, 'stok' => 4],
        'ayran'     => ['ad' => 'Ayran',     'fiyat' => 20,  'stok' => 8],
        'cay'       => ['ad' => 'Çay',       'fiyat' => 15,  'stok' => 10],
        'kahve'     => ['ad' => 'Kahve',     'fiyat' => 45,  'stok' => 6],
        'su'        => ['ad' => 'Su',        'fiyat' => 10,  'stok' => 8],
    ];
}

// i. müşteri (1'den başlar). Aynı numara + aynı i = hep aynı müşteri.
function musteri(int $no, int $i): array {
    $profiller = [
        ['ad' => 'Ece',    'tip' => 'Öğrenci',        'min' => 25, 'max' => 100],
        ['ad' => 'Mert',   'tip' => 'Akademisyen',    'min' => 70, 'max' => 180],
        ['ad' => 'Zeynep', 'tip' => 'Personel',       'min' => 40, 'max' => 140],
        ['ad' => 'Can',    'tip' => 'Misafir',        'min' => 10, 'max' => 220],
        ['ad' => 'Deniz',  'tip' => 'Burslu Öğrenci', 'min' => 20, 'max' => 90],
    ];
    mt_srand($no * 7919 + $i * 104729);
    $p = $profiller[mt_rand(0, 4)];
    $butce = mt_rand($p['min'], $p['max']);
    $urunler = array_keys(urunListesi());
    $urun = $urunler[mt_rand(0, 5)];
    return ['ad' => $p['ad'], 'tip' => $p['tip'], 'butce' => $butce, 'urun' => $urun];
}

// i. müşteriden sonra olay: 'elektrik', 'yogunluk', 'tedarik', 'bahsis' ya da null
function olay(int $no, int $i): ?string {
    mt_srand($no * 31337 + $i * 7);
    $r = mt_rand(1, 100);
    if ($r <= 12) return 'elektrik';
    if ($r <= 27) return 'yogunluk';
    if ($r <= 37) return 'tedarik';
    if ($r <= 50) return 'bahsis';
    return null;
}

// Oyunun başlangıç durumu. Anahtar isimlerini DEĞİŞTİRMEYİN; kontrol programı bunlara bakar.
function baslangic(int $no): array {
    $u = urunListesi();
    return [
        'no' => $no, 'gun' => 1, 'gunluk_musteri' => 0, 'musteri_sira' => 0,
        'kasa' => 1000, 'memnuniyet' => 100,
        'urunler' => $u,
        'seri' => 0,                                  // ?
        'ret_sayac' => array_fill_keys(array_keys($u), 0), // ?
        'zam' => array_fill_keys(array_keys($u), false),   // ?
        'elektrik' => 0, 'yogunluk' => false, 'gunluk_ret' => 0,
        'bitti' => false, 'kazandi' => false,
        'log' => [],
    ];
}

function ogrNo(string $giris): int {
    $d = preg_replace('/\D/', '', $giris);
    return $d === '' ? 0 : (int)substr($d, -6);
}
