<?php

namespace WP_Plugins\Boilerplate\Traits;

use ReflectionClass;

/**
 * A trait for objects where properties could be set in bulk.
 */
trait Can_Bulk_Assign_Properties_Trait {

	/**
	 * Sets all class properties that have setter methods using the given data.
	 *
	 * @param array|null $data property values
	 *
	 * @return $this
	 */
	public function setProperties( ?array $data ) : self {

		if ( ! $data ) {
			return $this;
		}

		foreach ( ( new ReflectionClass( static::class ) )->getProperties() as $property ) {
			if ( ! isset( $data[ $property->getName() ] ) ) {
				continue;
			}

			if ( method_exists( $this, 'set_' . $property->getName() ) ) {
				$this->{'set_' . $property->getName()}( $data[ $property->getName() ] );
			}
		}

		return $this;
	}
}
