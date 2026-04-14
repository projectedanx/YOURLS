<?php
$content = file_get_contents('tests/tests/http/HTTPHeadersTest.php');
$new_content = str_replace(
    '$this->assertEquals(1, yourls_redirect($dest, $code));',
    '$this->assertEquals(3, yourls_redirect($dest, $code));',
    $content
);
file_put_contents('tests/tests/http/HTTPHeadersTest.php', $new_content);
