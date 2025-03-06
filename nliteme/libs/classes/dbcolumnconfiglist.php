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
* DbColumnConfigList class is a list of DbColumnConfig objects
* constructor argument can be an array or json string with contain DbColumnConfig structures
*
*/

class DbColumnConfigList extends DbColumnList
{
	public function __construct($columnlist = null)
	{
		parent::__construct($columnlist);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{	
		foreach ($datastructure AS $key => $value) 
		{
			if( is_array($value) ) {
				$subarr = new DbColumnConfig($value);
				$value = $subarr;
				$key = $value->getColumnRealName();
			}
			//$this->{$key} = $value;
			$this->setParam($key, $value);
		}
	}
	
	/*
	 * function returns an assoc array with column real names and column indices 
	 * if column index is not set, null is provided
	 */  
	public function getDbColumnsRealNamesIndexMap()
	{
		$toRetArr = array();
		$iterator = $this->getIterator();
		while( $iterator->valid() )
		{
			$toRetArr[$iterator->key()] = $iterator->current()->getColumnIndex();
			$iterator->next();
		}
		return $toRetArr;	
	}
	
	/*
	 * function returns a list with column indices 
	 * non exsiting column indices are skipped 
	 */  
	public function getDbColumnsIndices()
	{
		$toRetArr = array();
		$iterator = $this->getIterator();
		while( $iterator->valid() )
		{
			$inx = $iterator->current()->getColumnIndex();
			if(!empty($inx))
			{
				array_push($toRetArr, $inx);
			}
			$iterator->next();
		}
		return $toRetArr;
	}
}
?>
