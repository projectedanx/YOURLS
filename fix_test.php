<?php
$file = 'tests/tests/auth/LoginCookieTest.php';
$content = file_get_contents($file);

// Replace hardcoded 0 and 1 assertions with dynamic baseline values
$content = str_replace(
    "\$this->assertSame( 0, yourls_did_action('pre_setcookie') );\n        \$this->assertTrue(yourls_check_auth_cookie());\n        \$this->assertTrue(yourls_is_valid_user());\n        \$this->assertSame( 1, yourls_did_action('pre_setcookie') );",
    "\$before = yourls_did_action('pre_setcookie');\n        \$this->assertTrue(yourls_check_auth_cookie());\n        \$this->assertTrue(yourls_is_valid_user());\n        \$this->assertSame( \$before + 1, yourls_did_action('pre_setcookie') );",
    $content
);

$content = str_replace(
    "\$this->assertSame( 0, yourls_did_action('pre_setcookie') );\n        \$this->assertFalse(yourls_check_auth_cookie());\n        \$this->assertNotTrue(yourls_is_valid_user());\n        \$this->assertSame( 0, yourls_did_action('pre_setcookie') );",
    "\$before = yourls_did_action('pre_setcookie');\n        \$this->assertFalse(yourls_check_auth_cookie());\n        \$this->assertNotTrue(yourls_is_valid_user());\n        \$this->assertSame( \$before, yourls_did_action('pre_setcookie') );",
    $content
);

file_put_contents($file, $content);
