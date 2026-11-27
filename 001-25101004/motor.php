<?php
declare(strict_types=1);

function adim(array $s, string $a): array {
    if (!empty($s['bitti'])) {
        return $s;
    }

    $no = (int)($s['no'] ?? 0);
    $s['gun'] = (int)($s['gun'] ?? 1);
    $s['gunluk_musteri'] = (int)($s['gunluk_musteri'] ?? 0);
    $s['musteri_sira'] = (int)($s['musteri_sira'] ?? 0);
    $s['kasa'] = (int)($s['kasa'] ?? 1000);
    $s['memnuniyet'] = (int)($s['memnuniyet'] ?? 100);
    $s['seri'] = (int)($s['seri'] ?? 0);
    $s['gunluk_ret'] = (int)($s['gunluk_ret'] ?? 0);

    if (!is_array($s['urunler'] ?? null)) {
        $s['urunler'] = urunListesi();
    }

    $hamle = trim($a);

    if ($hamle === 'M') {
        $s['musteri_sira']++;
        $s['gunluk_musteri']++;

        $m = musteri($no, $s['musteri_sira']);
        $uKey = (string)($m['urun'] ?? '');
        $butce = (int)($m['butce'] ?? 0);

        if ($uKey !== '' && isset($s['urunler'][$uKey])) {
            $stok = (int)($s['urunler'][$uKey]['stok'] ?? 0);
            $fiyat = (int)($s['urunler'][$uKey]['fiyat'] ?? 0);

            if ($stok >= 1 && $butce >= $fiyat) {
                $s['urunler'][$uKey]['stok'] = $stok - 1;
                $s['kasa'] += $fiyat;
                $s['seri']++;
            } else {
                $s['seri'] = 0;
                $s['gunluk_ret']++;
            }
        }

        $o = olay($no, $s['musteri_sira']);
        if ($o === 'yogunluk') {
            $s['yogunluk'] = true;
        } elseif ($o === 'elektrik') {
            $s['elektrik'] = (int)($s['elektrik'] ?? 0) + 1;
        }

        return $s;
    }

    if (str_starts_with($hamle, 'T-')) {
        $parcalar = explode('-', $hamle);
        $uKey = strtolower((string)($parcalar[1] ?? ''));
        $adet = (int)($parcalar[2] ?? 0);

        if ($uKey !== '' && isset($s['urunler'][$uKey]) && $adet > 0) {
            $s['urunler'][$uKey]['stok'] = (int)($s['urunler'][$uKey]['stok'] ?? 0) + $adet;
        }

        return $s;
    }

    if ($hamle === 'G') {
        // Gün kapanış gideri ve bozulan/kalan stok düşümü (ilk gözlemimize göre)
        $s['kasa'] -= 60;
        if (isset($s['urunler']['tost'])) {
            $s['urunler']['tost']['stok'] = max(0, (int)$s['urunler']['tost']['stok'] - 3);
        }
        if (isset($s['urunler']['hamburger'])) {
            $s['urunler']['hamburger']['stok'] = max(0, (int)$s['urunler']['hamburger']['stok'] - 2);
        }

        $s['gun']++;
        $s['gunluk_musteri'] = 0;
        $s['gunluk_ret'] = 0;
        $s['yogunluk'] = false;

        if ($s['gun'] > 5) {
            $s['bitti'] = true;
            $s['kazandi'] = ($s['memnuniyet'] > 0 && $s['kasa'] >= 1000);
        }

        return $s;
    }

    return $s;
}
