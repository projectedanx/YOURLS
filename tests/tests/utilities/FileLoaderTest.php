<?php

/**
 * Test sandboxed file loader
 *
 * @since 1.9.1
 */
#[\PHPUnit\Framework\Attributes\Group('functions')]
class FileLoaderTest extends PHPUnit\Framework\TestCase {

    /**
     * Load valid file = true
     */
    public function test_load_file_exists() {
        $file = YOURLS_TESTDATA_DIR . "/" . rand_str() . ".php";
        if( touch("$file") ) {
            $this->assertTrue( yourls_include_file_sandbox( $file ) );
            unlink("$file");
        } else {
            $this->markTestSkipped( "Cannot create test '$file");
        }
    }

    /**
     * Load missing file = string
     */
    public function test_load_file_not_exists() {
        $this->assertNull( yourls_include_file_sandbox( YOURLS_TESTDATA_DIR . "/" . rand_str() . ".php" ) );
    }


    /**
     * Load file that throws an exception = error string
     */
    public function test_load_file_exception() {
        $file = YOURLS_TESTDATA_DIR . "/" . rand_str() . ".php";
        if( file_put_contents("$file", "<?php throw new Exception('Test Exception');") ) {
            $result = yourls_include_file_sandbox( $file );
            $this->assertIsString( $result );
            $this->assertStringContainsString( "Test Exception", $result );
            $this->assertStringContainsString( basename($file), $result );
            unlink("$file");
        } else {
            $this->markTestSkipped( "Cannot create test '$file'");
        }
    }
    /**
     * For tests to load valid and broken PHP code: see in tests/plugins/files.php
     */

}
