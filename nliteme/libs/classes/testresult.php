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
* TestResults class defines a list of TestResult objects
*/
class TestResults extends DbRecords
{
	public function __construct($testResultList = null)
	{
		parent::__construct($testResultList);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($testResultList)
	{
		// parent constructor makes sure the argument is not empty
		// and is either TestResult object or array from which the TestResult object will be created
		//print_r($testResultList);
		foreach ($testResultList AS $key => $value) 
		{
			//print(memory_get_usage() . "\n");
			if( $value instanceof TestResult ) {
				$this->append( $value );
			/*
			} else if ( is_array($value) ) {
				$this->append( new TestResult($value) );	
			}
			*/
			} else {
				$this->append( new TestResult($value) );
			}			 
        }
	}
	
	/*
	 * function add TestResult object to the list
	 */ 
	function addToList(TestResult $testResult)
	{
		$this->append( $testResult );
	}
	
}

/*
 * Build class defines a record in builds table
 */
class TestResult extends DbRecord
{
	
	public function __construct($dbColumnList = null)
	{
		parent::__construct(TABLE_PREFIX.'testresults', 'id', $dbColumnList);
		// calculate md5 hash value if 'build' column value set
		$name = $this->getName();
		if(! empty($name) )
		{
			$this->setHash($name);
		}
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
		/* columns in the testresults table
		[ID] => id
		[CREATEDATE] => createdate
		[INCID] => incid
		[BUILDID] => buildid
		[TSID] => tsid
		[TCID] => tcid
		[TLID] => tlid
		[TCVERDICT] => tcverdict
		[EXTRACOLUMN_0] => extracolumn_0
		[EXTRACOLUMN_1] => extracolumn_1
		[EXTRACOLUMN_2] => extracolumn_2
		[EXTRACOLUMN_2] => extracolumn_3        
		[DURATION] => duration
		[DESCRIPTION] => description
		[FILEPATH] => filepath
		[HASH] => hash
		 */
		
		// this columns can be explicitly set 
		$allowedColumns = array('id', 'createdate', 'incid', 'buildid', 'tsid', 'tcid', 'tlid', 'tcverdict', 
								'extracolumn_0', 'extracolumn_1', 'extracolumn_2', 'extracolumn_3', 'duration', 'description', 'filepath', 'hash',
								'build', 'increment', 'tsname', 'tcname', 'tlname', 'fname', 'coverage', 'fid', 'flink' );
		return $allowedColumns;
	}
	
	/*
	 * function returns the list of ignored columns i.e. columns that shall not be 
	 * added to this DB table, but may be needed in the object for other purposes e.g. for updating another object
	 */ 
	public function getIgnoredColumns()
	{
		/*
		 */ 
		$ignoredColumns = array('id', 'build', 'increment', 'tsname', 'tcname', 'tlname', 'description', 'fname', 'coverage', 'fid', 'flink');
		return $ignoredColumns;
	}
	
	/*
	 * function validates if the Build exists in db
	 */
	public function is_inDb()
	{
		$selectQuery = new DbQueryBuilder($this->getTableName(), array('hash'));
		$selectQuery->addCondition("hash='".$this->getHash()."'");
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
	
	public function setName($value)
	{
		$this->setDbColumnValue('filepath', $value);
		// set md5 hash value
		$this->setHash($this->getName());
	}
	
	public function setCreateDate($value)
	{
		$this->setDbColumnValue('createdate', $value);
	}

	public function setIncId($value)
	{
		$this->setDbColumnValue('incid', $value);
	}

	public function setBuildId($value)
	{
		$this->setDbColumnValue('buildid', $value);
	}
	
	public function setTsId($value)
	{
		$this->setDbColumnValue('tsid', $value);
	}
	
	public function setTcId($value)
	{
		$this->setDbColumnValue('tcid', $value);
	}
	
	public function setTlId($value)
	{
		$this->setDbColumnValue('tlid', $value);
	}

	public function setVerdict($value)
	{
		$this->setDbColumnValue('tcverdict', $value);
	}

	public function setExtraColumn0($value)
	{
		$this->setDbColumnValue('extracolumn_0', $value);
	}
	
	public function setExtraColumn1($value)
	{
		$this->setDbColumnValue('extracolumn_1', $value);
	}

	public function setExtraColumn2($value)
	{
		$this->setDbColumnValue('extracolumn_2', $value);
	}
	public function setExtraColumn3($value)
	{
		$this->setDbColumnValue('extracolumn_3', $value);
	}
	
	public function setDuration($value)
	{
		$this->setDbColumnValue('duration', $value);
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
	 * used to set some parameters by name
	 * !!! it will not be added to table 
	 * !!! it may be used though for automated (curl api based) updated by name
	 */
	public function setIncrement($value)
	{
		$this->setDbColumnValue('increment', $value);
	}

	public function setBuild($value)
	{
		$this->setDbColumnValue('build', $value);
	}
	
	public function setTestSuite($value)
	{
		$this->setDbColumnValue('tsname', $value);
	}
	
	public function setTestCase($value)
	{
		$this->setDbColumnValue('tcname', $value);
	}
	
	public function setTestLine($value)
	{
		$this->setDbColumnValue('tlname', $value);
	}
    
 	public function setFeature($value)
	{
		$this->setDbColumnValue('fname', $value);
	}
	
	public function setCoverage($value)
	{
		$this->setDbColumnValue('coverage', $value);
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
	
	public function getName()
	{
		return $this->getDbColumnValue('filepath');
	}
	
	public function getNameColumn()
	{
		return $this->getDbColumn('filepath');
	}
	
	public function getNameColumnName()
	{
		return 'filepath';
	}		
	
	public function getCreateDate()
	{
		return $this->getDbColumnValue('createdate');
	}
	
	public function getIncId()
	{
		return $this->getDbColumnValue('incid');
	}

	public function getBuildId()
	{
		return $this->getDbColumnValue('buildid');
	}	
	
	public function getTsId()
	{
		return $this->getDbColumnValue('tsid');
	}
	
	public function getTcId()
	{
		return $this->getDbColumnValue('tcid');
	}
	
	public function getTlId()
	{
		return $this->getDbColumnValue('tlid');
	}

	public function getVerdict()
	{
		return $this->getDbColumnValue('tcverdict');
	}
	
	public function getExtraColumn0()
	{
		return $this->getDbColumnValue('extracolumn_0');
	}
	
	public function getExtraColumn1()
	{
		return $this->getDbColumnValue('extracolumn_1');
	}

	public function getExtraColumn2()
	{
		return $this->getDbColumnValue('extracolumn_2');
	}

	public function getExtraColumn3()
	{
		return $this->getDbColumnValue('extracolumn_3');
	}

	public function getDuration()
	{
		return $this->getDbColumnValue('duration');
	}	
	
	public function getDescription()
	{
		return $this->getDbColumnValue('description');
	}

	public function getNumOfPassedTcs()
	{
		return $this->getDbColumnValue('filename');
	}

	public function getHash()
	{		
		return $this->getDbColumnValue('hash');
	}
	
	/*
	 * used to set some parameters by name
	 * !!! it will not be added to table 
	 * !!! it may be used though for automated (curl api based) updated by name
	 */
	public function getIncrement()
	{
		return $this->getDbColumnValue('increment');
	}

	public function getBuild()
	{
		return $this->getDbColumnValue('build');
	}
	
	public function getTestSuite()
	{
		return $this->getDbColumnValue('tsname');
	}
	
	public function getTestCase()
	{
		return $this->getDbColumnValue('tcname');
	}
	
	public function getTestLine()
	{
		return $this->getDbColumnValue('tlname');
	}  
    
	public function getFeature()
	{
		return $this->getDbColumnValue('fname');
	} 

	public function getCoverage()
	{
		return $this->getDbColumnValue('coverage');
	} 
	
	public function getFeatureHlink()
	{
		return $this->getDbColumnValue('flink');
	} 
}

?>
