<?php
// Let's replace the RunInSeparateProcess and PreserveGlobalState in the files
$files = [
    'tests/tests/auth/LoginCookieTest.php',
    'tests/tests/install/MaintenanceModeTest.php',
    'tests/tests/links/FaviconTest.php'
];

foreach ($files as $f) {
    if (!file_exists($f)) {
        continue;
    }
    $c = file_get_contents($f);
    $c = preg_replace('/\/\/#\[\\\PHPUnit\\\Framework\\\Attributes\\\RunInSeparateProcess\]/', '#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]', $c);
    $c = preg_replace('/\/\/#\[\\\PHPUnit\\\Framework\\\Attributes\\\PreserveGlobalState\(false\)\]/', '#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]', $c);
    file_put_contents($f, $c);
}
