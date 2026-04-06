<?php
$file = 'tests/tests/auth/LoginCookieTest.php';
$content = file_get_contents($file);

$content = preg_replace(
    '/\$this->assertSame\(\s*0,\s*yourls_did_action\(\'pre_setcookie\'\)\s*\);(\s*)\$this->assertTrue\(yourls_check_auth_cookie\(\)\);(\s*)\$this->assertTrue\(yourls_is_valid_user\(\)\);(\s*)\$this->assertSame\(\s*1,\s*yourls_did_action\(\'pre_setcookie\'\)\s*\);/',
    "\$before = yourls_did_action('pre_setcookie');\\1\$this->assertTrue(yourls_check_auth_cookie());\\2\$this->assertTrue(yourls_is_valid_user());\\3\$this->assertSame( \$before + 1, yourls_did_action('pre_setcookie') );",
    $content
);

$content = preg_replace(
    '/\$this->assertSame\(\s*0,\s*yourls_did_action\(\'pre_setcookie\'\)\s*\);(\s*)\$this->assertFalse\(yourls_check_auth_cookie\(\)\);(\s*)\$this->assertNotTrue\(yourls_is_valid_user\(\)\);(\s*)\$this->assertSame\(\s*0,\s*yourls_did_action\(\'pre_setcookie\'\)\s*\);/',
    "\$before = yourls_did_action('pre_setcookie');\\1\$this->assertFalse(yourls_check_auth_cookie());\\2\$this->assertNotTrue(yourls_is_valid_user());\\3\$this->assertSame( \$before, yourls_did_action('pre_setcookie') );",
    $content
);

file_put_contents($file, $content);
