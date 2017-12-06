<?php

namespace Agility;

use Agility\Extensions\Enum;

	class Environment extends Enum {

		const __default = self::Development;

		const Development = "development";
		const Testing = "testing";
		const Production = "production";

	}

?>