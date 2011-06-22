<?php
	#
	# $Id$
	#
	# XML::NiceTree::NiceNode - Nodes for XML::NiceTree.
	#

	require_once 'XML/TreeNS/NodeNS.php';

	class XML_NiceTree_NiceNode extends XML_TreeNS_NodeNS {

		function findSingle($list){

			$nodes = $this->findMulti($list);
			return $nodes[0];
		}

		function findSingleContent($list){

			$node = $this->findSingle($list);
			return $node->content;
		}

		function findMulti($list){

			$parts = explode('/', $list);

			$parent = $this;

			foreach ($parts as $part){

				$hits = array();

				foreach ($parent->children as $child){

					if ($child->name == $part){

						$hits[] = $child;
					}
				}

				if (!count($hits)) return array();
				$parent = $hits[0];
			}

			return $hits;
		}

		function findSingleAttribute($list, $attribute){

			$node = $this->findSingle($list);

			return $node ? $node->attributes[$attribute] : null;
		}
	}
?>