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
* Testcases class defines a list of Testcase objects used to update database tables
* it is possible to add Testcase object at anytime
*/
class TestCases extends DbRecords
{
	public function __construct($testcaseList = null)
	{
		parent::__construct($testcaseList);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($testcaseList)
	{
		// parent constructor makes sure the argument is not empty
		// and is either DbRecord object or array from which the DbRecord object will be created
		foreach ($testcaseList AS $key => $value) 
		{
			if( $value instanceof TestCase ) {
				$this->append( $value );
			/*
			} else if ( is_array($value) ) {
				$this->append( new TestCase($value) );	
			}
			*/
			} else {
				$this->append( new TestCase($value) );	
			}			 
        }
	}
	
	/*
	 * function add Testcase object to the list
	 */ 
	public function addToList(TestCase $testcase)
	{
		$this->append( $testcase );
	}
	
}

/*
 * TestCase class defines a record in testcases table
 */
class TestCase extends DbRecord
{
	
	public function __construct($dbColumnList = null)
	{
		parent::__construct(TABLE_PREFIX.'testcases', 'tcid', $dbColumnList);
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
		[TCID] => tcid
		[CREATEDATE] => createdate
		[TCNAME] => tcname
		[SHORTDESCRIPTION] => shortdescription
		[DESCRIPTION] => description
		[FID] => fid
		[COVERAGE] => coverage
		 */
		 
		// this columns can be explicitly set 
		$allowedColumns = array('tcid', 'tcname', 'createdate', 'shortdescription', 'description', 'fid', 'fname', 'flink', 'coverage');
		return $allowedColumns;
	}

	/*
	 * function returns the list of ignored columns i.e. columns that shall not be 
	 * added to this DB table, but may be needed in the object for other purposes e.g. for updating another object
	 */ 
	public function getIgnoredColumns()
	{
		$ignoredColumns = array('tcid', 'description', 'flink', 'fname');
		return $ignoredColumns;
	}

	/*
	 * function validates if the Testcase exists in db
	 */
	public function is_inDb()
	{
		$selectQuery = new DbQueryBuilder($this->getTableName(), array('tcid'));
		$selectQuery->addCondition("tcname='".$this->getName()."'");
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
		$this->setDbColumnValue('tcid', $value);
	}
	
	public function setName($value)
	{
		$this->setDbColumnValue('tcname', $value);
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
    
	public function setCoverage($value)
	{
		$this->setDbColumnValue('coverage', $value);
	}
    
	public function setFeatureId($value)
	{
		$this->setDbColumnValue('fid', $value);
	}
	
	/*
	 * used to set feature by name and potential external link
	 * !!! it will not be added to db, 
	 * !!! it may be used though for automated (curl api based) updated by name
	 */
	public function setFeature($value)
	{
		$this->setDbColumnValue('fname', $value);
	}
	
	public function setFeatureHlink($value)
	{
		$this->setDbColumnValue('flink', $value);
	}
	
	/*
	 * get functions - used to get record fields
	 */
	public function getId()
	{
		return $this->getDbColumnValue('tcid');
	}
	
	public function getIdColumn()
	{
		return $this->getDbColumn('tcid');
	}
	
	public function getIdColumnName()
	{
		return 'tcid';
	}	
	
	public function getName()
	{
		return $this->getDbColumnValue('tcname');
	}
	
	public function getNameColumn()
	{
		return $this->getDbColumn('tcname');
	}

	public function getNameColumnName()
	{
		return 'tcname';
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
    
	public function getCoverage()
	{
		return $this->getDbColumnValue('coverage');
	}
    
	public function getFeatureId()
	{
		return $this->getDbColumnValue('fid');
	}
	/*
	 * used to set feature by name and posible external link
	 * !!! it will not be added to db, 
	 * !!! it may be used though for automated (curl api based) updated by name
	 */
	public function getFeature()
	{
		return $this->getDbColumnValue('fname');
	} 
	
	public function getFeatureHlink()
	{
		return $this->getDbColumnValue('flink');
	}  
}

 ?>
