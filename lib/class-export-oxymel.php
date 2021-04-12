<?php

namespace WXR_Generator;

// In some contexts Oxymel is already loaded; in that case, don't load it again.
if ( ! class_exists( 'Oxymel' ) ) {
	require_once __DIR__ . '/Oxymel.php';
}

use Oxymel;

class Export_Oxymel extends Oxymel {
	public function optional( $tag_name, $contents ) {
		if ( $contents ) {
			$this->$tag_name( $contents );
		}
		return $this;
	}

	public function optional_cdata( $tag_name, $contents ) {
		if ( $contents ) {
			$this->$tag_name->contains->cdata( $contents )->end;
		}
		return $this;
	}

	public function cdata( $text ) {
		if ( ! seems_utf8( $text ) ) {
			$text = utf8_encode( $text );
		}
		return parent::cdata( $text );
	}
}
