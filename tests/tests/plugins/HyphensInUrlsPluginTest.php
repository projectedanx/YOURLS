<?php

#[\PHPUnit\Framework\Attributes\Group('plugins')]
class HyphensInUrlsPluginTest extends PHPUnit\Framework\TestCase {

    protected function setUp(): void {
        parent::setUp();
        require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/user/plugins/hyphens-in-urls/plugin.php';
    }

    public function test_ozh_hyphen_in_charset() {
        $this->assertTrue( function_exists( 'ozh_hyphen_in_charset' ) );

        $this->assertSame( '-', ozh_hyphen_in_charset( '' ) );
        $this->assertSame( 'abc-', ozh_hyphen_in_charset( 'abc' ) );
        $this->assertSame( '0123456789-', ozh_hyphen_in_charset( '0123456789' ) );
    }
}
