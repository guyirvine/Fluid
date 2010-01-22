<?php


class Fluid_Image {

	static private function convert( $input, $type, $font, $transparent ) {
		$im = new Imagick(); 
		if ( !is_null( $font ) )
			$im->setFont($font);
		$im->readImageBlob( $input );
		$im->setImageFormat($type); 
		if ( !is_null( $transparent ) )
			$im->paintTransparentImage($transparent, 0.0, 10);
		return $im->getimageblob();
	}


	static function asJpeg( $input, $font=null, $transparent=null ) {
		return self::convert( $input, 'jpeg', $font, $transparent );
	}

	static function asSvg( $input ) {
		return self::convert( $input, 'svg' );
	}

	static function asPng( $input, $font=null, $transparent=null ) {
		return self::convert( $input, 'png', $font, $transparent );
	}

}
