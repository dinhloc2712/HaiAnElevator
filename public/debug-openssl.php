<?php
// Debug script for OpenSSL on Windows
header('Content-Type: text/plain');

$opensslConf = 'C:/laragon/bin/php/php/extras/ssl/openssl.cnf';
echo "Checking openssl.cnf at: $opensslConf\n";
echo "File exists: " . (file_exists($opensslConf) ? 'YES' : 'NO') . "\n";

// Try setting it manually
putenv("OPENSSL_CONF=$opensslConf");
echo "Env OPENSSL_CONF: " . getenv('OPENSSL_CONF') . "\n";

echo "\n--- Attempting to create EC Key ---\n";
$config = [
    'private_key_type' => OPENSSL_KEYTYPE_EC,
    'curve_name' => 'prime256v1',
    'config' => $opensslConf, // Some versions allow passing it here
];

$res = @openssl_pkey_new($config);

if ($res === false) {
    echo "FAILED to create key.\n";
    echo "OpenSSL Error: " . openssl_error_string() . "\n";
} else {
    echo "SUCCESS! Key created.\n";
}

echo "\n--- OpenSSL Info ---\n";
echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
echo "OpenSSL Version Number: " . OPENSSL_VERSION_NUMBER . "\n";
