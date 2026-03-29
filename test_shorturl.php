<?php
require_once 'includes/load-yourls.php';
$url = 'http://127.0.0.1/yourls_directory/abc?foo=bar';
echo "Relative URL: " . yourls_get_relative_url($url) . "\n";
echo "Sanitize Keyword: " . yourls_sanitize_keyword(yourls_get_relative_url($url)) . "\n";
