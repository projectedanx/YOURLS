<?php

/**
 * Checks upgrade functions
 */
#[\PHPUnit\Framework\Attributes\Group('install')]
class UpgradeTest extends PHPUnit\Framework\TestCase {

    protected function tearDown(): void {
        parent::tearDown();
        if (file_exists(YOURLS_ABSPATH . '/.maintenance')) {
            unlink(YOURLS_ABSPATH . '/.maintenance');
        }
    }

    public function test_yourls_upgrade_step_3() {
        // Set initial options
        yourls_update_option('version', '1.0');
        yourls_update_option('db_version', '100');

        // Step 3 updates options and removes maintenance mode
        // pass oldsql = 506 to skip the oldsql == 100 which triggers full table alters that fail when table already exists
        yourls_upgrade(3, '1.0', YOURLS_VERSION, 506, YOURLS_DB_VERSION);

        $this->assertSame(YOURLS_VERSION, yourls_get_option('version'));
        $this->assertSame(YOURLS_DB_VERSION, yourls_get_option('db_version'));
        $this->assertFalse(yourls_get_option('maintenance_mode'));
    }

    public function test_yourls_upgrade_step_1_2() {
        // Test step 1/2 with up-to-date db version to avoid triggering real db upgrades
        // We buffer the output since it echoes a javascript redirect and maintenance mode message
        ob_start();
        yourls_upgrade(1, YOURLS_VERSION, YOURLS_VERSION, 506, 506);
        $output = ob_get_clean();

        // Check that javascript redirect to step 3 is outputted
        $this->assertStringContainsString('upgrade.php?step=3', $output);
        $this->assertStringContainsString('window.location=', $output);
    }

}
