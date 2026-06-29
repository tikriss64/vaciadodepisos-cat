<?php
/**
 * enviar.php — Formulario de contacto VaciadoDePisos.cat
 * Gestiona todos los formularios del sitio (ciudad, idioma, premium).
 */

// ── CONFIGURACIÓN ────────────────────────────────────────────────────────────
define('DEST_EMAIL', 'vaciadodepisos@protonmail.com');
define('FROM_EMAIL', 'noreply@vaciadodepisos.cat');
define('SITE_NAME',  'VaciadoDePisos.cat');
define('BASE_URL',   'https://vaciadodepisos.cat/');
define('ERROR_URL',  'https://vaciadodepisos.cat/?error=1#contact');

// Páginas de éxito permitidas (whitelist contra open redirect)
const REDIRECTS_OK = [
    'gracias.html', 'gracies.html',
    'gracias-es.html', 'gracies-ca.html',
    'gracias-en.html', 'gracias-fr.html', 'gracias-de.html',
];
// ─────────────────────────────────────────────────────────────────────────────

// Modo dry-run para test automático — omite mail(), verifica solo validación y redirección.
// Solo activo con ?dryrun=test2026 (mismo key que test_formularios.php).
$dry_run = (($_GET['dryrun'] ?? '') === 'test2026');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

// Honeypot — PRESENCIA OBLIGATORIA.
// Los 23 formularios del sitio incluyen el campo _honey, así que un navegador real
// SIEMPRE lo envía (vacío). Los bots que atacan directamente a enviar.php sin cargar
// la página NO lo mandan → se descartan aquí. Esta es la mayor fuga de spam cerrada.
// (Se exceptúa el modo dry-run para no romper el test automático.)
if (!$dry_run && !isset($_POST['_honey'])) {
    header('Location: ' . BASE_URL . 'gracias.html');
    exit;
}
// Si el honeypot viene relleno, es un bot (los humanos no ven el campo oculto).
if (!empty($_POST['_honey'])) {
    header('Location: ' . BASE_URL . 'gracias.html');
    exit;
}

// Control de tiempo (< 5 s = bot).
// Solo se bloquea si HAY marca de tiempo y el envío fue demasiado rápido.
// Si no hay marca de tiempo (visitante sin JavaScript), NO se descarta: el honeypot
// de arriba sigue protegiendo contra bots, pero así no se pierde ningún cliente real.
$ts = (int)($_POST['_ts'] ?? 0);
if ($ts !== 0 && (time() - $ts) < 5) {
    header('Location: ' . BASE_URL . 'gracias.html');
    exit;
}

// Sanitizar
function limpiar(string $v): string {
    return htmlspecialchars(trim(strip_tags($v)), ENT_QUOTES, 'UTF-8');
}

// Nombre — ES: nombre / CA: nom / EN+DE: name
$nombre = limpiar(
    $_POST['name']    ??
    $_POST['nombre']  ??
    $_POST['nom']     ?? ''
);
// Teléfono — ES: telefono / CA: telefon / FR: telephone / EN: phone
$telefono = limpiar(
    $_POST['phone']     ??
    $_POST['telefono']  ??
    $_POST['telefon']   ??
    $_POST['telephone'] ?? ''
);
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
// Servicio — múltiples nombres según idioma
$servicio = limpiar(
    $_POST['service']       ??
    $_POST['servicio']      ??
    $_POST['servei']        ??
    $_POST['service-type']  ??
    $_POST['tipo-servicio'] ??
    $_POST['tipus-servei']  ??
    $_POST['type-service']  ??
    $_POST['service-art']   ?? ''
);
// Ubicación — combina DIRECCIÓN EXACTA + ZONA/BARRIO (múltiples nombres según idioma).
// Antes se tomaba solo el primer campo no vacío; los formularios de ciudad tienen DOS
// campos de ubicación (p. ej. "barrio" + "dirección exacta", o en CA "zona" + "adreça"),
// así que se perdía uno de los dos (en sant-pere-ribes-ca se perdía justo la dirección
// exacta porque el campo se llamaba "adreca", que no estaba en la lista). Ahora se
// conservan ambos: "Dirección exacta · Zona".
$direccion_exacta = limpiar(
    $_POST['location']    ??
    $_POST['ubicacion']   ??
    $_POST['ubicacio']    ??
    $_POST['direccion']   ??
    $_POST['adreca']      ??
    $_POST['emplacement'] ??
    $_POST['standort']    ??
    $_POST['address']     ?? ''
);
$zona_barrio = limpiar($_POST['barrio'] ?? $_POST['zona'] ?? '');
$ubicacion = trim(implode(' · ', array_filter([$direccion_exacta, $zona_barrio], 'strlen')));
// Mensaje — múltiples nombres según idioma
$mensaje = limpiar(
    $_POST['message']  ??
    $_POST['mensaje']  ??
    $_POST['missatge'] ??
    $_POST['nachricht'] ?? ''
);
// Ciudad — varía según la página (ciudad / pueblo / poble / provincia / ciutat)
$ciudad = limpiar(
    $_POST['ciudad']    ??
    $_POST['pueblo']    ??
    $_POST['poble']     ??
    $_POST['provincia'] ??
    $_POST['ciutat']    ?? ''
);

// Página de éxito (validada contra whitelist)
$redir_raw   = basename(trim($_POST['_redirect'] ?? 'gracias.html'));
$success_url = BASE_URL . (in_array($redir_raw, REDIRECTS_OK) ? $redir_raw : 'gracias.html');

// Validar obligatorios
if (empty($nombre) || empty($telefono) || empty($servicio) || empty($ubicacion)) {
    header('Location: ' . ERROR_URL);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . ERROR_URL);
    exit;
}

// ── FILTRO ANTI-SPAM DE CONTENIDO ────────────────────────────────────────────
// Los bots rellenan todos los campos (saltándose honeypot/tiempo), pero su texto
// los delata: enlaces, estafas (jackpot/crypto), captación B2B en inglés…
// Si se detecta, se descarta EN SILENCIO (redirige a "gracias" para que el bot
// crea que envió y no reintente). No afecta a clientes reales (ES/CA sin enlaces).
$blob = ' ' . strtolower($nombre . ' ' . $mensaje . ' ' . $servicio . ' ' . $ubicacion) . ' ';
$patron_spam = '~('
    . 'https?://|www\.|t\.me/|bit\.ly|tinyurl|myip\.kr|\.ru/|\.top/|telegram'   // enlaces
    . '|jackpot|casino|crypto|bitcoin|viagra|cialis|escort|\bporn\b|\bseo\b|backlink'  // estafa/adulto/SEO
    . '|i hope this message finds you|i represent|marketing department|investment firm' // captación B2B
    . '|business financing|growth capital|financing solutions|\bloan\b|gift card'
    . '|click here|inactive for|claim your|bragging rights|\$\s?[0-9]'           // phishing/dinero
    . '|guest post|link building|rank your|first page of google|web design service' // SEO/marketing spam
    . '|web development service|digital marketing|increase your (traffic|sales|ranking)'
    . '|we noticed your|we can help you|boost your|outreach|\[url|\[/url|<a href' // captación/BBCode
    . ')~';
$es_spam = preg_match($patron_spam, $blob) === 1;
// Nombre basura: 12+ caracteres seguidos en MAYÚSCULAS/dígitos (p.ej. NATREGTEGH478780).
if (preg_match('/[A-Z0-9]{12,}/', $nombre)) { $es_spam = true; }
// Alfabetos no latinos (cirílico, chino, japonés, coreano, árabe, tailandés…) en el
// nombre o el mensaje. Tus clientes escriben en español/catalán/inglés/francés/alemán
// (alfabeto latino), así que esto es señal casi segura de bot ruso/chino. No afecta a
// clientes reales, ni siquiera a los internacionales de las páginas premium.
if (preg_match('/[\x{0400}-\x{04FF}\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}\x{AC00}-\x{D7AF}\x{0600}-\x{06FF}\x{0E00}-\x{0E7F}]/u', $nombre . ' ' . $mensaje)) {
    $es_spam = true;
}
if ($es_spam) {
    header('Location: ' . BASE_URL . 'gracias.html');
    exit;
}

// Asunto — incluye ciudad si la hay
$label_ciudad = $ciudad ? ' · ' . $ciudad : '';
$asunto = '=?UTF-8?B?' . base64_encode('Nueva solicitud' . $label_ciudad . ' · ' . SITE_NAME) . '?=';

// ── CUERPO DEL EMAIL ─────────────────────────────────────────────────────────
$fila = function(string $label, string $valor, bool $gris = false): string {
    $bg = $gris ? 'background:#f9f9f9;' : '';
    return "<tr style=\"{$bg}border-bottom:1px solid #eee\">
      <td style=\"padding:11px 14px;color:#777;width:32%;font-size:.88rem\"><strong>{$label}</strong></td>
      <td style=\"padding:11px 14px;color:#222;font-size:.9rem\">{$valor}</td>
    </tr>";
};

$tel_link  = '<a href="tel:' . preg_replace('/[^0-9+]/', '', $telefono) . '" style="color:#2B638D;font-weight:700">' . $telefono . '</a>';
$mail_link = '<a href="mailto:' . $email . '" style="color:#2B638D">' . $email . '</a>';
$msg_cel   = $mensaje ? nl2br($mensaje) : '<em style="color:#aaa">—</em>';

$filas_extra = $ciudad ? $fila('Ciudad', $ciudad, true) : '';

$cuerpo = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f0f4f8;margin:0;padding:24px">
<div style="max-width:580px;margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.1)">
  <div style="background:linear-gradient(135deg,#1a3a5c,#2B638D);padding:26px 30px">
    <h1 style="color:#fff;margin:0;font-size:1.15rem;font-weight:700">📋 Nueva solicitud de presupuesto</h1>
    <p style="color:rgba(255,255,255,.7);margin:5px 0 0;font-size:.82rem">VaciadoDePisos.cat · ' . date('d/m/Y \a\s H:i') . '</p>
  </div>
  <div style="padding:6px 0">
    <table style="width:100%;border-collapse:collapse">'
    . $fila('Nombre',    $nombre)
    . $fila('Teléfono',  $tel_link,  true)
    . $fila('Email',     $mail_link)
    . $filas_extra
    . $fila('Servicio',  $servicio,  true)
    . $fila('Ubicación', $ubicacion)
    . '<tr style="background:#f9f9f9">
        <td style="padding:11px 14px;color:#777;font-size:.88rem;vertical-align:top"><strong>Mensaje</strong></td>
        <td style="padding:11px 14px;color:#222;font-size:.9rem">' . $msg_cel . '</td>
      </tr>
    </table>
  </div>
  <div style="background:#eef4fb;padding:14px 30px;border-top:1px solid #dce8f5;text-align:center">
    <a href="tel:' . preg_replace('/[^0-9+]/', '', $telefono) . '" style="display:inline-block;background:#E8A933;color:#1a2a3a;padding:10px 28px;border-radius:50px;font-weight:700;font-size:.9rem;text-decoration:none">📞 Llamar ahora: ' . $telefono . '</a>
  </div>
  <div style="padding:12px 30px;font-size:.75rem;color:#aaa;text-align:center">
    Enviado desde vaciadodepisos.cat
  </div>
</div>
</body></html>';
// ─────────────────────────────────────────────────────────────────────────────

$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
$cabeceras .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
$cabeceras .= 'From: ' . SITE_NAME . ' <' . FROM_EMAIL . '>' . "\r\n";
$cabeceras .= 'Reply-To: ' . $nombre . ' <' . $email . '>' . "\r\n";
$cabeceras .= 'X-Mailer: PHP/' . phpversion() . "\r\n";

// ── RESPALDO: guardar cada solicitud en un CSV protegido (red de seguridad) ──
// La carpeta /solicitudes/ está bloqueada al público por su propio .htaccess (datos personales).
// Si un email se perdiera, la solicitud NO se pierde: queda aquí. Descárgalo por FTP.
if (!$dry_run) {
    $origen = limpiar($_SERVER['HTTP_REFERER'] ?? '');
    $dir = __DIR__ . '/solicitudes';
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    $csv = $dir . '/solicitudes.csv';
    $es_nuevo = !file_exists($csv);
    $fh = @fopen($csv, 'a');
    if ($fh !== false) {
        if ($es_nuevo) {
            @fputcsv($fh, ['Fecha', 'Nombre', 'Telefono', 'Email', 'Servicio', 'Ciudad', 'Ubicacion', 'Mensaje', 'Pagina']);
        }
        @fputcsv($fh, [
            date('Y-m-d H:i:s'), $nombre, $telefono, $email, $servicio, $ciudad, $ubicacion,
            str_replace(["\r", "\n"], ' ', $mensaje), $origen
        ]);
        @fclose($fh);
    }
}

// ── ENVÍO DEL EMAIL — exactamente como en tu versión original que funcionaba ──
if (!$dry_run) {
    mail(DEST_EMAIL, $asunto, $cuerpo, $cabeceras);
}

// ── GUARDAR LEAD EN CRM (Supabase) ───────────────────────────────────────────
// Apunta al CRM NUEVO (blfesrhoclmhpfgyscbb). Usa la RPC pública `submit_lead`,
// que es el ÚNICO camino anónimo permitido: valida el lead y lo asigna a la
// empresa por su slug. La clave publishable es pública (igual que en el cliente
// React) y no da acceso a leer/editar datos, solo a llamar a esta función.
if (!$dry_run && function_exists('curl_init')) {
    $sb_payload = json_encode([
        '_nombre'        => $nombre,
        '_telefono'      => $telefono ?: null,
        '_email'         => $email    ?: null,
        '_servicio'      => $servicio ?: null,
        '_ubicacion'     => $ubicacion ?: null,
        '_ciudad'        => $ciudad   ?: null,
        '_mensaje'       => $mensaje  ?: null,
        '_origen_pagina' => limpiar($_SERVER['HTTP_REFERER'] ?? '') ?: null,
        '_tenant_slug'   => 'vaciadodepisos',
    ], JSON_UNESCAPED_UNICODE);
    $sb_key = 'YOUR_SUPABASE_PUBLISHABLE_KEY';
    $ch = curl_init('https://YOUR_PROJECT.supabase.co/rest/v1/rpc/submit_lead');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $sb_payload,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: ' . $sb_key,
            'Authorization: Bearer ' . $sb_key,
            'Prefer: return=minimal',
        ],
    ]);
    curl_exec($ch);
    curl_close($ch);
}

header('Location: ' . $success_url);
exit;
