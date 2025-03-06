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

/*
* TestLines class defines a list of TestLine objects used to update database tables
* it is possible to add TestLine object at anytime
*/
class TestLines extends DbRecords
{
	public function __construct($testlineList = null)
	{
		parent::__construct($testlineList);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($testlineList)
	{
		// parent constructor makes sure the argument is not empty
		// and is either DbRecord object or array from which the DbRecord object will be created
		foreach ($testlineList AS $key => $value) 
		{
			if( $value instanceof TestLine ) {
				$this->append( $value );
			/*
			} else if ( is_array($value) ) {
				$this->append( new TestLine($value) );	
			}
			*/
			} else {
				$this->append( new TestLine($value) );	
			}			 
        }
	}
	
	/*
	 * function add TestLine object to the list
	 */ 
	public function addToList(TestLine $testline)
	{
		$this->append( $testline );
	}
	
}

/*
 * TestLine class defines a record in testlines table
 */
class TestLine extends DbRecord
{
	
	public function __construct($dbColumnList = null)
	{
		parent::__construct(TABLE_PREFIX.'testlines', 'tlid', $dbColumnList);
	}

	/*
	 * function defines how the object will look like
	 */ 
	//protected function set($dbColumnList)
	//{
	//	parent::set($dbColumnList);
	//}
	
	/*
	 * function returns a list of columns that can be defined for modification of DB table
	 */
	public function getAllowedColumns()
	{
		/* columns in the testcases table
		[TLID] => tlid
		[CREATEDATE] => createdate
		[TLNAME] => tlname
		[SHORTDESCRIPTION] => shortdescription
		[DESCRIPTION] => description
		 */
		 
		// this columns can be explicitly set 
		$allowedColumns = array('tlid', 'tlname', 'createdate', 'shortdescription', 'description');
		return $allowedColumns;
	}

	/*
	 * function returns the list of ignored columns i.e. columns that shall not be 
	 * added to this DB table, but may be needed in the object for other purposes e.g. for updating another object
	 */ 
	public function getIgnoredColumns()
	{
		$ignoredColumns = array('tlid', 'description');
		return $ignoredColumns;
	}

	/*
	 * function validates if the TestLine exists in db
	 */
	public function is_inDb()
	{
		$selectQuery = new DbQueryBuilder($this->getTableName(), array('tlid'));
		$selectQuery->addCondition("tlname='".$this->getName()."'");
		$rs = $selectQuery->executeQuery();
		if(! $rs->EOF ) {
			return true;
		} else {
			return false;
		}
	}
	
	/*
	 * set functions - used to set record fields
	 */
	public function setId($value)
	{
		$this->setDbColumnValue('tlid', $value);
	}
	
	public function setName($value)
	{
		$this->setDbColumnValue('tlname', $value);
	}
	
	public function setCreateDate($value)
	{
		$this->setDbColumnValue('createdate', $value);
	}
	
	public function setShortDescription($value)
	{
		$this->setDbColumnValue('shortdescription', $value);
	}
	
	public function setDescription($value)
	{
		$this->setDbColumnValue('description', $value);
	}

	private function setHash($value)
	{		
		$this->setDbColumnValue('hash', md5($value));
	}
	
	/*
	 * get functions - used to get record fields
	 */
	public function getId()
	{
		return $this->getDbColumnValue('tlid');
	}

	public function getIdColumn()
	{
		return $this->getDbColumn('tlid');
	}
	
	public function getIdColumnName()
	{
		return 'tlid';
	}		
	
	public function getName()
	{
		return $this->getDbColumnValue('tlname');
	}
	
	public function getNameColumn()
	{
		return $this->getDbColumn('tlname');
	}
	
	public function getNameColumnName()
	{
		return 'tlname';
	}		
	
	public function getCreateDate()
	{
		return $this->getDbColumnValue('createdate');
	}
	
	public function getShortDescription()
	{
		return $this->getDbColumnValue('shortdescription');
	}
	
	public function getDescription()
	{
		return $this->getDbColumnValue('description');
	}

	public function getHash()
	{
		return $this->getDbColumnValue('hash');
	}	
} 
 ?>
