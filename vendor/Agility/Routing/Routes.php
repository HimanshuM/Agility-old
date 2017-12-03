<?php

namespace Agility\Routing;

	class Routes {

		private static $_sharedInstance;

		private function __construct() {

		}

		static function getSharedInstance() {

			if (is_null(self::$_sharedInstance)) {
				self::$_sharedInstance = new Routes;
			}
			return self::$_sharedInstance;

		}

		static function map($callback) {
			($callback->bindTo(self::$_sharedInstance))();
		}

	}

?>