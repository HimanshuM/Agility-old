<?php

namespace Agility\Routing;

	class TreeBuilder {

		static function buildTree($parent, $urlFragments, $n, $i, $finalObject) {

			if ($i == $n - 1) {
				return array_merge($parent, [$urlFragments[$i] => [$finalObject]]);
			}
			else {

				if (isset($parent[$urlFragments[$i]])) {

					$parent[$urlFragments[$i]] = TreeBuilder::buildTree($parent[$urlFragments[$i]], $urlFragments, $n, $i + 1, $finalObject);
					return $parent;

				}
				else {

					$parent[$urlFragments[$i]] = [];
					return array_merge($parent, [$urlFragments[$i] => TreeBuilder::buildTree($parent[$urlFragments[$i]], $urlFragments, $n, $i + 1, $finalObject)]);

				}

			}

		}

	}

?>