<?php

namespace WP_Plugins\Boilerplate\Traits;

use ReflectionClass;
use ReflectionProperty;
use WP_Plugins\Boilerplate\Array_Helper;

/**
 * A trait that allows a given class/object to convert its state to an array.
 */
trait Can_Convert_To_Array_Trait {

	/** @var bool convert Private Properties to Array Output */
	protected bool $to_array_include_private = false;

	/** @var bool convert Protected Properties to Array Output */
	protected bool $to_array_include_protected = true;

	/** @var bool convert Public Properties to Array Output */
	protected bool $to_array_include_public = true;

	/** @var bool prevents infinite recursive calls */
	private bool $bail_if_in_recursive_call = false;


	/**
	 * Converts all class data properties to an array.
	 *
	 * @return array
	 */
	public function to_array() : array {

		if ( $this->bail_if_in_recursive_call ) {
			return [];
		}

		$this->bail_if_in_recursive_call = true;

		$excludes = $this->toArrayExcludes ?? [];

		$array = [];

		foreach ( ( new ReflectionClass( static::class ) )->getProperties() as $property ) {
			if ( $this->to_array_should_property_be_accessible( $property ) &&
			     ! Array_Helper::contains( $excludes, $property->getName() ) ) {
				$property->setAccessible( true );

				$value = $property->getValue( $this );

				$array[ $property->getName() ] = is_callable( [ $value, 'toArray' ] ) ? $value->toArray() : $value;
			}
		}

		$this->bail_if_in_recursive_call = false;

		return Array_Helper::except( $array, [
			'bailIfInRecursiveCall',
			'toArrayIncludePrivate',
			'toArrayIncludeProtected',
			'toArrayIncludePublic',
			'toArrayExcludes',
		] );
	}


	/**
	 * Checks if the property is accessible for {@see to_array()} conversion.
	 *
	 * @param ReflectionProperty $property
	 *
	 * @return bool
	 */
	private function to_array_should_property_be_accessible( ReflectionProperty $property ) : bool {

		if ( $this->to_array_include_public && $property->isPublic() ) {
			return true;
		}

		if ( $this->to_array_include_protected && $property->isProtected() ) {
			return true;
		}

		if ( $this->to_array_include_private && $property->isPrivate() ) {
			return true;
		}

		return false;
	}
}
