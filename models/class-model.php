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
	 * @var mixed The original WordPress model
	 */
	protected $wordpress_model;

	/**
	 * @var array The formatted result of the model conversion
	 */
	protected $attributes = [];


	/**
	 * Set the model's attribute
	 *
	 * @param $key string
	 * @param $value mixed
	 *
	 * @return array
	 */
	protected function set_attribute( $key, $value ) {

		if ( array_key_exists( $key, $this->attributes ) ) {

			$model_name = strtolower( str_replace( 'Bustle\\', '', get_called_class() ) );

			$value = apply_filters( "bustle_exporter_{$model_name}_attribute_{$key}", $value, $this->wordpress_model );

			$sanitizer = apply_filters( "bustle_exporter_{$model_name}_attribute_{$key}_sanitizer", 'esc_attr' );

			if ( is_callable( $sanitizer ) ) {
				$value = call_user_func( $sanitizer, $value );
			}

			$this->attributes[ $key ] = $value;
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

		$attributes = apply_filters( "bustle_exporter_{$model_name}_model", $attributes, $this->wordpress_model );

		return $attributes;
	}
}
