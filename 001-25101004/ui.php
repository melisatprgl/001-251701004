<?php
// ARAYÜZ — DEĞİŞTİRMEYİN. Durum, yapılan hamlelerin baştan tekrar oynatılmasıyla hesaplanır.
session_start();
$KEY = 'kk_' . ($GIZLI ? 'kutu' : 'ogr');
$_SESSION[$KEY] ??= ['no' => 0, 'ofs' => 0, 'h' => []];
$S = &$_SESSION[$KEY];

function calistir(int $no, int $ofs, array $h, bool $gizli): array {
    $s = baslangic($no); $s['musteri_sira'] = $ofs;
    foreach ($h as $a) $s = $gizli ? gizli_adim($s, $a) : adim($s, $a);
    return $s;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['a'] ?? '';
    if ($a === 'basla') { $S = ['no' => ogrNo($_POST['no'] ?? ''), 'ofs' => max(0, min(999, (int)($_POST['ofs'] ?? 0))), 'h' => []]; }
    elseif ($a === 'geri') { array_pop($S['h']); }
    elseif ($a === 'dizi') {
        $S['h'] = array_values(array_filter(array_map('trim', explode(',', strtoupper(str_replace(' ', '', $_POST['dizi'] ?? '')))), fn($v) => $v !== ''));
        $S['h'] = array_map(fn($v) => preg_replace_callback('/^T-(\w+)-(\d+)$/', fn($m) => 'T-' . strtolower($m[1]) . '-' . $m[2], $v), $S['h']);
    }
    elseif ($a === 'M' || $a === 'G') { $S['h'][] = $a; }
    elseif ($a === 'T') { $S['h'][] = 'T-' . ($_POST['u'] ?? '') . '-' . (int)($_POST['adet'] ?? 1); }
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?')); exit;
}
$s = $S['no'] ? calistir($S['no'], $S['ofs'] ?? 0, $S['h'], $GIZLI) : null;
?><!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $GIZLI ? 'KARA KUTU' : 'BENİM MOTORUM' ?> · Kantinde Kaos</title>
<style>
body{margin:0;background:<?= $GIZLI ? '#0b0f17' : '#0d1a14' ?>;color:#e9f0f7;font:15px/1.45 system-ui,Segoe UI,sans-serif}
.w{max-width:1100px;margin:0 auto;padding:18px}h1{margin:0 0 4px;font-size:24px}.tag{display:inline-block;padding:3px 10px;border-radius:8px;font-weight:800;background:<?= $GIZLI ? '#ff6f78' : '#57d69b' ?>;color:#111}
.p{background:#ffffff0d;border:1px solid #ffffff22;border-radius:12px;padding:14px;margin:12px 0}.g{display:grid;grid-template-columns:1fr 1fr;gap:12px}
button,input,select{font:inherit;border-radius:8px;border:1px solid #ffffff33;background:#ffffff14;color:#fff;padding:8px 12px}button{cursor:pointer;font-weight:700;background:#43d9d0;color:#062}
.st{display:grid;grid-template-columns:repeat(5,1fr);gap:8px}.st div{background:#ffffff0d;border-radius:10px;padding:10px}.st b{display:block;font-size:22px}
table{width:100%;border-collapse:collapse}td,th{padding:6px;border-bottom:1px solid #ffffff1f;text-align:left}.log div{padding:4px 0;border-bottom:1px dashed #ffffff1a;font-size:14px}
code{background:#ffffff14;padding:2px 6px;border-radius:5px;word-break:break-all}@media(max-width:800px){.g,.st{grid-template-columns:1fr 1fr}}
</style></head><body><div class="w">
<h1>Kantinde Kaos <span class="tag"><?= $GIZLI ? 'KARA KUTU' : 'BENİM MOTORUM' ?></span></h1>
<form method="post" class="p"><input name="no" placeholder="Öğrenci numaran" value="<?= $S['no'] ?: '' ?>"> <input name="ofs" type="number" min="0" max="999" style="width:110px" title="Deneme turu: 0 = asıl oyun. Başka sayı = aynı kurallar, farklı müşteriler" value="<?= (int)($S['ofs'] ?? 0) ?>"> <button name="a" value="basla">Başla / Sıfırla</button>
<?php if ($s): ?> <small>Oyun no: <?= $S['no'] ?> · Deneme turu: <?= (int)($S['ofs'] ?? 0) ?></small><?php endif ?></form>
<?php if ($s): ?>
<div class="st"><div>Gün<b><?= $s['gun'] ?>/5</b></div><div>Bugünkü müşteri<b><?= $s['gunluk_musteri'] ?>/5</b></div><div>Kasa<b><?= $s['kasa'] ?> TL</b></div><div>Memnuniyet<b><?= $s['memnuniyet'] ?></b></div><div>Durum<b><?= $s['bitti'] ? ($s['kazandi'] ? '🏆 Kazandı' : '🧾 Bitti') : 'Devam' ?></b></div></div>
<div class="g"><div class="p"><table><tr><th>Ürün</th><th>Fiyat</th><th>Stok</th></tr>
<?php foreach ($s['urunler'] as $u): ?><tr><td><?= $u['ad'] ?></td><td><?= $u['fiyat'] ?> TL</td><td><?= $u['stok'] ?></td></tr><?php endforeach ?></table>
<form method="post" style="margin-top:10px;display:flex;gap:6px;flex-wrap:wrap"><button name="a" value="M">+ Müşteri</button><button name="a" value="G">🌙 Günü kapat</button><button name="a" value="geri" style="background:#ffad58">↶ Geri al</button></form>
<form method="post" style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap"><select name="u"><?php foreach ($s['urunler'] as $k => $u): ?><option value="<?= $k ?>"><?= $u['ad'] ?></option><?php endforeach ?></select>
<select name="adet"><?php for ($q = 1; $q <= 10; $q++): ?><option><?= $q ?></option><?php endfor ?></select><button name="a" value="T">🚚 Tedarik et</button></form></div>
<div class="p log"><b>Günlük</b><?php foreach (array_reverse(array_slice($s['log'], -18)) as $l): ?><div><?= htmlspecialchars($l) ?></div><?php endforeach ?></div></div>
<div class="p"><b>Hamle dizisi</b> (kopyala, diğer ekrana yapıştır):<br><code><?= htmlspecialchars(implode(',', $S['h'])) ?: '—' ?></code>
<form method="post" style="margin-top:8px;display:flex;gap:6px"><input name="dizi" style="flex:1" placeholder="M,M,T-tost-5,M,..."><button name="a" value="dizi">Diziyi oynat</button></form></div>
<?php endif ?></div></body></html>
