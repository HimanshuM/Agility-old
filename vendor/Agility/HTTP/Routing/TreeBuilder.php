<?php

namespace Agility\HTTP\Routing;

	class TreeBuilder {

		static function buildTree($parent, $urlFragments, $n, $i, $finalObject) {

			$urlFragments[$i] = (intval($urlFragments[$i]) ? "_".$urlFragments[$i] : $urlFragments[$i]);

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