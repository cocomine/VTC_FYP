<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace cocopixelmc\Auth;

header('Content-Type: text/json; charset=UTF-8');

$config = array(
    "digest_alg" => "sha512",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
);
$res = openssl_pkey_new($config);

openssl_pkey_export($res, $privKey);
$pubKey = openssl_pkey_get_details($res);
$pubKey = $pubKey["key"];

session_start();
unset($_SESSION['pvKey']);
$_SESSION['pvKey'] = $privKey;
header('Content-Type: text/text; charset=UTF-8');
echo $pubKey;