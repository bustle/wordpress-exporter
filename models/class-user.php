<?php

namespace Bustle;

/**
 * Class User
 *
 * @see \Bustle\Model
 * @package Bustle
 */
class User extends Model {

	/**
	 * The user attributes
	 *
	 * @var array
	 */
	protected $attributes = [
		'email'    => '',
		'name'     => '',
		'role'     => '',
		'bio'      => '',
		'mediaUrl' => '',
		'staffer'  => false,
	];

	/**
	 * User constructor.
	 *
	 * @param \WP_User $user The user object.
	 */
	public function __construct( $user ) {

		if ( is_a( $user, '\WP_User' ) ) {
			$this->convert_to_model( $user );
		}

	}


	/**
	 * Convert the default WordPress properties to a Bustle\User model
	 *
	 * @param \WP_User $user The user object.
	 *
	 * @return array
	 */
	private function convert_to_model( $user ) {
		$user_meta = get_userdata( $user->ID );

		$this->set_attribute( 'name', $user->display_name );
		$this->set_attribute( 'role', $user_meta->roles[0] );
		$this->set_attribute( 'bio', $user->user_description );
		$this->set_attribute( 'mediaUrl', get_avatar_url( $user ) );

		return $this->attributes;

	}

}
