<?php

namespace Agility\String;

	class Str {

		// CamelCase
		static function camelCase($string) {
			return $string;
		}

		// pascalCase
		static function pascalCase($string) {
			return $string;
		}

		// snake_case
		static function snakeCase($string, $delimiter = "_") {

			$matches = [];
			$string = lcfirst($string);
			preg_match_all("/[A-Z]/", $string, $matches);
			foreach ($matches[0] as $match) {
				$string = str_replace($match, "_".strtolower($match), $string);
			}

			return $string;
		}

	}

?>