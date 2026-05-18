<?php

/**
 * Build VNPAY payment URL
 * order: array from orders table (must have id, total)
 */
function vnpay_build_payment_url(array $order, string $ipAddr): string {
  $cfg = require __DIR__ . '/../config.php';
  $vnp = $cfg['vnpay'];

  $tmnCode    = (string)$vnp['tmn_code'];
  $hashSecret = (string)$vnp['hash_secret'];
  $payUrl     = (string)$vnp['pay_url'];
  $returnUrl  = (string)$vnp['return_url'];

  $amount = ((int)$order['total']) * 100; // VNPAY requires x100
  $txnRef = (string)$order['id'];

  $createDate = date('YmdHis');
  $expireDate = date('YmdHis', time() + 15 * 60);

  $inputData = [
    'vnp_Version'    => '2.1.0',
    'vnp_Command'    => 'pay',
    'vnp_TmnCode'    => $tmnCode,
    'vnp_Amount'     => $amount,
    'vnp_CurrCode'   => 'VND',
    'vnp_TxnRef'     => $txnRef,
    'vnp_OrderInfo'  => 'Thanh toan don hang #' . $order['id'],
    'vnp_OrderType'  => 'other',
    'vnp_Locale'     => 'vn',
    'vnp_ReturnUrl'  => $returnUrl,
    'vnp_IpAddr'     => $ipAddr ?: '127.0.0.1',
    'vnp_CreateDate' => $createDate,
    'vnp_ExpireDate' => $expireDate,
  ];

  // Optionally force bank code (INTCARD = international card)
  $bankCode = trim((string)($vnp['bank_code_card'] ?? ''));
  if ($bankCode !== '') {
    $inputData['vnp_BankCode'] = $bankCode;
  }

  ksort($inputData);

  $hashPairs = [];
  foreach ($inputData as $k => $v) {
    $hashPairs[] = $k . '=' . $v;
  }
  $hashDataStr = implode('&', $hashPairs);
  $secureHash = hash_hmac('sha512', $hashDataStr, $hashSecret);

  $query = http_build_query($inputData);
  return $payUrl . '?' . $query . '&vnp_SecureHash=' . $secureHash;
}

/**
 * Verify VNPAY signature for return/ipn query params
 */
function vnpay_verify_signature(array $params): bool {
  $cfg = require __DIR__ . '/../config.php';
  $hashSecret = (string)$cfg['vnpay']['hash_secret'];

  $secureHash = (string)($params['vnp_SecureHash'] ?? '');
  unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);

  ksort($params);

  $hashPairs = [];
  foreach ($params as $k => $v) {
    if ($v === '' || $v === null) continue;
    $hashPairs[] = $k . '=' . $v;
  }
  $hashDataStr = implode('&', $hashPairs);

  $calc = hash_hmac('sha512', $hashDataStr, $hashSecret);
  return hash_equals($calc, $secureHash);
}