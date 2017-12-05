<?php
/**
 * Class for testing a field case
 *
 * @package WordPress
 * @subpackage Custom Meta Boxes
 */

namespace HMCMB\Tests;

use WP_UnitTest_Factory;
use WP_UnitTestCase;
use WP_Scripts;

/**
 * Class TestFieldCase
 */
abstract class TestFieldCase extends WP_UnitTestCase {
	/**
	 * Consistent post for use with testing fields.
	 *
	 * @var
	 */
	protected static $post;

	/**
	 * Class instance.
	 */
	protected $instance;

	/**
	 * Store a reference to global scripts to reset after tests are complete.
	 *
	 * @var
	 */
	private static $old_wp_scripts;

	/**
	 * Setup objects for our tests.
	 *
	 * @param $factory
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		// Setup a default post for usage within test.
		self::$post = $factory->post->create_and_get( [
			'post_author'  => 1,
			'post_status'  => 'publish',
			'post_content' => rand_str(),
			'post_title'   => rand_str(),
			'post_type'    => 'post',
		] );

		// Capture WP Scripts object before usage.
		self::$old_wp_scripts = isset( $GLOBALS['wp_scripts'] ) ? $GLOBALS['wp_scripts'] : null;
	}

	public static function wpTearDownAfterClass() {
		wp_delete_post( self::$post->ID );
		$GLOBALS['wp_scripts'] = self::$old_wp_scripts;
	}

	/**
	 * Update the argument set on a field class.
	 *
	 * @param $new_arguments
	 */
	public function update_arguments( $new_arguments ) {
		$this->instance->set_arguments( $new_arguments );
	}

	/**
	 * Reset the wp_script globals
	 */
	public function reset_wp_scripts() {
		$GLOBALS['wp_scripts'] = new WP_Scripts();
		$GLOBALS['wp_scripts']->default_version = get_bloginfo( 'version' );
	}

	/**
	 * Verify that the field outputs with no errors for each argument set.
	 *
	 * @dataProvider argumentsProvider
	 *
	 * @param
	 */
	public function test_field_output( $arguments ) {
		$this->update_arguments( $arguments );

		// Check the default HTML output.
		// The point of these checks is to ensure that the field doesn't error out with each argument set.
		$this->expectOutputRegex( '/data-class=\"' . get_class( $this->instance ) . '\"/' );
		$this->instance->display();
	}

	/**
	 * Verify that the field saves values correctly to meta.
	 *
	 * @dataProvider valuesProvider
	 *
	 * @param
	 * @param
	 */
	function test_save_value( $value, $expected_value = false ) {
		$this->instance->save( self::$post->ID, $value );

		// Usually, we only want to pass one value and not a parsed value. Accomodate this here.
		if ( false === $expected_value ) {
			$expected_value = $value;
		}

		// Verify single value is properly saved.
		$this->assertEquals(
			$expected_value,
			get_post_meta( self::$post->ID, get_class( $this->instance ), false )
		);
	}

	/**
	 * Provide a set of arguments to test against.
	 *
	 * Here we provide a default set of arguments to test each field against.
	 *
	 * @return array Default argument set.
	 */
	public function argumentsProvider() {
		return [
			[ [] ],
			[ [
				'id' => 'my-ID',
			] ],
			[ [
				'id'   => 'my-ID',
				'name' => 'My Name'
			] ],
			[ [
				'id'   => 'my-ID',
				'name' => 'My Name',
				'desc' => 'A long description',
			] ],
			[ [
				'id'         => 'my-ID',
				'name'       => 'My Name',
				'desc'       => 'A long description',
				'repeatable' => false,
				'sortable'   => true,
			] ],
			[ [
				'id'         => 'my-ID',
				'name'       => 'My Name',
				'desc'       => 'A long description',
				'repeatable' => true,
				'sortable'   => false,
			] ],
			[ [
				'id'         => 'my-ID',
				'name'       => 'My Name',
				'desc'       => 'A long description',
				'repeatable' => true,
				'sortable'   => true,
				'cols'       => 6,
				'readonly'   => true,
				'disabled'   => true,
				'class'      => 'my-fancy-fancy-class',
			] ],

			// @todo:: add default
		];
	}

	/**
	 * Provide a default set of values to test saving against.
	 *
	 * @return array Default values set.
	 */
	public function valuesProvider() {
		return [
			[ [ 'A string' ] ],
			[ [ 162735 ] ],
			[ [ true ] ],
		];
	}
}