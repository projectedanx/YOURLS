<?php

#[\PHPUnit\Framework\Attributes\Group('install')]
class CheckPDOTest extends PHPUnit\Framework\TestCase {

    public function test_yourls_check_PDO() {
        $this->assertSame( extension_loaded('pdo'), yourls_check_PDO() );
    }

}
