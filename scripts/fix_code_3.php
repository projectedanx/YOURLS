<?php
// Fix admin/index.php header before echo
$content = file_get_contents('admin/index.php');
$search = <<<'CODE'
        echo yourls_apply_filter( 'bookmarklet_jsonp', 'yourls_callback(' . json_encode( $jsonp_data ) . ');' );
        header('Content-Type: application/javascript');
CODE;
$replace = <<<'CODE'
        header('Content-Type: application/javascript');
        echo yourls_apply_filter( 'bookmarklet_jsonp', 'yourls_callback(' . json_encode( $jsonp_data ) . ');' );
CODE;
$content = str_replace($search, $replace, $content);
file_put_contents('admin/index.php', $content);

echo "Modifications 3 completed.";
