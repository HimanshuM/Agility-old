<?php

namespace Agility\HTTP\Mime;

use Agility\Extensions\Enum;

	class MimeTypes extends Enum {

		const __default = "text/plain";

		const Html = "text/html";
		const Json = "application/json";
		const Xml = "application/xml";
		const Plain = "text/plain";

		private static $_mimeTypes = [
			"text/html" => "html",
			"application/json" => "json",
			"application/xml" => "xml",
			"application/xml+xhtml" => "xml",
			"text/plain" => "text"
		];

		static function register($acceptString, $name) {

			if (!isset(self::$_mimeTypes[$acceptString])) {
				self::$_mimeTypes[$acceptString] = $name;
			}

		}

		function __get($mimeType) {

			foreach (self::$_mimeTypes as $acceptString => $name) {

				if ($name == $mimeType) {
					return $acceptString;
				}

			}

			throw new Exception("No Mime type is registered with name $mimeType", 1);

		}

	}

?>