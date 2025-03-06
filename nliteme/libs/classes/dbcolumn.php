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
*
* DbColumn class defines database column parameters
* constructor argument can be an array or json string with relevant params
*
*/

class DbColumn extends JsonObject
{
	public function __construct($columnSettings)
	{
		parent::__construct($columnSettings);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{
		foreach ($datastructure AS $key => $value) 
		{
				//$this->{$key} = $value;
				$this->setParam($key, $value);
        }
	}
	
	/*
	 * function returns column real name - as defined in database
	 */
	public function getColumnRealName()
	{
		return $this->getParam('realname');
	}
	
	/*
	 * function sets column real name
	 */
	public function setColumnRealName($columnRealName)
	{
		$this->setParam('realname', $columnRealName);
	}
	
	/*
	 * function returns column value
	 */
	public function getColumnValue()
	{
		return $this->getParam('value');
	}
	
	/*
	 * function sets column value
	 */
	public function setColumnValue($value)
	{
		$this->setParam('value', $value);
	}
	
	/*
	 * function check if column value is set
	 */
	public function hasColumnValue()
	{
		$temp = $this->getParam('value');
		if( isset($temp) ) {
			return true;
		} else {
			return false;
		}
	}
}

?>
