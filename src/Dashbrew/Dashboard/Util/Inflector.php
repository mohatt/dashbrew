<?php

namespace Dashbrew\Dashboard\Util;

class Inflector {

	public static function camelize($string) {

		$string = str_replace('_', ' ', $string);
		$string = str_replace('-', ' ', $string);
		$string = ucwords($string);
		$string = str_replace(' ', '', $string);

		return $string;
	}

	public static function titleize($string) {

		$string = preg_replace('/[A-Z]/', ' $0', $string);
		$string = trim(str_replace('_', ' ', $string));
		$string = ucwords($string);

		return $string;
	}
	
	public static function underscore($string) {

		$string = preg_replace('/[A-Z]/', ' $0', $string);
		$string = str_replace(' ', '_', trim(strtolower($string)));

		return $string;
	}
	
	public static function dash($string) {

		$string = preg_replace('/[A-Z]/', ' $0', $string);
		$string = str_replace(' ', '-', trim(strtolower($string)));

		return $string;
	}	
}
