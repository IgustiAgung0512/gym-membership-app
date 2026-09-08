<?php

try {
    $ca = ini_get('openssl.cafile') ?: ini_get('curl.cainfo');
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => $ca,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    $pdo = new PDO(
        'mysql:host=gateway01.ap-southeast-1.prod.aws.tidbcloud.com;port=4000;dbname=test',
        '2k6bquwmDUSys4g.root',
        'OekoAc0KNbUvxb8D',
        $options
    );

    echo "CONNECTED SUCCESSFULLY TO TIDB CLOUD!\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Found " . count($tables) . " tables.\n";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
