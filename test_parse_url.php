<?php
$url = 'http://127.0.0.1/yourls_directory/abc?foo=bar#baz';
$relative = 'abc?foo=bar#baz';
echo "Parsed path: " . parse_url($relative, PHP_URL_PATH) . "\n";
echo "Parsed query: " . parse_url($relative, PHP_URL_QUERY) . "\n";
