<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CarieraCore implements ArrayAccess {
	protected $contents;

	function __construct() {
		$this->contents = [];
	}
	public function offsetSet( $offset, $value ): void {
		$this->contents[ $offset ] = $value;
	}

	public function offsetExists( $offset ): bool {
		return isset( $this->contents[ $offset ] );
	}

	public function offsetUnset( $offset ): void {
		unset( $this->contents[ $offset ] );
	}

	public function offsetGet( $offset ): mixed {
		if ( is_callable( $this->contents[ $offset ] ) ) {
			return call_user_func( $this->contents[ $offset ], $this );
		}
		return isset( $this->contents[ $offset ] ) ? $this->contents[ $offset ] : null;
	}

	public function run() {
		foreach ( $this->contents as $key => $content ) { // Loop on contents.
			if ( is_callable( $content ) ) {
				$content = $this[ $key ];
			}
			if ( is_object( $content ) ) {
				$reflection = new ReflectionClass( $content );
				if ( $reflection->hasMethod( 'run' ) ) {
					$content->run(); // Call run method on object.
				}
			}
		}
	}
}
