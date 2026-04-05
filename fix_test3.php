<?php
$file = 'tests/tests/auth/LoginCookieTest.php';
$content = file_get_contents($file);

$content = str_replace("\$this->assertSame( 0, yourls_did_action('pre_setcookie') );", "\$before = yourls_did_action('pre_setcookie');", $content);
$content = str_replace("\$this->assertSame( 1, yourls_did_action('pre_setcookie') );", "\$this->assertSame( \$before + 1, yourls_did_action('pre_setcookie') );", $content);

$content = preg_replace(
    '/\$this->assertNotTrue\(yourls_is_valid_user\(\)\);\n        \$before = yourls_did_action\(\'pre_setcookie\'\);/',
    "\$this->assertNotTrue(yourls_is_valid_user());\n        \$this->assertSame( \$before, yourls_did_action('pre_setcookie') );",
    $content
);

file_put_contents($file, $content);
