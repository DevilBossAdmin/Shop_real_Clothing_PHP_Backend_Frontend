<?php
require_once __DIR__ . '/Shipping.php';

class CarrierApi
{
  private static function curlJson(string $method, string $url, array $headers, ?array $body=null): array
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($body !== null) {
      $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }

    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) return ['ok'=>false,'http'=>$code,'error'=>$err ?: 'curl_error'];
    $json = json_decode($resp, true);
    return ['ok'=>($code>=200 && $code<300),'http'=>$code,'data'=>$json,'raw'=>$resp];
  }

  // Bạn nhập base_url + token trong admin, endpoint path bạn tự cấu hình theo tài liệu hãng
  public static function createShipmentGHN(array $payload): array
  {
    $s = Shipping::settings();
    if (empty($s['ghn_enabled'])) return ['ok'=>false,'error'=>'GHN disabled'];

    $base = rtrim((string)$s['ghn_base_url'], '/');
    $url = $base . '/create'; // <-- bạn đổi path đúng theo GHN
    $headers = [
      'Content-Type: application/json',
      'Token: ' . (string)$s['ghn_token'],
      'ShopId: ' . (string)$s['ghn_shop_id'],
    ];
    return self::curlJson('POST', $url, $headers, $payload);
  }

  public static function trackGHN(string $trackingCode): array
  {
    $s = Shipping::settings();
    if (empty($s['ghn_enabled'])) return ['ok'=>false,'error'=>'GHN disabled'];

    $base = rtrim((string)$s['ghn_base_url'], '/');
    $url = $base . '/track?code=' . urlencode($trackingCode); // <-- đổi đúng theo GHN
    $headers = [
      'Content-Type: application/json',
      'Token: ' . (string)$s['ghn_token'],
      'ShopId: ' . (string)$s['ghn_shop_id'],
    ];
    return self::curlJson('GET', $url, $headers, null);
  }

  public static function createShipmentGHTK(array $payload): array
  {
    $s = Shipping::settings();
    if (empty($s['ghtk_enabled'])) return ['ok'=>false,'error'=>'GHTK disabled'];

    $base = rtrim((string)$s['ghtk_base_url'], '/');
    $url = $base . '/create'; // <-- đổi path đúng theo GHTK
    $headers = [
      'Content-Type: application/json',
      'Token: ' . (string)$s['ghtk_token'],
    ];
    return self::curlJson('POST', $url, $headers, $payload);
  }

  public static function trackGHTK(string $trackingCode): array
  {
    $s = Shipping::settings();
    if (empty($s['ghtk_enabled'])) return ['ok'=>false,'error'=>'GHTK disabled'];

    $base = rtrim((string)$s['ghtk_base_url'], '/');
    $url = $base . '/track?code=' . urlencode($trackingCode); // <-- đổi path đúng theo GHTK
    $headers = [
      'Content-Type: application/json',
      'Token: ' . (string)$s['ghtk_token'],
    ];
    return self::curlJson('GET', $url, $headers, null);
  }
}