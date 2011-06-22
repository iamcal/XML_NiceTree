<?php
	#
	# $Id$
	#
	# XML::TreeNS::NodeNS - Nodes for XML::TreeNS.
	#
	# Based on an old version of the now unmaintained PEAR XML::Tree::Node:
	# http://pear.php.net/package/XML_Tree
	#


	class XML_TreeNS_NodeNS {

		var $attributes;
		var $children;
		var $content;
		var $name;
		var $is_a_node = 1;

		function XML_TreeNS_NodeNS($name, $content = '', $attributes = array()){
			$this->attributes = $attributes;
			$this->children = array();
			$this->setContent($content);
			$this->name = $name;
		}

		function &addChild($child, $content = '', $attributes = array()){
			$index = sizeof($this->children);
			if (is_object($child)){
				if ($child->is_a_node){
					$this->children[$index] = $child;
				}
				if ($child->is_a_tree && isset($child->root)){
					$this->children[$index] = $child->root->get_element();
				}
			}else{
				$my_class = get_class($this);
				$this->children[$index] = new $my_class($child, $content, $attributes);
			}
			return $this->children[$index];
		}

		function &clone_node(){
			$clone=new XML_TreeNS_NodeNS($this->name,$this->content,$this->attributes);
			$max_child=count($this->children);
			for($i=0;$i<$max_child;$i++) {
				$clone->children[] = $this->children[$i]->clone();
			}
			return($clone);
		}

		function &insertChild($path,$pos,&$child, $content = '', $attributes = array()){
			array_splice($this->children,$pos,0,'dummy');
			if (is_object($child)){
				if ($child->is_a_node){
					$this->children[$pos]=&$child;
				}
				if ($child->is_a_tree && isset($child->root)){
					$this->children[$pos]=$child->root->get_element();
				}
			}else{
				$my_class = get_class($this);
				$this->children[$pos] = new $my_class($child, $content, $attributes);
			}
			return($this);
		}

		function &removeChild($pos) {
			return(array_splice($this->children,$pos,1));
		}

		function &get(){
			static $deep = -1;
			static $do_ident = true;
			$deep++;
			if ($this->name !== null){
				$ident = str_repeat('  ', $deep);
				if ($do_ident){
					$out = $ident . '<' . $this->name;
				}else{
					$out = '<' . $this->name;
				}
				foreach ($this->attributes as $name => $value){
					$out .= ' ' . $name . '="' . HtmlSpecialChars($value) . '"';
				}

				$out .= '>' . $this->content;

				if (sizeof($this->children) > 0){
					$out .= "\n";
					foreach ($this->children as $child){
						$out .= $child->get();
					}
				}else{
					$ident = '';
				}
				if ($do_ident){
					$out .= $ident . '</' . $this->name . ">\n";
				}else{
					$out .= '</' . $this->name . '>';
				}
				$do_ident = true;
			}else{
				$out = $this->content;
				$do_ident = false;
			}
			$deep--;
			return $out;
		}

		function getAttribute($name){
			return $this->attributes[strtolower($name)];
		}

		function &getElement($path){
			if (sizeof($path) == 0){
				return $this;
			}
			$next = array_shift($path);
			return $this->children[$next]->get_element($path);
		}

		function setAttribute($name, $value = ''){
			$this->attributes[strtolower($name)] = $value;
		}

		function unsetAttribute($name){
			unset($this->attributes[strtolower($name)]);
		}

		function setContent(&$content){
			$this->content = $content;
		}

		function dump(){
			echo $this->get();
		}
	}
?>