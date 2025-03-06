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
**/

class TestActivitiesModel extends Model
{
	private $numOfDays = null;
	private $numOfBuilds = null;
	private $resultSetTotal = null;
	private $verdictCountQuery = null;
	private $outputArray = array();
	private $columnConfig = null; // DbColumnList containing column config information retrieved from preferences
	private $groupingColumns = array(); // realcolumn name used for grouping records
	private $verdictConditions = array(); // list of conditions for count verdict selection
	private $staticConditions = null; // condition string specifying additional condition for getTotalResPerBuildPerTsPerTlInLastNDays
	/*
	 * constructor
	 */ 
	public function __construct(array $groupingColumns=array())
	{
		$this->numOfDays = 3; //to be taken from preferences; if 0 then do not perform lastNdays query
		$this->numOfBuilds = 10; //to be taken from preferences; if 0 then do not perform lastNBuilds query
		$this->getColumnConfig('testresults');
		$this->groupingColumns = $groupingColumns; //to be taken from preferences
	}
	
	/*
	 * function to perform show details action
	 */ 
	public function show()
	{
		$result = false;
		// try to get total count for the activities in last Ndays
		if(! empty($this->numOfDays))
		{
			$result = $this->getTotalResInLastNDays($this->numOfDays);
		}
		// try to get total count for activies for N last tested builds
		// 2 cases: only this type was requested or both are set and total count for numOfDays is 0
		if(! empty($this->numOfBuilds) && $result === false)
		{
			$result = $this->getTotalResForLastNBuilds($this->numOfBuilds);
		}		
		

		// based on the Total results get the verdicts results counts
		// will be done per total result row, otherwise the query will be too complex (with lots of tempfiles and sorting) and hence inefficient
		if($result === true && !empty($this->resultSetTotal)) {
			foreach($this->resultSetTotal->GetRows() AS $row)
			{
				$valuesForVerdictConditions = $this->getValuesForVerdictConditions($row);
				$rs = $this->getVerdictsCountQuery()->executeQuery($valuesForVerdictConditions);
				if(! $rs->EOF ) 
				{
					// set verdict counts in form of array of verdict => count pairs
					$row['tcverdict'] = $rs->GetAssoc();
					// set passcount value
					$row['passcount'] = (isset($row['tcverdict'][0]) ? $row['tcverdict'][0] : 0);
					// calculate pass rate
					$row['passrate'] = round((($row['totalcount'] != 0 && isset($row['tcverdict'][0])) ? (100 * $row['tcverdict'][0] / $row['totalcount']) : 0),1) . '%';
					// create link arguments
					$row['link-args'] = array_combine(array_keys($this->verdictConditions),$valuesForVerdictConditions);
					//$row['link-args'] = implode('&',array_map(function($k, $v){return "$k=$v";}, array_keys($this->verdictConditions),$valuesForVerdictConditions));
				}
				array_push($this->outputArray,$row);
			}
			if(empty($this->outputArray)) { $result = false; }
		}
		return $result;
	}

	/*
	 * modifies time range e.g. last ndays for SQL query or from To date for build test dates.
	 * modified parameter is used in case $limitType == LIMITTYPE::_TIME_ONLY)
	 */
	public function setNumOfDays($numOfDaysLimit)
	{
		$this->numOfDays = $numOfDaysLimit;
	}
	
	public function getNumOfDays()
	{
		return $this->numOfDays;
	}

	/*
	 * modifies numOfBuildsLimit for SQL query
	 * modified parameter is used in case $limitType != LIMITTYPE::_TIME_ONLY)
	 */
	public function setNumOfBuildsLimit($numOfBuildsLimit)
	{
		$this->numOfBuilds = $numOfBuildsLimit;
	}

	public function getNumOfBuildsLimit()
	{
		return $this->numOfBuilds;
	}

	/*
	 * sets static conditions to be added to SQL query
	 * will be staticaly added at the begining of WHERE statement
	 */ 		
	public function setStaticConditions(array $conditionList)
	{
		if(! empty($conditionList) )
		{
			$arr = array();
			foreach ($conditionList AS $key => $value) 
			{
				$key = $this->checkAndAddTableTag($key);
				if( is_array($value) ) 
				{
					$tmp = array();
					foreach ($value as $k => $v)
					{
						if ( isset($v) && $v != '' )
						{
							$tmp[$k] = $key."=".$v;
						}
					}
					$value = implode(' OR ', $tmp);
					if (! empty($value) )
					{
						array_push($arr, '('.$value.')');
					}
				} else if (! Utils::emptystr($value) ) {
					array_push($arr, $key."='".$value."'");
				}
			}
			
			$this->staticConditions = implode(' AND ', $arr);
		}
	}
	
	/*
	 * returns a recordset with records used a table body in the view
	 */ 
	public function getOutputArray()
	{
		return $this->outputArray;
	}
	/*
	 * returns an array of column names used for grouping (GROUP BY)
	 */ 	
	public function getGroupingColumns()
	{
		return $this->groupingColumns;
	}
	/*
	 * returns an array of column names for which the values are present in record and which may be used for table header in the view
	 */ 	
	public function getHeaderColumns()
	{
		return array_merge($this->groupingColumns, array('createdate', 'totalcount', 'passrate'));//, 'passcount'));
	}

	/*
	 * function return a dbColumnList with columns configuration from preferences
	 */ 
	protected function getColumnConfig($columnConfigName=null)
	{
		if( empty($this->columnConfig) )
		{
			$this->columnConfig = Config::getInstance()->getColumnConfig($columnConfigName);
		}
		return $this->columnConfig;
	}
	
	/*
	 * function modifies DbQueryBuilder object according to specified groupingColumns 
	 */	
	private function addGroupingColumns(DbQueryBuilder& $selectQuery)
	{
		foreach($this->groupingColumns as $columnName)
		{
			$dbColConfObj = $this->getColumnConfig()->getDbColumnByRealName($columnName);
			if( $dbColConfObj !== null)
			{
				if($dbColConfObj->getColumnIndex() !== null) {
					
					if($dbColConfObj->getColumnJoinTab() !== null) {
						$selectQuery->addJoinTable(TABLE_PREFIX.$dbColConfObj->getColumnJoinTab(),$dbColConfObj->getColumnIndex()); // inner join TABLE
						$selectQuery->addColumns(array($dbColConfObj->getColumnIndex(),$dbColConfObj->getColumnRealName()),TABLE_PREFIX.$dbColConfObj->getColumnJoinTab()); 
					} else {
						$selectQuery->addColumns(array($dbColConfObj->getColumnIndex(),$dbColConfObj->getColumnRealName())); 
					}
					$selectQuery->addGroupBy($dbColConfObj->getColumnIndex());
					$this->addToVerdictConditions($dbColConfObj->getColumnIndex());
				} else {
					$selectQuery->addColumns(array($dbColConfObj->getColumnRealName()));
					$selectQuery->addGroupBy($dbColConfObj->getColumnRealName());
					$this->addToVerdictConditions($dbColConfObj->getColumnRealName());
				}
			}
		}
	}
	
	/*
	 * function adds column name to be used for building conditions for SQL query counting verdicts 
	 */		
	private function addToVerdictConditions($columnName)
	{
		if(! isset($this->verdictConditions[$columnName]))
		{
			$this->verdictConditions[$columnName] = $columnName.'=?';
		}
	}

	/*
	 * function returns array of values to be used for execution of verdictCountQuery
	 * the values are taken from $row record
	 */		
	private function getValuesForVerdictConditions(array $row)
	{
		$arrayOfValues = array();
		foreach(array_keys($this->verdictConditions) as $key)
		{
			if( isset($row[$key]) )
			{
				array_push($arrayOfValues, $row[$key]);
			}
		}
		return $arrayOfValues;
	}

	/*
	 * function retrieve total number of Test results per Inc, Build, Testsuite and Testline tested within last N days sorted by testdate (latest first)
	 * returns true on success, false otherwise 
	 */
	private function getTotalResInLastNDays($numOfDays)
	{
		$selectQuery = new DbQueryBuilder(TABLE_PREFIX.'testresults');
		$selectQuery->addJoinTable(TABLE_PREFIX.'builds','buildid'); // inner join TABLE builds on buildid field
		$selectQuery->addColumns(array('COUNT(@@id@@) as totalcount', 'createdate')); // add fixed columns to select from TABLE testresults
		
		// add conditions - static first
		if(! empty($this->staticConditions) )
		{
			$selectQuery->addCondition('('.$this->staticConditions.')');
		}
		// add condition TABLE_PREFIX.builds.testdate > DATE_SUB(NOW(), INTERVAL 3 DAY)	
		$whereCondition = TABLE_PREFIX.'builds.testdate > DATE_SUB(NOW(), INTERVAL '.$numOfDays.' DAY)';
		$selectQuery->addCondition($whereCondition);
		
		//add inner join, columns, group by statements based on grouping columns
		$this->addGroupingColumns($selectQuery);
		
		// add ordering
		$selectQuery->addOrderBy('createdate',SQLORDERBY::_DESC);
		$selectQuery->prepareQuery();
		
		//print_r($selectQuery->getQuery());	
		$rs = $selectQuery->executeQuery();
		if(! $rs->EOF ) {
			$this->resultSetTotal = $rs;
			return true;
		} else {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('TOTALBUILDSTAT').' '.Text::_('NOT_FOUND').' '.Text::_('SQL_ERROR');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			$this->addMessage(Text::_('NO_ACTIVITIES').' '.Text::_('IN_LAST').' '.$numOfDays.' '.Text::_('DAYS'));
			return false;
		}
	}
	
	/*
	 * function retrieves total number of Test results per Inc, Build, Testsuite and Testline for last tested N Builds sorted by testdate (latest first) 
	 * returns true on success, false otherwise 
	 */
	private function getTotalResForLastNBuilds($numOfBuilds)
	{
		// first find out N lately tested builds
		$buildQuery = new DbQueryBuilder(TABLE_PREFIX.'builds');
		$buildQuery->addColumns(array('build', 'buildid'));
		$buildQuery->addOrderBy('testdate',SQLORDERBY::_DESC);
		$buildQuery->addLimit(0,$numOfBuilds);
		$buildQuery->prepareQuery();
		$rs = $buildQuery->executeQuery();
		if(! $rs->EOF ) {
			$selectQuery = new DbQueryBuilder(TABLE_PREFIX.'testresults');
			$selectQuery->addJoinTable(TABLE_PREFIX.'builds','buildid'); // inner join TABLE builds on buildid field
			$selectQuery->addColumns(array('COUNT(@@id@@) as totalcount', 'createdate')); // add columns to select from TABLE testresults
						// add conditions - static first
			if(! empty($this->staticConditions) )
			{
				$selectQuery->addCondition($this->staticConditions);
			}
			// add conditions i.e. last NBuilds
			$buildConditions = array();
			foreach($rs->GetRows() AS $row)
			{
				array_push($buildConditions, TABLE_PREFIX.'builds.buildid='.$row['buildid']);
			}
			if(! empty($buildConditions))
			{
				$selectQuery->addCondition('('.implode(' OR ', $buildConditions).')');	
			}
			
			//add inner join, columns, group by statements based on grouping columns
			$this->addGroupingColumns($selectQuery);
			
			// add ordering
			$selectQuery->addOrderBy('createdate',SQLORDERBY::_DESC);
			$selectQuery->prepareQuery();
			
			//print_r($selectQuery->getQuery());	
			$rs = $selectQuery->executeQuery();
			if(! $rs->EOF ) {
				// convert row to DbColumnList
				$this->resultSetTotal = $rs;
				return true;
			} else {
				$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_ACTIVITIES').' '.Text::_('FOR_LAST').' '.$numOfBuilds.' '.Text::_('TPL_BUILDS');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				$this->addMessage(Text::_('NO_ACTIVITIES').' '.Text::_('FOR_LAST').' '.$numOfBuilds.' '.Text::_('TPL_BUILDS'));
				return false;
			}
		} else {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_ACTIVITIES').' '.Text::_('FOUND');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			$this->addMessage(Text::_('NO_ACTIVITIES').' '.Text::_('FOUND'));
			return false;
		}		
	}
	
	private function getVerdictsCountQuery()
	{		
		if(empty($this->verdictCountQuery))
		{
			$query = new DbQueryBuilder(TABLE_PREFIX.'testresults');
			$query->addColumns(array('tcverdict','COUNT(@@id@@) as c')); // add columns to select from TABLE testresults
			// add conditions - static first
			if(! empty($this->staticConditions) )
			{
				$query->addCondition($this->staticConditions);
			}
			// add condition
			if(!empty($this->verdictConditions))
			{
				$query->addCondition(implode(' AND ', array_values($this->verdictConditions)));
			}
			// add group by clause
			$query->addGroupBy('tcverdict');
			
			$query->prepareQuery();
			//print_r($query->getQuery());
			$this->verdictCountQuery = $query;
		}
		return $this->verdictCountQuery;
	}

	/*
	 * function check if $name is on the searchable list 
	 */ 
	private function checkAndAddTableTag($name)
	{
		if( preg_match('/\w+\.\w+/', $name) ) {
			$name = TABLE_PREFIX.$name;
		} else {
			$name = '@@'.$name.'@@';
		}
		return $name;
	}		
}
?>
