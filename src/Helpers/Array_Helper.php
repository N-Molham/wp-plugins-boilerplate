<?php /** @noinspection PhpUnused */

namespace WP_Plugins\Boilerplate;

use ArrayAccess;
use Exception;
use JsonException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/**
 * A helper to manipulate arrays.
 */
class Array_Helper {

	/**
	 * Determines if a given item is an accessible array.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function accessible( $value ) : bool {

		return is_array( $value ) || $value instanceof ArrayAccess;
	}


	/**
	 * Combines two array values.
	 *
	 * @NOTE: This provides special handling for when one of the values is null.
	 *
	 * @param array|ArrayAccess|null $array original array
	 * @param array $merge variable list of arrays to merge
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function combine( $array, ...$merge ) : array {

		if ( ! self::accessible( $array ) ) {
			throw new RuntimeException( 'The array provided as the original array was not accessible!' );
		}

		foreach ( $merge as $item ) {
			if ( ! self::accessible( $item ) ) {
				throw new RuntimeException( 'One of the arrays provided to merge into the original was not accessible!' );
			}
		}

		return array_merge( $array, ...$merge );
	}


	/**
	 * Combines two array values recursively to preserve nested keys.
	 *
	 * @NOTE: This provides special handling for when one of the values is null.
	 *
	 * @param array|ArrayAccess|null $array original array
	 * @param array $merge variable list of arrays to merge
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function combine_recursive( $array, ...$merge ) : array {

		if ( ! self::accessible( $array ) ) {
			throw new RuntimeException( 'The array provided as the original array was not accessible!' );
		}

		foreach ( $merge as $item ) {
			if ( ! self::accessible( $item ) ) {
				throw new RuntimeException( 'One of the arrays provided to merge into the original was not accessible!' );
			}
		}

		return array_replace_recursive( $array, ...$merge );
	}


	/**
	 * Determines if an array has a given value.
	 *
	 * @param array $array
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function contains( array $array, $value ) : bool {

		return self::exists( array_flip( self::flatten( $array ) ), $value );
	}


	/**
	 * Gets an array excluding the given keys.
	 *
	 * @param array $array
	 * @param array|string $keys
	 *
	 * @return array
	 */
	public static function except( array $array, $keys ) : array {

		$temp = $array;

		self::remove( $temp, self::wrap( $keys ) );

		return $temp;
	}


	/**
	 * Determines if an array key exists.
	 *
	 * @param ArrayAccess|array $array
	 * @param string|int $key
	 *
	 * @return bool
	 */
	public static function exists( $array, $key ) : bool {

		if ( $array instanceof ArrayAccess ) {
			return $array->offsetExists( $key );
		}

		return array_key_exists( $key, self::wrap( $array ) );
	}


	/**
	 * Flattens a multi-dimensional array.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function flatten( array $array ) : array {

		$arrayValues = [];

		foreach ( $array as $value ) {
			if ( is_array( $value ) ) {
				/** @noinspection SlowArrayOperationsInLoopInspection */
				$arrayValues = array_merge( $arrayValues, self::flatten( $value ) );

				continue;
			}

			$arrayValues[] = $value;
		}

		return $arrayValues;
	}


	/**
	 * Flattens a multi-dimensional array to dot notated keys.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function flatten_to_dot_notation( array $array ) : array {

		$result        = [];
		$keys          = [];
		$arrayIterator = new RecursiveArrayIterator( $array );
		$iterator      = new RecursiveIteratorIterator( $arrayIterator, RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $iterator as $key => $value ) {
			$keys[ $iterator->getDepth() ] = $key;

			if ( ! self::accessible( $value ) ) {
				$dotKey            = implode( '.', array_slice( $keys, 0, $iterator->getDepth() + 1 ) );
				$result[ $dotKey ] = $value;
			}
		}

		return $result;
	}


	/**
	 * Gets an array value from a dot notated key.
	 *
	 * @param ArrayAccess|array $array
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public static function get( $array, string $key, $default = '' ) {

		if ( ! self::accessible( $array ) ) {
			return $default;
		}

		foreach ( explode( '.', $key ) as $segment ) {
			if ( ! self::exists( $array, $segment ) ) {
				return $default;
			}

			$array = $array[ $segment ];
		}

		return $array;
	}


	/**
	 * Determines if an array has a nested key by dot notation.
	 *
	 * @param ArrayAccess|array $array
	 * @param string|array $keys
	 *
	 * @return bool
	 */
	public static function has( $array, $keys ) : bool {

		$keys = self::wrap( $keys );

		if ( ! $array || empty( $keys ) ) {
			return false;
		}

		foreach ( $keys as $key ) {
			if ( self::exists( $array, $key ) ) {
				continue;
			}

			$subArray = $array;

			foreach ( explode( '.', $key ) as $segment ) {
				if ( ! self::accessible( $subArray ) || ! self::exists( $subArray, $segment ) ) {
					return false;
				}

				$subArray = $subArray[ $segment ];
			}
		}

		return true;
	}


	/**
	 * Inserts the given element before or after the given key or value in the array.
	 *
	 * Allows inserting one or more elements, with or without keys. When inserting into a positional array, a value instead
	 * of a key should be given. If the given key or value is not present in the array, it's possible to optionally
	 * append the given element(s) to the array.
	 *
	 * @param array $array the array to insert the element to
	 * @param mixed $element the element(s) to insert to the array
	 * @param int|string $keyOrValue the key or value after which to insert the element
	 * @param bool $after optional - whether to insert the element(s) before or after the given key/value
	 *
	 * @return array
	 * @throws Exception
	 * @since 3.4.1
	 */
	public static function insert( array $array, $element, $keyOrValue, bool $after = true ) : array {

		$keys    = array_keys( $array );
		$isAssoc = self::is_assoc( $array );
		$index   = array_search( $keyOrValue, $isAssoc ? $keys : array_values( $array ), true );

		if ( $index !== false ) {
			$index = $after ? $index + 1 : $index;

			if ( $isAssoc ) {
				// union will simply append new keys/values to the preceding array if the key is not already present - which is why it won't
				// work for non-assoc arrays
				return array_slice( $array, 0, $index ) + ( is_array( $element ) ? $element : [ $element ] ) + array_slice( $array, $index );
			}

			array_splice( $array, $index, 0, $element );

			return $array;
		}

		// if the key/value was not found, append elements to the array
		return self::combine( $array, self::wrap( $element ) );
	}


	/**
	 * Inserts the given element before the given key or value in the array.
	 *
	 * Allows inserting one or more elements, with or without keys. When inserting into a positional array, a value instead
	 * of a key should be given. If the given key or value is not present in the array, it's possible to optionally
	 * append the given element(s) to the array.
	 *
	 * @param array $array the array to insert the element to
	 * @param mixed $element the element(s) to insert to the array
	 * @param int|string $keyOrValue the key or value after which to insert the element
	 *
	 * @return array
	 * @throws Exception
	 * @since 3.4.1
	 */
	public static function insert_before( array $array, $element, $keyOrValue ) : array {

		return self::insert( $array, $element, $keyOrValue, false );
	}


	/**
	 * Inserts the given element after the given key or value in the array.
	 *
	 * Allows inserting one or more elements, with or without keys. When inserting into a positional
	 * array, a value instead of a key should be given. If the given key or value is not present in
	 * the array, element(s) will be appended to/combined with the array.
	 *
	 * @param array $array the array to insert the element to
	 * @param mixed $element the element(s) to insert to the array
	 * @param int|string $keyOrValue the key or value after which to insert the element
	 *
	 * @return array
	 * @throws Exception
	 * @since 3.4.1
	 */
	public static function insert_after( array $array, $element, $keyOrValue ) : array {

		return self::insert( $array, $element, $keyOrValue );
	}


	/**
	 * Determines if an array is associative.
	 *
	 * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
	 *
	 * @param array $array
	 *
	 * @return bool
	 * @since 3.4.1
	 *
	 */
	public static function is_assoc( array $array ) : bool {

		$keys = array_keys( $array );

		return array_keys( $keys ) !== $keys;
	}


	/**
	 * Encodes an array to JSON.
	 *
	 * @param ArrayAccess|array $array
	 *
	 * @return string
	 * @throws JsonException
	 */
	public static function json_encode( array $array ) : string {

		return json_encode( $array, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}


	/**
	 * Plucks values from an array given a key with optional key assignment.
	 *
	 * @NOTE: The WordPress function {@see wp_list_pluck()} does not support multi-dimensional arrays in a standard way.
	 *
	 * @param array|ArrayAccess $array
	 * @param string|array|int $search
	 *
	 * @return array
	 */
	public static function pluck( $array, $search ) : array {

		$results = [];

		foreach ( $array as $item ) {
			if ( $value = self::get( $item, $search ) ) {
				$results[] = $value;
			}
		}

		return $results;
	}


	/**
	 * Converts the array into a query string.
	 *
	 * @NOTE: We use a custom function here instead of {@see add_query_arg()} because the WordPress function appends items to the current or given url.
	 * That can cause problems when using this class for non-standard WordPress redirects.
	 * This function uses the native {@see http_build_query()} instead.
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	public static function query( array $array ) : string {

		return http_build_query( $array, '', '&', PHP_QUERY_RFC3986 );
	}


	/**
	 * Removes a given key or keys from the original array.
	 *
	 * @param array $array
	 * @param array|string $keys
	 *
	 * @return void
	 */
	public static function remove( array &$array, $keys ) : void {

		foreach ( self::wrap( $keys ) as $key ) {

			// if the key exists at this level unset and bail
			if ( self::exists( $array, $key ) ) {
				unset( $array[ $key ] );

				continue;
			}

			// if the key doesn't exist at all then bail
			if ( ! self::has( $array, $key ) ) {
				continue;
			}

			$temporary = &$array;
			$segments  = explode( '.', $key );
			$levels    = count( $segments );

			// key exists so lets walk to it
			foreach ( $segments as $currentLevel => $segment ) {
				if ( $currentLevel <= $levels - 1 ) {
					unset( $temporary[ $segment ] );
				}

				$temporary = &$temporary[ $segment ];
			}
		}
	}


	/**
	 * Sets an array value from dot notated key.
	 *
	 * @param ArrayAccess|array $array
	 * @param string $search
	 * @param mixed $value
	 *
	 * @return mixed|void|null
	 */
	public static function set( &$array, string $search, $value = null ) {

		if ( ! self::accessible( $array ) ) {
			return;
		}

		foreach ( explode( '.', $search ) as $segment ) {
			if ( ! self::exists( $array, $segment ) ) {
				$array[ $segment ] = [];
			}

			$array = &$array[ $segment ];
		}

		return $array = $value;
	}


	/**
	 * Filters a given array by its callback.
	 *
	 * @param array $array
	 * @param callable $callback
	 * @param bool $maintainIndex
	 *
	 * @return array
	 */
	public static function where( array $array, callable $callback, bool $maintainIndex = true ) : array {

		$array = array_filter( $array, $callback, ARRAY_FILTER_USE_BOTH );

		return $maintainIndex ? $array : array_values( $array );
	}


	/**
	 * Wraps a given item in an array if it is not an array.
	 *
	 * @param mixed $item
	 *
	 * @return array
	 */
	public static function wrap( $item = null ) : array {

		if ( is_array( $item ) ) {
			return $item;
		}

		return $item ? [ $item ] : [];
	}


	/**
	 * Filters our null and empty values.
	 *
	 * @param array $array
	 * @param string|null $callback
	 * @return array
	 */
	public static function filter_empty( array $array, string $callback = null ) : array {

		if ( $callback && is_callable( $callback ) ) {
			$array = array_map( $callback, $array );
		}

		return array_values( array_filter( $array ) );
	}
}
