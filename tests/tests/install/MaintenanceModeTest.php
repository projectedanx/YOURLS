<?php

#[\PHPUnit\Framework\Attributes\Group('install')]
class MaintenanceModeTest extends PHPUnit\Framework\TestCase {

    protected function tearDown(): void {
        parent::tearDown();
        // Ensure .maintenance file is removed after tests
        $file = YOURLS_ABSPATH . '/.maintenance';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    //#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    //#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_maintenance_mode_enable() {
        $file = YOURLS_ABSPATH . '/.maintenance';

        // Ensure the file does not exist before enabling
        if (file_exists($file)) {
            unlink($file);
        }

        $result = yourls_maintenance_mode(true);

        $this->assertTrue($result);
        $this->assertFileExists($file);

        $content = file_get_contents($file);
        $this->assertStringContainsString('<?php $maintenance_start = ', $content);
    }

    //#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    //#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_maintenance_mode_disable() {
        $file = YOURLS_ABSPATH . '/.maintenance';

        // Ensure the file exists before disabling
        file_put_contents($file, 'dummy');

        $result = yourls_maintenance_mode(false);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($file);
    }
}
