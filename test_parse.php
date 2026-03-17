<?php
require_once 'includes/load-yourls.php';
// bypass DB connection
function yourls_keyword_is_taken($k) { return $k === 'abc'; }

$url1 = "http://127.0.0.1/yourls_directory/abc?foo=bar";
$k1 = yourls_get_relative_url($url1);
echo "k1: $k1\n";
