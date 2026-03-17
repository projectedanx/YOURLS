<?php
require_once 'includes/load-yourls.php';

$shorturl1 = "http://sho.rt/abc";
$shorturl2 = "http://sho.rt/abc+";
$shorturl3 = "http://sho.rt/abc+all";
$shorturl4 = "abc";

// Simulate a taken keyword
$ydb = yourls_get_db();
$ydb->set_infos('abc', ['url' => 'http://example.com']);

echo "1: " . (yourls_is_shorturl($shorturl1) ? 'true' : 'false') . "\n";
echo "2: " . (yourls_is_shorturl($shorturl2) ? 'true' : 'false') . "\n";
echo "3: " . (yourls_is_shorturl($shorturl3) ? 'true' : 'false') . "\n";
echo "4: " . (yourls_is_shorturl($shorturl4) ? 'true' : 'false') . "\n";
