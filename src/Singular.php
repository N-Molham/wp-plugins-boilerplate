<?php namespace WP_Plugins\Boilerplate;

/**
 * Class Singular
 *
 * @package WP_Plugins\Boilerplate
 */
abstract class Singular {

	/**
	 * Singular instance holder
	 *
	 * @var array
	 */
	protected static array $instances = [];


	/**
	 * Singular Initialization
	 *
	 * Prevent creating instance from outside
	 */
	protected function __construct() {
		// do nothing
	}


	/**
	 * Get only instance
	 *
	 * @param mixed $args ( optional )
	 *
	 * @return static
	 */
	public static function get_instance( $args = '' ) : Singular {

		$class_name = static::class;

		if ( isset( self::$instances[ $class_name ] ) ) {
			return self::$instances[ $class_name ];
		}

		// create the instance of not yet created
		self::$instances[ $class_name ] = new static();

		if ( method_exists( self::$instances[ $class_name ], 'init' ) ) {
			// run initialization method if exists
			$num_args = func_num_args();
			$args     = func_get_args();

			if ( 0 === $num_args ) {
				// call without args
				self::$instances[ $class_name ]->init();
			} else {
				// pass on all argument
				call_user_func_array( [ self::$instances[ $class_name ], 'init' ], $args );
			}
		}

		// return the instance
		return self::$instances[ $class_name ];
	}


	/**
	 * Prevent cloning
	 *
	 * @return void
	 */
	protected function __clone() {
		// do nothing
	}

}
