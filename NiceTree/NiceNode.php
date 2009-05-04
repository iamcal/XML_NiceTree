<?php
	#
	# $Id$
	#
	# XML::NiceTree::NiceNode - Nodes for XML::NiceTree.
	#
	# Copyright (c) 2007-2008 Yahoo! Inc.  All rights reserved.  This library is
	# free software; you can redistribute it and/or modify it under the terms of
	# the GNU General Public License (GPL), version 2 only.  This library is
	# distributed WITHOUT ANY WARRANTY, whether express or implied. See the GNU
	# GPL for more details (http://www.gnu.org/licenses/gpl.html)
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