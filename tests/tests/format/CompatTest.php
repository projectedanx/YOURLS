<?php

/**
 * Format compatibility functions.
 *
 * @since 0.1
 */
#[\PHPUnit\Framework\Attributes\Group('formatting')]
#[\PHPUnit\Framework\Attributes\Group('compat')]
class CompatTest extends PHPUnit\Framework\TestCase {

    /**
     * Test yourls_array_to_json with non-array inputs
     */
    public function test_array_to_json_non_array() {
        $this->assertFalse( yourls_array_to_json( null ) );
        $this->assertFalse( yourls_array_to_json( false ) );
        $this->assertFalse( yourls_array_to_json( true ) );
        $this->assertFalse( yourls_array_to_json( 123 ) );
        $this->assertFalse( yourls_array_to_json( 'string' ) );
        $this->assertFalse( yourls_array_to_json( (object)array('a' => 1) ) );
    }

    /**
     * Test yourls_array_to_json with a vector array (non-associative)
     */
    public function test_array_to_json_vector() {
        // Empty array
        $this->assertSame( '[  ]', yourls_array_to_json( array() ) );

        // Single string element
        $this->assertSame( '[ "value" ]', yourls_array_to_json( array('value') ) );

        // Numeric elements
        $this->assertSame( '[ 123, 45.6 ]', yourls_array_to_json( array(123, 45.6) ) );

        // Mixed elements
        $this->assertSame( '[ "string", 123 ]', yourls_array_to_json( array('string', 123) ) );

        // Escaping check
        $this->assertSame( '[ "hello \"world\"" ]', yourls_array_to_json( array('hello "world"') ) );
    }

    /**
     * Test yourls_array_to_json with an associative array
     */
    public function test_array_to_json_associative() {
        // Simple string key and value
        $this->assertSame( '{ "key": "value" }', yourls_array_to_json( array('key' => 'value') ) );

        // Numeric values
        $this->assertSame( '{ "age": 30 }', yourls_array_to_json( array('age' => 30) ) );

        // Numeric keys in associative array (prepends "key_")
        $this->assertSame( '{ "key_0": "zero", "a": "b" }', yourls_array_to_json( array(0 => 'zero', 'a' => 'b') ) );

        // Escaping check
        $this->assertSame( '{ "key \"1\"": "value \"2\"" }', yourls_array_to_json( array('key "1"' => 'value "2"') ) );
    }

    /**
     * Test yourls_array_to_json with nested arrays
     */
    public function test_array_to_json_nested() {
        $data = array(
            'status' => 'success',
            'data'   => array(
                'id' => 1,
                'tags' => array('new', 'popular')
            )
        );

        $expected = '{ "status": "success", "data": { "id": 1, "tags": [ "new", "popular" ] } }';
        $this->assertSame( $expected, yourls_array_to_json( $data ) );
    }
}
