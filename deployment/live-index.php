<?php
declare(strict_types=1);
// The document root contains only this bridge; source and secrets live outside it.
$port = trim((string) @file_get_contents('/home/studiosorrydotco/apps/sorrydotcomeditor/shared/active-port'));
if (!in_array($port, ['3101', '3102'], true)) {
    http_response_code(503);
    exit('The application is starting. Please try again shortly.');
}
$skip = ['host', 'connection', 'content-length', 'transfer-encoding', 'accept-encoding', 'upgrade', 'forwarded', 'x-forwarded-for', 'x-forwarded-proto', 'x-forwarded-host'];
$headers = [];
foreach ($_SERVER as $key => $value) {
    if (!str_starts_with($key, 'HTTP_')) continue;
    $name = str_replace('_', '-', substr($key, 5));
    if (!in_array(strtolower($name), $skip, true)) $headers[] = $name . ': ' . $value;
}
if (!empty($_SERVER['CONTENT_TYPE'])) $headers[] = 'Content-Type: ' . $_SERVER['CONTENT_TYPE'];
$headers[] = 'Host: studio.sorrydotcom.lk';
$headers[] = 'X-Forwarded-For: ' . ($_SERVER['REMOTE_ADDR'] ?? '');
$headers[] = 'X-Forwarded-Proto: ' . ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
$headers[] = 'X-Forwarded-Host: studio.sorrydotcom.lk';
$curl = curl_init('http://127.0.0.1:' . $port . ($_SERVER['REQUEST_URI'] ?? '/'));
curl_setopt_array($curl, [
    CURLOPT_CUSTOMREQUEST => $_SERVER['REQUEST_METHOD'] ?? 'GET',
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_NOBODY => ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD',
    CURLOPT_HEADERFUNCTION => static function ($handle, string $line): int {
        $parts = explode(':', trim($line), 2);
        if (count($parts) === 2 && !in_array(strtolower($parts[0]), ['connection', 'content-length', 'transfer-encoding', 'keep-alive', 'upgrade'], true)) {
            header(trim($line), strtolower($parts[0]) !== 'set-cookie');
        }
        return strlen($line);
    },
]);
$body = file_get_contents('php://input');
if ($body !== false && $body !== '') curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
$response = curl_exec($curl);
http_response_code($response === false ? 502 : (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
echo $response === false ? 'The application is restarting. Please try again shortly.' : $response;
curl_close($curl);
