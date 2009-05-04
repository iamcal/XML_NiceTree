<?php
	#
	# $Id$
	#
	# XML::TreeNS - A namespace aware XML Tree parser.
	#
	# Based on an old version of the now unmaintained PEAR XML::Tree:
	# http://pear.php.net/package/XML_Tree
	#

	require_once 'XML/ParserNS.php';
	require_once 'XML/TreeNS/NodeNS.php';

	class XML_TreeNS extends XML_ParserNS {

		var $file = NULL;
		var $filename = '';
		var $namespace = array();
		var $root = NULL;
		var $version = '1.0';
		var $node_class = 'XML_TreeNS_NodeNS';
		var $is_a_tree = 1;

		function XML_TreeNS($filename = '', $version = '1.0'){
			$this->filename = $filename;
			$this->version  = $version;
		}

		function &addRoot($name, $content = '', $attributes = array()){
			$this->root = new $this->node_class($name, $content, $attributes);
			return $this->root;
		}

		function &addRootNS($name, $content, $attributes, $ns_url, $local_el){
			$this->root = new $this->node_class($name, $content, $attributes);

			$this->root->namespace = $ns_url;
			$this->root->local_name = $local_el;
			$this->root->clark_name = '{'.$ns_url.'}'.$local_el;

			return $this->root;
		}

		function &insertChild($path,$pos,$child, $content = '', $attributes = array()){
			$count=count($path);
			foreach($this->namespace as $key => $val){
				if ((array_slice($val,0,$count)==$path) && ($val[$count]>=$pos))
					$this->namespace[$key][$count]++;
			}
			$parent=&$this->get_node_by_path($path);
			return($parent->insert_child($pos,$child,$content,$attributes));
		}

		function &removeChild($path,$pos){
			$count=count($path);
			foreach($this->namespace as $key => $val){
				if (array_slice($val,0,$count)==$path){
					if ($val[$count]==$pos){ unset($this->namespace[$key]); break; }
					if ($val[$count]>$pos){ $this->namespace[$key][$count]--; }
				}
			}
			$parent=&$this->get_node_by_path($path);
			return($parent->remove_child($pos));
		}

		function &getTreeFromFile(){
			$this->folding = false;
			$this->XML_Parser(null, 'event');
			$ok = $this->setInputFile($this->filename);
			if (!$ok){
				return 0;
			}
			$this->cdata = null;
			$ok = $this->parse();
			if (!$ok){
				return 0;
			}
			return $this->root;
		}

		function getTreeFromString($str, $encoding = null){
			$this->folding = false;
			$this->XML_ParserNS($encoding, 'event');
			$this->cdata = null;
			$ok = $this->parseString($str);
			if (!$ok){
				return 0;
			}
			return $this->root;
		}

		function startHandler($xp, $elem, &$attribs, $ns_url, $local_el){
			if (!isset($this->i)){
				$this->obj1 =& $this->addRootNS($elem, null, $attribs, $ns_url, $local_el);
				$this->i = 2;
			}else{
				if (!empty($this->cdata)){
					$parent_id = 'obj' . ($this->i - 1);
					$parent    =& $this->$parent_id;
					$parent->children[] = &new $this->node_class(null, $this->cdata);
				}
				$obj_id = 'obj' . $this->i++;
				$this->$obj_id = &new $this->node_class($elem, null, $attribs);
				$this->$obj_id->namespace = $ns_url;
				$this->$obj_id->local_name = $local_el;
				$this->$obj_id->clark_name = '{'.$ns_url.'}'.$local_el;
			}
			$this->cdata = null;
			return null;
		}

		function endHandler($xp, $elem){
			$this->i--;
			if ($this->i > 1){
				$obj_id = 'obj' . $this->i;
				$node   =& $this->$obj_id;
				if (count($node->children) > 0){
					if (trim($this->cdata)){
						$node->children[] = &new $this->node_class(null, $this->cdata);
					}
				}else{
					$node->setContent($this->cdata);
				}
				$parent_id = 'obj' . ($this->i - 1);
				$parent    =& $this->$parent_id;
				$parent->children[] = $node;
			}
			$this->cdata = null;
			return null;
		}

		function cdataHandler($xp, $data){
			if (trim($data)){
				$this->cdata .= $data;
			}
		}

		function clone_tree(){
			$clone=new XML_TreeNS($this->filename,$this->version);
			$clone->root=$this->root->clone();
			$temp=get_object_vars($this);
			foreach($temp as $varname => $value)
				if (!in_array($varname,array('filename','version','root')))
					$clone->$varname=$value;
			return($clone);
		}

		function dump(){
			echo $this->get();
		}

		function &get(){
			$out = '<?xml version="' . $this->version . "\"?>\n";
			$out .= $this->root->get();
			return $out;
		}

		function &getName($name){
			return $this->root->get_element($this->namespace[$name]);
		}

		function registerName($name, $path){
			$this->namespace[$name] = $path;
		}
	}
?>