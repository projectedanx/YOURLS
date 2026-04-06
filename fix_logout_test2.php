<?php
$file = 'tests/tests/auth/LogoutTest.php';
$content = file_get_contents($file);

$content = str_replace(
    "\$this->assertSame('yourls', self::\$user);",
    "\$this->assertSame(yourls_get_user(), self::\$user);",
    $content
);

file_put_contents($file, $content);
