<?php

#[\PHPUnit\Framework\Attributes\Group('auth')]
class CheckPasswordHashTest extends PHPUnit\Framework\TestCase {
    protected static $yourls_user_passwords_copy;

    public static function setUpBeforeClass(): void {
        global $yourls_user_passwords;
        self::$yourls_user_passwords_copy = $yourls_user_passwords;

        // Reset passwords for this test
        $yourls_user_passwords = [];

        // Setup passwords
        $password = 'mypassword123';

        // 1. Clear text
        $yourls_user_passwords['user_cleartext'] = $password;

        // 2. MD5
        $salt = rand(10000, 99999);
        $yourls_user_passwords['user_md5'] = 'md5:' . $salt . ':' . md5($salt . $password);

        // 3. PHPass (simulating old phpass hashing with '!' replacement)
        $yourls_user_passwords['user_phpass'] = 'phpass:' . str_replace('$', '!', yourls_phpass_hash($password));
    }

    public static function tearDownAfterClass(): void {
        global $yourls_user_passwords;
        $yourls_user_passwords = self::$yourls_user_passwords_copy;
    }

    public function test_check_password_hash_cleartext() {
        // Correct password
        $this->assertTrue(yourls_check_password_hash('user_cleartext', 'mypassword123'));

        // Incorrect password
        $this->assertFalse(yourls_check_password_hash('user_cleartext', 'wrongpassword'));

        // Case sensitive
        $this->assertFalse(yourls_check_password_hash('user_cleartext', 'Mypassword123'));
    }

    public function test_check_password_hash_md5() {
        // Correct password
        $this->assertTrue(yourls_check_password_hash('user_md5', 'mypassword123'));

        // Incorrect password
        $this->assertFalse(yourls_check_password_hash('user_md5', 'wrongpassword'));

        // Case sensitive
        $this->assertFalse(yourls_check_password_hash('user_md5', 'Mypassword123'));
    }

    public function test_check_password_hash_phpass() {
        // Correct password
        $this->assertTrue(yourls_check_password_hash('user_phpass', 'mypassword123'));

        // Incorrect password
        $this->assertFalse(yourls_check_password_hash('user_phpass', 'wrongpassword'));

        // Case sensitive
        $this->assertFalse(yourls_check_password_hash('user_phpass', 'Mypassword123'));
    }

    public function test_check_password_hash_unknown_user() {
        $this->assertFalse(yourls_check_password_hash('unknown_user', 'mypassword123'));
    }
}
