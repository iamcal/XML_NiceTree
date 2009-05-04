<?php
	#
	# $Id$
	#
	# XML::NiceTree - A namespace aware XML tree with simple XPath-like methods.
	#
	# Copyright (c) 2007-2008 Yahoo! Inc.  All rights reserved.  This library is
	# free software; you can redistribute it and/or modify it under the terms of
	# the GNU General Public License (GPL), version 2 only.  This library is
	# distributed WITHOUT ANY WARRANTY, whether express or implied. See the GNU
	# GPL for more details (http://www.gnu.org/licenses/gpl.html)
	#

	require_once 'XML/TreeNS.php';
	require_once 'XML/NiceTree/NiceNode.php';

	class XML_NiceTree extends Xml_TreeNS {

		var $base;

		function XML_NiceTree($xml, $encoding = null){

			$this->node_class = 'XML_NiceTree_NiceNode';

			$this->getTreeFromString($xml, $encoding);

			$this->base = new XML_NiceTree_NiceNode('FAKE');
			$this->base->children = array($this->root);
		}

		function findMulti($list){
			return $this->base->findMulti($list);
		}

		function findSingle($list){
			return $this->base->findSingle($list);
		}

		function findSingleContent($list){
			return $this->base->findSingleContent($list);
		}

		function findSingleAttribute($list, $attribute){
			return $this->base->findSingleAttribute($list, $attribute);
		}
	}

?>
