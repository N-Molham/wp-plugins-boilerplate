<?php namespace WP_Plugins\Boilerplate;

/**
 * Class Singular
 *
 * @package WP_Plugins\Boilerplate
 */
class Singular
{
	/**
	 * Singular instance holder
	 *
	 * @var array
	 */
	protected static $static = [ ];

	/**
	 * Singular Initialization
	 *
	 * Prevent creating instance from outside
	 */
	protected function __construct()
	{
		// do nothing
	}

	/**
	 * Get only instance
	 *
	 * @param mixed $args ( optional )
	 *
	 * @return static
	 */
	public static function &get_instance( $args = '' )
	{
		// use 5.4 method for backward compatibility
		$class_name = get_called_class();

		if ( !isset( self::$static[ $class_name ] ) )
		{
			// create the instance of not yet created
			self::$static[ $class_name ] = new static();

			if ( method_exists( self::$static[ $class_name ], 'init' ) )
			{
				// run initialization method if exists
				$num_args = func_num_args();
				$args     = func_get_args();
				if ( $num_args == 0 )
				{
					// call without args
					self::$static[ $class_name ]->init();
				}
				else
				{
					if ( $num_args == 1 )
					{
						// pass on one argument
						self::$static[ $class_name ]->init( $args[0] );
					}
					else
					{
						// pass on all argument
						call_user_func_array( [ self::$static[ $class_name ], 'init' ], $args );
					}
				}
			}
		}

		// return the instance
		return self::$static[ $class_name ];
	}

	/**
	 * Prevent cloning
	 *
	 * @return void
	 */
	protected function __clone()
	{
		// do nothing
	}
}