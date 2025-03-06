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
* Builds class defines a list of Build objects used to update database tables
* it is possible to add DbRecord object at anytime
*/
class Builds extends DbRecords
{
	public function __construct($buildList = null)
	{
		parent::__construct($buildList);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($buildList)
	{
		// parent constructor makes sure the argument is not empty
		// and is either DbRecord object or array from which the DbRecord object will be created
		foreach ($buildList AS $key => $value) 
		{
			if( $value instanceof Build ) {
				$this->append( $value );
			/*
			} else if ( is_array($value) ) {
				$this->append( new Build($value) );	
			}
			*/
			} else {
				$this->append( new Build($value) );
			}  
        }
	}
	
	/*
	 * function add Build object to the list
	 */ 
	public function addToList(Build $build)
	{
		$this->append( $build );
	}
	
}

/*
 * Build class defines a record in builds table
 */
class Build extends DbRecord
{
	
	public function __construct($dbColumnList = null)
	{
		parent::__construct(TABLE_PREFIX.'builds', 'buildid', $dbColumnList);
	}

	/*
	 * function defines how the object will look like
	 */ 
	//protected function set($dbColumnList)
	//{
	//	parent::set($dbColumnList);
	//}
	
	/*
	 * function returns a list of columns that can be defined
	 */
	public function getAllowedColumns()
	{
		/*
		[BUILDID] => buildid
		[CREATEDATE] => createdate
		[TESTDATE] => testdate
		[BUILD] => build
		[INCID] => incid
		[SHORTDESCRIPTION] => shortdescription
		[DESCRIPTION] => description
		 */
		$allowedColumns = array('buildid', 'build', 'createdate', 'testdate', 'incid', 'shortdescription', 'description', 'increment');
		return $allowedColumns;
	}
	
	/*
	 * function returns the list of ignored columns i.e. columns that shall not be 
	 * added to this DB table, but may be needed in the object for other purposes e.g. for updating another object
	 */ 
	public function getIgnoredColumns()
	{
		$ignoredColumns = array('buildid', 'increment', 'description');
		return $ignoredColumns;
	}

	/*
	 * function validates if the Build exists in db
	 */
	public function is_inDb()
	{
		$selectQuery = new DbQueryBuilder($this->getTableName(), array('buildid'));
		$selectQuery->addCondition("build='".$this->getName()."'");
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
		$this->setDbColumnValue('buildid', $value);
	}
	
	public function setName($value)
	{
		$this->setDbColumnValue('build', $value);
	}
	
	public function setCreateDate($value)
	{
		$this->setDbColumnValue('createdate', $value);
	}

	public function setTestDate($value)
	{
		$this->setDbColumnValue('testdate', $value);
	}
	
	public function setIncId($value)
	{
		$this->setDbColumnValue('incid', $value);
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
	 * used to set increment by name
	 * !!! it will not be added to db, 
	 * !!! it may be used though for automated (curl api based) updated by name
	 */
	public function setIncrement($value)
	{
		$this->setDbColumnValue('increment', $value);
	}
	  
	/*
	 * !!! Url is currently not part of builds table record, therefore set outside of DbColumnList !!!
	 */
	public function setUrl($value)
	{
		$this->setParam('url', $value);
	}
	
	/*
	 * get functions - used to get record fields
	 */
	public function getId()
	{
		return $this->getDbColumnValue('buildid');
	}
	
	public function getIdColumn()
	{
		return $this->getDbColumn('buildid');
	}
	
	public function getIdColumnName()
	{
		return 'buildid';
	}		
	
	public function getName()
	{
		return $this->getDbColumnValue('build');
	}

	public function getNameColumn()
	{
		return $this->getDbColumn('build');
	}
	
	public function getNameColumnName()
	{
		return 'build';
	}		
	
	public function getCreateDate()
	{
		return $this->getDbColumnValue('createdate');
	}

	public function getTestDate()
	{
		return $this->getDbColumnValue('testdate');
	}
	
	public function getIncId()
	{
		return $this->getDbColumnValue('incid');
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
	
	/*
	 * used to set increment by name
	 * !!! it will not be added to db, 
	 * !!! it may be used though for automated (curl api based) updated by name
	 */
	public function getIncrement()
	{
		return $this->getDbColumnValue('increment');
	}
	
	public function getUrl()
	{
		return $this->getParam('url');
	}
}



 
 ?>
