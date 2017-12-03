<?php

namespace Agility\Exception;

	class PropertyNotFoundException extends Exception {

		function __construct($class, $property) {
			parent::__construct("Property $property not found on class $class");
		}

	}

?>