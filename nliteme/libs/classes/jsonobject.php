<?php

/**
***************************************************************************************************
 * @Author		Michal Krugly
 * 
 * Copyright (c) 2013 by Michal Krugly (mailto: mickrugly[at]gmail.com)
 * 
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 *   - Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 *   - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 *   - Neither the name of the Michal Krugly nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

 * DISCLAIMER:
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. 
 
**************************************************************************************************
**/

abstract class JsonObject extends ArrayObject
{
/* 
 * public
 */
	/*
	 * constructor
	 * input: array or json string
	 */ 
	public function __construct($datastructure = null)
	{
		if( ! empty($datastructure) ) 
		{
			if ( is_string($datastructure) ) {
				$datastructure = trim($datastructure);
                if ((substr($datastructure, 0, 1) == '{') && (substr($datastructure, -1, 1) == '}'))
                {
					$datastructure = json_decode($datastructure, true);
					switch (json_last_error()) 
					{
						case JSON_ERROR_NONE:
							break;
						case JSON_ERROR_DEPTH:
							$this->jsonError = Text::_('JSON_ERROR_DEPTH');
							$str = get_class()."::".__FUNCTION__.": ".Text::_('JSON_ERROR_DEPTH');
							Tracer::getInstance()->log($str, LOGLEV_ERROR);
							break;
						case JSON_ERROR_STATE_MISMATCH:
							$this->jsonError = Text::_('JSON_ERROR_STATE_MISMATCH');
							$str = get_class()."::".__FUNCTION__.": ".Text::_('JSON_ERROR_STATE_MISMATCH');
							Tracer::getInstance()->log($str, LOGLEV_ERROR);
							break;
						case JSON_ERROR_CTRL_CHAR:
							$this->jsonError = Text::_('JSON_ERROR_CTRL_CHAR');
							$str = get_class()."::".__FUNCTION__.": ".Text::_('JSON_ERROR_CTRL_CHAR');
							Tracer::getInstance()->log($str, LOGLEV_ERROR);
							break;
						case JSON_ERROR_SYNTAX:
							$this->jsonError = Text::_('JSON_ERROR_SYNTAX');
							$str = get_class()."::".__FUNCTION__.": ".Text::_('JSON_ERROR_SYNTAX');
							Tracer::getInstance()->log($str, LOGLEV_ERROR);
							break;
						case JSON_ERROR_UTF8:
							$this->jsonError = Text::_('JSON_ERROR_UTF8');
							$str = get_class()."::".__FUNCTION__.": ".Text::_('JSON_ERROR_UTF8');
							Tracer::getInstance()->log($str, LOGLEV_ERROR);
							break;
						default:
							$this->jsonError = Text::_('UNKNOWN_ERROR');
							$str = get_class()."::".__FUNCTION__.": ".Text::_('UNKNOWN_ERROR');
							Tracer::getInstance()->log($str, LOGLEV_ERROR);
							break;
					}
					//print_r($datastructure);
                }
			}
			$this->set($datastructure);
		}
 	}
 	
 	/*
 	 * function sets the content of the object i.e. builds the array
 	 */ 
 	abstract protected function set($datastructure);
 /*
    {
		foreach ($datastructure AS $key => $value) 
		{
				this->{$key} = $value;
        }
    }
 */
 
    /*
     * function sets param value specified by $name
     */
    public function setParam($name, $value)
    {
		// just in case: remove leading/trailing whitespaces from $name and $value
		$name = trim($name);
		if(is_string($value)) {
			$value = trim($value);
		} else if (is_array($value)) {
			//$value = array_map('trim', $value);
			$value = $this->array_map_recursive('trim', $value);
		}
		$this->offsetSet($name, $value);
	}
    
    /*
     * function returns param value specified by $name
     */
    public function getParam($name)
    {
		if( $this->offsetExists($name) ) {
			return $this[$name];
		} else {
			return null;
		}
		
	}
	
    /*
     * function removes the param with a given $name
     */
    public function delParam($name)
    {
		if( $this->offsetExists($name) ) {
			$this->offsetUnset($name);
		} 
	}
 
    /*
	 * function encodes the object into json string
	 * if $objToEncode is NULL - encode $this object (DEFAULT behaviour)
	 * else - encode $objToEncode (NOTE: TO BE USED ONLY with class function call i.e. JsonObject::encode($objToEncode))
	 */ 
    public function encode($objToEncode = null)
    {
        if( empty($objToEncode) ) {
			return json_encode( (array) $this, JSON_FORCE_OBJECT);
		} else {
			return json_encode( (array) $objToEncode, JSON_FORCE_OBJECT);
		}
    }
    
    public function getJsonError()
    {
		if( ! empty($this->jsonError) ) {
			return $this->jsonError;
		} else {
			return null;
		}
	}
    
	/*
	 * function makes it possible to use classic array_functions on the object, just as if it was an array
	 */ 
	public function __call($func, $argv)
    {
        if (!is_callable($func) || substr($func, 0, 6) !== 'array_')
        {
            throw new BadMethodCallException(__CLASS__.'->'.$func);
        }
        return call_user_func_array($func, array_merge(array($this->getArrayCopy()), $argv));
    }
    
    /*
     * helper function to enbale recursive array_map
     */ 
    private function array_map_recursive($func, array $arr) 
    {
		array_walk_recursive($arr, function(&$v) use ($func) {
			$v = $func($v);
		});
		return $arr;
	}
      
}


?>
