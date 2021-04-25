<?php

namespace WXR_Generator;

// In some contexts Oxymel is already loaded; in that case, don't load it again.
if ( ! class_exists( 'Oxymel' ) ) {
	require_once __DIR__ . '/Oxymel.php';
}

use Oxymel;

// I'm not sure what the purpose of this class is.
// Seems like optional/optional_cdata aren't used
// And if the whole purpose is the utf8 detection/encoding, is that the right place for it?

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
