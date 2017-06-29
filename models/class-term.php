<?php

namespace Bustle;

/**
 * Class Term
 *
 * @see \Bustle\Model
 * @package Bustle
 */
class Term extends Model {

	/**
	 * Term attributes
	 *
	 * @var array
	 */
	protected $attributes = [
		'name'        => '',
		'slug'        => '',
		'description' => '',
		'updatedAt'   => 0,
		'createdAt'   => 0,
	];

	/**
	 * Term constructor.
	 *
	 * @param \WP_Term $term The term object.
	 */
	public function __construct( $term ) {

		add_filter( 'bustle_exporter_term_attribute_updatedAt_sanitizer', 'intval' );
		add_filter( 'bustle_exporter_term_attribute_createdAt_sanitizer', 'intval' );

		if ( is_a( $term, '\WP_Term' ) ) {
			$this->convert_to_model( $term );
		}

	}

	/**
	 * Convert the default WordPress properties to a Bustle\Term model
	 *
	 * @param \WP_Term $term The term object.
	 *
	 * @return array
	 */
	private function convert_to_model( $term ) {

		$this->set_attribute( 'name', $term->name );
		$this->set_attribute( 'slug', $term->slug );
		$this->set_attribute( 'description', $term->description );
		$this->set_attribute( 'updatedAt', time() );
		$this->set_attribute( 'createdAt', time() );

		return $this->attributes;

	}

}
