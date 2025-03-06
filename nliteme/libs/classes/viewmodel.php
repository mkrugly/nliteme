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
 *
 * Defines classes that will be directly used when rendering twig templates
 * 
 */ 


/*
 * LeftColumn Class is a hash table where key is submenuId and value is an arrays of objects used as menu itens
 */ 
class LeftColumn extends ArrayObject
{
/* private */
	private $submenuInx2NameMap = array();
	private $commonScriptUrl = null;

/* public */

	/* 
	 * constructor sets default Url script name
	 */  
	public function __construct($scriptUrl)
	{
		$this->commonScriptUrl = $scriptUrl;
	}
	
	/* 
	 * function returns submenuName for a given submenuId if it exists or null otherwise
	 */  
	public function getScriptUrl()
	{
		return $this->commonScriptUrl;
	}
	
	/* 
	 * function sets mapping between submenuId (key) and  submenuName (value)
	 */  
	public function setScriptUrl($scriptUrl)
	{
		$this->commonScriptUrl = $scriptUrl;
	}
	
	
	/* 
	 * function returns submenuName for a given submenuId if it exists or null otherwise
	 */  
	public function getSubmenuName($submenuId)
	{
		if( isset($this->submenuInx2NameMap[$submenuId]) ) {
			return $this->submenuInx2NameMap[$submenuId];
		} else {
			return NULL;
		}
	}
	
	/* 
	 * function sets mapping between submenuId (key) and  submenuName (value)
	 */  
	public function setSubmenuName($submenuId, $submenuName)
	{
		$this->submenuInx2NameMap[$submenuId] = $submenuName;
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
}

/*
 * MainContentTable defines main content table
 * $headers is a DbColumnList with mapping between real column name and the name to be displayed in html table - can be null!
 * $row is an array of Records objects used as source for feeling the html table row
 */ 
class MainContentTable
{
/* private */
	private $link;
	private $headers;
	private $rows = array(); // array of Record objects

/* public */
	public function __construct($queryResultSet, DbColumnList &$showableColumnList, Links $link = null)
	{
		$this->link = $link;
		// generate column headers
		$this->headers = $showableColumnList;
		
		if($queryResultSet) 
		{
			foreach($queryResultSet->GetRows() AS $record)
			{
				$this->addMainContentRecord($record);
			}
		}
	}
	
	/* 
	 * function initializes $headers array (mapping between real and display name)
	 */
	public function initHeaderList(DbColumnList &$showableColumnList)
	{
		if( ! empty($showableColumnList) )
		{
			$iterator = $showableColumnList->getIterator();
			while( $iterator->valid() )
			{
				$this->headers[$iterator->current()->getColumnRealName()] = $iterator->current();
				$iterator->next();
			}
		}
	}
	
	/* 
	 * function returns $headers array (mapping between real and display name)
	 */	 
	public function getHeaderList()
	{
		 return $this->headers;
	}
	
	/* 
	 * function creates a main content record in the suitable format from input array 
	 * (in form of keys - column realname as in DB, values - corresponding column value)
	 */	 
	public function addMainContentRecord($dbRow)
	{
		if( $dbRow instanceof Record) {
			array_push($this->rows, $dbRow);
		} else {	
			// prepare an ordering list for the Record object
			$orderingList = array();
			if( ! empty($this->headers) ) {
				foreach( $this->headers as $key => $value)
				{
					if( isset($dbRow[$key]) )
					{
						array_push($orderingList, $key);
						// if there is a predefined value for the column set it
						if ( $value->hasPredefinedValues() && $value->getColumnPredefinedValuebyIndex($dbRow[$key]) ) {
							$dbRow[$key] = $value->getColumnPredefinedValuebyIndex($dbRow[$key]);
						} 
					}	
				}
			} else {
				foreach( $dbRow as $key => $value)
				{
					array_push($orderingList, $key);
				}
			}
				
			if(! empty($orderingList) )
			{
				$rec = new Record($orderingList, $dbRow);
				array_push($this->rows, $rec);
			}
		} 
	}
	/* 
	 * function returns $maincontent array
	 */	 
	public function getMainContent()
	{
		 return $this->rows;
	}
	 
	public function setLink(Link $link)
	{
		 $this->link = $link;
	} 
	 
	public function getLink()
	{
		 return $this->link;
	}
	
}

/*
 * Table Row record
 */ 
class Record extends ArrayObject
{
/* private */
	private $orderingList = array();
	private $dbRow = array();
	
/* public */
	public function __construct($orderingList, $dbRow)
	{
		// generate column headers
		$this->orderingList = $orderingList;
		$this->dbRow = $dbRow;
	}

	/* 
	 * function returns orderingList
	 */	 
	public function getOrderingList()
	{
		 return $this->orderingList;
	}
	
	/* 
	 * function returns orderingList count (length of array)
	 */	 
	public function getOrderingListCount()
	{
		 return count($this->orderingList);
	}	
		
	/* 
	 * function returns dbRow array (row of resultSet from sql query)
	 */	 
	public function getDbRow()
	{
		 return $this->dbRow;
	}
	
	/* 
	 * function returns number of fields in dbRow (length of array)
	 */	 
	public function getDbRowCount()
	{
		 return count($this->dbRow);
	}	
}

/*
 * SearcherForm Class 
 */ 
class SearcherForm extends ArrayObject
{
	private $submitUrl = null;
	private $inputs =array();
	
	public function __construct(ArrayObject& $inputsList, $submitUrl)
	{
		$this->submitUrl = $submitUrl;
		$this->inputs = $inputsList;
	}

	public function getSubmitUrl()
	{
		return $this->submitUrl;
	}
	
	public function setSubmitUrl($submitUrl)
	{
		$this->submitUrl = $submitUrl;
	}
	
	public function getInputs()
	{
		return $this->inputs;
	}
	
	public function setInputs(array $inputsList)
	{
		$this->inputs = $inputsList;
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
}

?>
