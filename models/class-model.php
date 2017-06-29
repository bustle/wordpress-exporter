<?php

namespace Bustle;

/**
 * Class Model
 *
 * Base model with generic setter/getter methods
 *
 * @package Bustle
 */
abstract class Model {

	/**
	 * The original WordPress model
	 *
	 * @var mixed
	 */
	protected $wordpress_model;

	/**
	 * The formatted result of the model conversion
	 *
	 * @var array
	 */
	protected $attributes = [];


	/**
	 * Set the model's attribute
	 *
	 * @param string $attribute The name of the model attribitute, e.g. "title" in Post.title.
	 * @param mixed  $value     The attribute value.
	 *
	 * @return array The updated model's attributes
	 */
	protected function set_attribute( $attribute, $value ) {

		if ( array_key_exists( $attribute, $this->attributes ) ) {

			$model_name = strtolower( str_replace( 'Bustle\\', '', get_called_class() ) );

			// Allow for the value to be filtered per model and attribute.
			$value = apply_filters( "bustle_exporter_{$model_name}_attribute_{$attribute}", $value,
			$this->wordpress_model );

			// By default, all attributes are escaped with esc_attr. Allow for custom callback like esc_url or __return_false (to not sanitize).
			$sanitizer = apply_filters( "bustle_exporter_{$model_name}_attribute_{$attribute}_sanitizer", 'esc_attr' );

			if ( is_callable( $sanitizer ) ) {
				$value = call_user_func( $sanitizer, $value );
			}

			$this->attributes[ $attribute ] = $value;
		}

		return $this->attributes;
	}

	/**
	 * Return the current model
	 *
	 * @return array|mixed
	 */
	public function get_model() {

		$attributes = $this->attributes;

		$model_name = strtolower( str_replace( 'Bustle\\', '', get_called_class() ) );

		// Final filter to change the model.
		$attributes = apply_filters( "bustle_exporter_{$model_name}_model", $attributes, $this->wordpress_model );

		return $attributes;
	}
}
