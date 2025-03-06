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

* Text class is static. Just to try out an alternative to singleton
* 
 */

class Text
{
	private static $initialized;
	private static $texts = array();
	private function __construct() {}
	private function __clone() {}

	/*
	 * function returns a user readable string (if exists) defined in text.ini file for a given keyword
	 * otherwise the keyword itself
	 */ 
	public static function _($stringNameFromIniFile)
	{
		if( empty(self::$initialized) )
		{
			self::reinitialize();
		}
		
		if(! empty(self::$texts[$stringNameFromIniFile]) ) {
			return self::$texts[$stringNameFromIniFile];
		} else {
			return $stringNameFromIniFile;
		} 
	}
	
	/*
	 * function reads init file and initlizes the $text array with keywords and user strings
	 */
	public static function reinitialize($textIniFilePath = null)
	{
		if( empty($textIniFilePath) )
		{
			$textIniFilePath = dirname(__FILE__).'/../../etc/text.ini';
		}
		$ini = parse_ini_file($textIniFilePath);
		if($ini !== false) {
			self::$texts = $ini;
		} else {
			$str = get_class()."::".__FUNCTION__.": Parsing of INI file: ".$textIniFilePath." failed!";
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
		}
		
		self::$initialized = true;
	}
}

class Text4Twig
{
	public function _($stringNameFromIniFile)
	{
		return Text::_($stringNameFromIniFile);
	}
}

?>
