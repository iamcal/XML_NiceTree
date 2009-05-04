<?php
	#
	# $Id$
	#
	# XML::ParserNS - A namespace aware XML parser.
	#
	# Based on an old version of the the PEAR XML::Parser module:
	# http://pear.php.net/package/XML_Parser
	#

	class XML_ParserNS {

		var $parser;
		var $fp;
		var $folding = false;
		var $mode;
		var $handler = array(
			'character_data_handler'		=> 'cdataHandler',
			'default_handler'			=> 'defaultHandler',
			'processing_instruction_handler'	=> 'piHandler',
			'unparsed_entity_decl_handler'		=> 'unparsedHandler',
			'notation_decl_handler'			=> 'notationHandler',
			'external_entity_ref_handler'		=> 'entityrefHandler'
		);
		var $srcenc;
		var $tgtenc;
		var $use_call_user_func = true;
		var $last_error;

		function XML_ParserNS($srcenc = null, $mode = "event", $tgtenc = null){

			if ($srcenc === null){
				$xp = @xml_parser_create();
			}else{
				$xp = @xml_parser_create($srcenc);
			}
			if (is_resource($xp)){
				if ($tgtenc !== null){
					if (!@xml_parser_set_option($xp, XML_OPTION_TARGET_ENCODING, $tgtenc)){
						return $this->raiseError("invalid target encoding");
					}
				}
				$this->parser = $xp;
				$this->setMode($mode);
				xml_parser_set_option($xp, XML_OPTION_CASE_FOLDING, $this->folding);
			}
			$this->srcenc = $srcenc;
			$this->tgtenc = $tgtenc;
		}

		function setMode($mode){

			$this->mode = $mode;

			xml_set_object($this->parser, $this);

			switch ($mode){

				case "func":
					// use call_user_func() when php >= 4.0.7
					// or call_user_method() if not
					if (version_compare(phpversion(), '4.0.7', 'lt')){
						$this->use_call_user_func = false;
					}else{
						$this->use_call_user_func = true;
					}
					xml_set_element_handler($this->parser, "funcStartHandler", "funcEndHandler");
					break;

				case "event":
					xml_set_element_handler($this->parser, "startHandlerStub", "endHandlerStub");
					break;
			}

			foreach ($this->handler as $xml_func => $method){
				if (method_exists($this, $method)){
					$xml_func = "xml_set_" . $xml_func;
					$xml_func($this->parser, $method);
				}
			}
		}

		function setInputFile($file){
			$fp = @fopen($file, "rb");
			if (is_resource($fp)) {
				$this->fp = $fp;
				return $fp;
			}
			return $this->raiseError($php_errormsg);
		}

		function setInput($fp){
			if (is_resource($fp)){
				$this->fp = $fp;
				return true;
			}
			return $this->raiseError("not a file resource");
		}

		function parse(){
			if (!is_resource($this->fp)){
				return $this->raiseError("no input");
			}
			while ($data = fread($this->fp, 2048)){
				$ok = $this->parseString($data, feof($this->fp));
				if (!$ok){
					fclose($this->fp);
					return 0;
				}
			}
			fclose($this->fp);
			return true;
		}

		function parseString($data, $eof = false){
			if (!xml_parse($this->parser, $data, $eof)){
				$this->raiseError($this->parser);
				xml_parser_free($this->parser);
				return 0;
			}
			return true;
		}

		function funcStartHandler($xp, $elem, $attribs){
			$func = 'xmltag_' . $elem;
			if (method_exists($this, $func)){
				if ($this->use_call_user_func){
					call_user_func(array(&$this, $func), $xp, $elem, $attribs);
				}else{
					call_user_method($func, $this, $xp, $elem, $attribs);
				}
			}
		}

		function funcEndHandler($xp, $elem){
			$func = 'xmltag_' . $elem . '_';
			if (method_exists($this, $func)){
				if ($this->use_call_user_func){
					call_user_func(array(&$this, $func), $xp, $elem);
				}else{
					call_user_method($func, $this, $xp, $elem);
				}
			}
		}

		function startHandler($xp, $elem, &$attribs, $ns, $node_name){
			return NULL;
		}

		function endHandler($xp, $elem, $ns, $node_name){
			return NULL;
		}

		######################################################################################

		var $ns_stack = array();
		var $ns_name_stack = array();
		var $ns_url_stack = array();
		var $DEFAULT_NS_STR = 'DEFAULT_NS';

		function startHandlerStub($xp, $elem, &$attribs){

			#
			# create namespace alias tag
			#

			$ns = array();

			foreach($attribs as $key => $value){

				if (StrToLower($key) == 'xmlns'){

					$ns[$this->DEFAULT_NS_STR] = $value;
					unset($attribs[$key]);

				}else if (preg_match('/^xmlns:(.+)$/i', $key, $matches)){

					$ns[$matches[1]] = $value;
					unset($attribs[$key]);
				}		
			}

			$this->ns_stack[] = $ns;

			#
			# get name and namespace
			#

			list($node_name, $ns) = $this->get_name_and_ns($elem);

			$this->ns_name_stack[] = $node_name;
			$this->ns_url_stack[] = $ns;

			return $this->startHandler($xp, $elem, $attribs, $ns, $node_name);
		}

		function endHandlerStub($xp, $elem){
			array_pop($this->ns_stack);
			$node_name = array_pop($this->ns_name_stack);
			$ns = array_pop($this->ns_url_stack);	
			return $this->endHandler($xp, $elem, $ns, $node_name);
		}

		function get_name_and_ns($full){
			list($a, $b) = explode(':', $full, 2);
			if (!strlen($b)){
				return array($a, $this->get_default_ns());
			}
			foreach(array_reverse($this->ns_stack) as $ns){
				foreach($ns as $key => $value){
					if ($key == $a){
						return array($b, $value);
					}
				}
			}
			return array($a, $this->get_default_ns());
		}

		function get_default_ns(){
			foreach(array_reverse($this->ns_stack) as $ns){
				foreach($ns as $key => $value){
					if ($key == $this->DEFAULT_NS_STR){
						return $value;
					}
				}
			}
			return '';
		}

		function raiseError($str){
			$this->last_error = $str;
			return 0;
		}

		function cleanup(){
			xml_parser_free($this->parser);
		}

		######################################################################################
	}
?>