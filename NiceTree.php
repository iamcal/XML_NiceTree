<?php
	#
	# $Id$
	#
	# XML::NiceTree - A namespace aware XML tree with simple XPath-like methods.
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
