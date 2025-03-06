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
 * Description class defines a record in testcases table
 */
abstract class Description extends DbRecord
{
	
	public function __construct($tableName,$dbColumnList = null)
	{
		parent::__construct(TABLE_PREFIX.$tableName, 'id', $dbColumnList);
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
		[ID] => id
		[DESCRIPTION] => description
		 */
		 
		// this columns can be explicitly set 
		$allowedColumns = array('id', 'description');
		return $allowedColumns;
	}

	/*
	 * function returns the list of ignored columns i.e. columns that shall not be 
	 * added to this DB table, but may be needed in the object for other purposes e.g. for updating another object
	 */ 
	public function getIgnoredColumns()
	{
		$ignoredColumns = array();
		return $ignoredColumns;
	}

	/*
	 * function validates if the Testcase exists in db
	 */
	public function is_inDb()
	{
		$selectQuery = new DbQueryBuilder($this->getTableName(), array('id'));
		$selectQuery->addCondition("id='".$this->getId()."'");
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
		$this->setDbColumnValue('id', $value);
	}
	
	public function setDescription($value)
	{
		$this->setDbColumnValue('description', $value);
	}
	
	/*
	 * get functions - used to get record fields
	 */
	public function getId()
	{
		return $this->getDbColumnValue('id');
	}
	
	public function getIdColumn()
	{
		return $this->getDbColumn('id');
	}
	
	public function getIdColumnName()
	{
		return 'id';
	}			
	
	public function getDescription()
	{
		return $this->getDbColumnValue('description');
	}
	
	// other abstract functions implemented for the sake of consistency
	public function getName()
	{
		return $this->getDbColumnValue('id');
	}
	
	public function getNameColumn()
	{
		return $this->getDbColumn('id');
	}
	
	public function getNameColumnName()
	{
		return 'id';
	}			
	
	public function getHash()
	{
		return '';
	}
}

/*
 * Here are the classes using Description
 */

class BuildIncrementDescription extends Description
{
	public function __construct($dbColumnList = null)
	{
		parent::__construct('build_increments_description', $dbColumnList);
	} 
}

class BuildDescription extends Description
{
	public function __construct($dbColumnList = null)
	{
		parent::__construct('builds_description', $dbColumnList);
	} 
}

class TestSuiteDescription extends Description
{
	public function __construct($dbColumnList = null)
	{
		parent::__construct('testsuites_description', $dbColumnList);
	} 
}

class TestCaseDescription extends Description
{
	public function __construct($dbColumnList = null)
	{
		parent::__construct('testcases_description', $dbColumnList);
	} 
}

class TestLineDescription extends Description
{
	public function __construct($dbColumnList = null)
	{
		parent::__construct('testlines_description', $dbColumnList);
	} 
}

class TestResultDescription extends Description
{
	public function __construct($dbColumnList = null)
	{
		parent::__construct('testresults_description', $dbColumnList);
	} 
}

class FeatureDescription extends Description
{
	public function __construct($dbColumnList = null)
	{
		parent::__construct('features_description', $dbColumnList);
	} 
}

?>
