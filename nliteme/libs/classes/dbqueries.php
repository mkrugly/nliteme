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

* Db queries
* holds simple, often used db queries
* 

 **/

class DbQueries{
/* private */	
	private static $instance;
	private function __construct() {}
	private function __clone() {}

/* public */	
    /*
     * function returns a reference to DbQueries singleton instance 
     */ 
	public static function getInstance()
	{
        if (! isset(self::$instance) ) {
			self::$instance = new DbQueries();
        }
        return self::$instance;
    }
	
	/*
	 *  function returns a list of Build objects
	 */ 
	public function getLastNBuildsOfType($buildType, $numOfBuildsToReturn = NULL)
	{
		if(! isset($numOfBuildsToReturn) )
		{
			$numOfBuildsToReturn = Config::getInstance()->getSettings()->getParam("numberOfBuildToReturn");
		}
		//print get_class($this)."::".__FUNCTION__." numberOfBuildToReturn=".$numOfBuildsToReturn."\n";
		$selectQuery = new DbQueryBuilder(TABLE_PREFIX."builds");
		//$selectQuery->addColumns(array('buildid','build','FROM_UNIXTIME(@@testdate@@, \'%Y-%m-%d %H:%i:%s\') as testdate'));
		$selectQuery->addColumns(array('buildid','build','testdate'));
		$selectQuery->addCondition('buildtype = '.$buildType, SQLLOGOPER::_AND);
		$selectQuery->addOrderBy('testdate', SQLORDERBY::_DESC);
		$selectQuery->addLimit(0,$numOfBuildsToReturn);
		//print_r($selectQuery->getQuery()."\n");
		$rs = $selectQuery->executeQuery();
		$buildsArr = new Builds();
		if( ! empty($rs) && $rs->FieldCount() == 3)
		{
		   while (!$rs->EOF) 
		   {
             //array_push($buildsArr, new Build($rs->fields[0], $rs->fields[1], $buildType, $rs->fields[2]));
             $newBuild = new Build(TABLE_PREFIX."builds");
             $newBuild->setId($rs->fields['buildid']);
             $newBuild->setName($rs->fields['build']);
             $newBuild->setTestDate($rs->fields['testdate']);
             $newBuild->setType($buildType);
	 		 $buildsArr->addToList($newBuild);
	 		 $rs->MoveNext();
	 	   } 
		}
		return $buildsArr;
	}
	/*
	 *  function returns a build type name for a given buildType index
	 */ 
	public function getGetBuildTypeName($buildType)
	{
		$name = NULL;
		$buildTypeCol = Config::getInstance()->getColumnConfig()->getDbColumnByRealName('buildtype');
		if( ! empty($buildTypeCol) && $buildTypeCol->hasPredefinedValues() )
		{
			$vals = $buildTypeCol->getColumnPredefinedValues();
			if( isset($vals[$buildType]) )
			{
				$name = $vals[$buildType];
				//print get_class($this)."::".__FUNCTION__." name=".$name."\n";
			}
		}
		return $name;
	}
	
	/*
	 *  function returns an array with build type ids
	 */ 
	public function getGetBuildTypes()
	{
		$buildTypesArr = NULL;
		$buildTypeCol = Config::getInstance()->getColumnConfig()->getDbColumnByRealName('buildtype');
		if( ! empty($buildTypeCol) && $buildTypeCol->hasPredefinedValues() )
		{
			$buildTypesArr = array_keys($buildTypeCol->getColumnPredefinedValues());
		}
		return $buildTypesArr;
	}
	
	/*
	 * function returns a dbColumnList with searchable columns
	 */
	public function getSearchableColumnList()
	{
		$searchColumnList = new DbColumnList();
		$iterator = Config::getInstance()->getColumnConfig()->getIterator();
		while( $iterator->valid() )
		{
			if($iterator->current()->is_searchable())
			{
				// first add new column
				$searchColumnList->addColumn(clone $iterator->current());
				// then get the handle to the new column object
				$newColumnObject = $searchColumnList->getDbColumnByRealName($iterator->current()->getColumnRealName());
				if( ! $newColumnObject->hasPredefinedValues() && 
					//! empty($iterator->current()->getSearcherFieldType()) &&
					$newColumnObject->getSearcherFieldType() === FIELDTYPES::_SM)
				{
					// try to find predefined values
					// currently for tcname and builds
					$arr = array();
					if( $newColumnObject->getColumnRealName() === 'tcname') {
						$selectQuery = new DbQueryBuilder(TABLE_PREFIX.'testcases');
						$selectQuery->addColumns(array('tcid','tcname'));
						$selectQuery->addOrderBy('tcname',SQLORDERBY::_ASC);
						$result = $selectQuery->executeQuery();
						if($result)
						{
							foreach($result->GetRows() AS $row)
							{
								$arr[$row['tcid']] = $row['tcname'];
							}
						}
						// Column Real Name has to be here overwritten to tcid, since we want to search by tcId
						$newColumnObject->setColumnRealName('tcid');
					} else if( $newColumnObject->getColumnRealName() === 'build') {
						$selectQuery = new DbQueryBuilder(TABLE_PREFIX.'builds');
						$selectQuery->addColumns(array('buildid','build'));
						$selectQuery->addOrderBy('build',SQLORDERBY::_ASC);
						$result = $selectQuery->executeQuery();
						if($result)
						{						
							foreach($result->GetRows() AS $row)
							{
								$arr[$row['buildid']] = $row['build'];
							}
						}
						// Column Real Name has to be here overwritten to buildid, since we want to search by buildId
						$newColumnObject->setColumnRealName('buildid');
					}
					$newColumnObject->setColumnPredefinedValues($arr);
				}
			}	
			$iterator->next();
		}
		return $searchColumnList;
	}
	
	/*
	 * function return a dbColumnList with showable columns
	 */ 
	public function getShowableColumnList()
	{
		$showableColumnList = new DbColumnList();
		$iterator = Config::getInstance()->getColumnConfig()->getIterator();
		while( $iterator->valid() )
		{
			if($iterator->current()->is_showable())
			{
				$showableColumnList->addColumn(clone $iterator->current());
			}	
			$iterator->next();
		}
		return $showableColumnList;
	}
	
	/* 
	 * function takes an optional (NULL means default conditions to be applied) conditions object as parameter
	 * returns sql query result either record count or complete recordset
	 */ 
	public function getTestResults(QueryConditions &$querycond = null, $offset=0, $countOnly = false)
	{
	   /*
		* 3. as 2 but with ordering by 2 columns
		* select t.filename, t.tcid, tc.tcname, t.buildid, b.build from prgreg_testresults as t inner join prgreg_testcases as tc on t.tcid=tc.tcid inner join prgreg_builds as b * on  t.buildid=b.buildid where ( b.buildid=799) order by  tc.tcname asc ,b.testdate desc; 
		*/ 
		$selectQuery = new DbQueryBuilder(TABLE_PREFIX.'testresults');
		$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','tcid'); // inner join TABLE testcases on tcid field
		$selectQuery->addJoinTable(TABLE_PREFIX.'builds','buildid'); // inner join TABLE builds on buildid field
		// add conditions
		if( empty($querycond) || count($querycond) == 0 ) { // default
			// limit the default query to last 7 days, to speed up
			$t = time() - (7 * 24 * 60 * 60);
			$tmp = '@@createdate@@ >= '."'".date('Y-m-d', $t)."'";
			$selectQuery->addCondition($tmp);
		} else { // user defined
			$iterator = $querycond->getIterator();
			while( $iterator->valid() )
			{
				$tmp = $iterator->current();
				// special handling for tcname and build cases
				// this is needed when searching by string, not mulitple selection box, because the names are stored in a different tables
				if($iterator->key() === 'tcname' ) {
					$tmp = preg_replace('/@@(.*?)@@/', TABLE_PREFIX.'testcases'.'.${1}',$iterator->current(),-1,$count);
				} else if ( $iterator->key() === 'build' ) {
					$tmp =preg_replace('/@@(.*?)@@/', TABLE_PREFIX.'builds'.'.${1}',$iterator->current(),-1,$count);
				}
				$selectQuery->addCondition($tmp);
				$iterator->next();
			}	
		}

		// if only record count requested
		if( $countOnly === true ) {
			$selectQuery->addColumns(array('COUNT(@@id@@) as c')); // add columns to select from TABLE testresults
			//print_r($selectQuery->getQuery());
		// if test resutls are to be returned
		} else {
			$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('tcname'),TABLE_PREFIX.'testcases'); // add columns to select from TABLE testcases
			$selectQuery->addColumns(array('build'),TABLE_PREFIX.'builds'); // add columns to select from TABLE testcases
					
			// add ordering
			$selectQuery->addOrderBy('tcname',SQLORDERBY::_ASC,TABLE_PREFIX.'testcases');
			//$selectQuery->addOrderBy('createdate',SQLORDERBY::_DESC,TABLE_PREFIX.'builds');
			//$selectQuery->addOrderBy('createdate',SQLORDERBY::_DESC,TABLE_PREFIX.'testresults');
			
			// add limit
			$pagLimit = Config::getInstance()->getSettings()->getParam("paginationLimit");
			$selectQuery->addLimit($offset*$pagLimit,$pagLimit);
			//print_r($selectQuery->getQuery());
		}
		
		$result = $selectQuery->executeQuery();
		return $result;
	}
	
	/* 
	 * function returns a number of records for a given set of conditions
	 * NOTE. to simplify it uses only testresults table, so the assumption is the QueryConditions object has conditions on columns from this table
	 */ 
	public function getNumOfTestResults(QueryConditions &$querycond = null)
	{
		$numOfRecords = 0;
		
		$result = $this->getTestResults($querycond, 0, true);

		if($result)
		{
			$row = $result->FetchRow();
			$numOfRecords = $row['c'];
		}
		//print_r("</BR>".$numOfRecords);
		return $numOfRecords;
	}

}

/*
 * class creates a list of ready sql conditions 
 * @@columnName@@ - @@ tag is used to tell Query builder it should put the tablename. in front of column name
 */ 
class QueryConditions extends JsonObject
{
	public function __construct($conditionList)
	{
		parent::__construct($conditionList);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{	
		foreach ($datastructure AS $key => $value) 
		{
				if( is_array($value) ) 
				{
					$tmp = array();
					foreach ($value as $k => $v)
					{
						if ( isset($v) && $v != '' )
						{
							$tmp[$k] = '@@'.$key.'@@'."=".$v;
						}
					}
					$value = implode(' OR ', $tmp);
					if (! empty($value) )
					{
						$this->setParam($key, '('.$value.')');
					}
				} else if (! empty($value) ) {
					if ($key === 'startdate') {
						$value = '@@createdate@@ >= '."'".$value."'";
					} else if ($key === 'enddate') {
						$value = '@@createdate@@ <= '."'".$value." 23:59:59'";
					} else {
						$value = '@@'.$key.'@@'." LIKE CONCAT('".trim($value)."','%')";
					}
					$this->setParam($key, $value);
				}
        }
	}
}

?>
