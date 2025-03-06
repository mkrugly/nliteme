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

class TestResultsRateModel extends Model
{
	private $resultSetTotal = null;
	private $verdictCountQuery = null;
	private $featureCoverageExecutedTcQuery = null;
	private $featureCoveragePassedTcQuery = null;
	private $featureCoverageQuery = null;
	private $outputArray = array();
	private $columnConfig = null; // DbColumnList containing column config information retrieved from preferences
	private $groupingColumns = array(); // realcolumn name used for grouping records
	private $verdictConditions = array(); // list of conditions for count verdict selection
	private $staticConditionsLinkArgs = array(); // list of static conditions link args originated from URL
	private $staticConditions = null; // condition string specifying additional condition for getTotalResPerBuildPerTsPerTlInLastNDays
	private $buildIdCondition = null; // condition string specifying buildids for which the stats shall be retrieved
	private $orderByList = array(); // an array of columnName=>SQLORDERBY::_value pair (optional)
	protected $countedColumn = null; //
	/*
	 * constructor
	 */ 
	public function __construct(array $groupingColumns=array(), $countedColumn = null)
	{
		$this->getColumnConfig('testresults');
		$this->groupingColumns = $groupingColumns; //to be taken from preferences
		if(empty($countedColumn)) {
			$this->countedColumn = '@@id@@';
		} else {
			$this->countedColumn = $countedColumn;
		}
	}
	
	/*
	 * function to perform show details action
	 */ 
	public function show()
	{
		$result = false;
		// for performance reason get the total count only in case the $buildConditions are set
		if(! empty($this->buildIdCondition))
		{	
			$result = $this->getTotalResults();
		}	

		// based on the Total results get the verdicts results counts
		// will be done per total result row, otherwise the query will be too complex (with lots of tempfiles and sorting) and hence inefficient
		if($result === true && !empty($this->resultSetTotal)) {
			$fid_key = array_search('fid', array_keys($this->verdictConditions));
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
					//$row['link-args'] = array_combine(array_keys($this->verdictConditions),$valuesForVerdictConditions);
					$row['link-args'] = array_replace($this->staticConditionsLinkArgs, array_combine(array_keys($this->verdictConditions),$valuesForVerdictConditions));
					//$row['link-args'] = implode('&',array_map(function($k, $v){return "$k=$v";}, array_keys($this->verdictConditions),$valuesForVerdictConditions));
				}
				// show feature coverage
				if($fid_key !== False)
				{
					$coverage_arr = array();
					$rs = $this->getFeatureCoverageQuery()->executeQuery(array($valuesForVerdictConditions[$fid_key]));
					if(! $rs->EOF ) 
					{
						$r = $rs->FetchRow();
						$coverage_arr['definecount'] = min($r['coverage'], 100);
						$row['hlink'] = $r['hlink'];
					}
					$rs = $this->getFeatureCoverageExecutedTcQuery()->executeQuery($valuesForVerdictConditions);
					if(! $rs->EOF ) 
					{
						$coverage_arr['executecount'] = min($rs->FetchRow()['coverage'], 100);
					}
					$rs = $this->getFeatureCoveragePassedTcQuery()->executeQuery($valuesForVerdictConditions);
					if(! $rs->EOF ) 
					{
						$coverage_arr['passcount'] = min($rs->FetchRow()['coverage'], 100);
					}
					$row['coverage'] = $coverage_arr;
				}
				array_push($this->outputArray,$row);
			}
			if(empty($this->outputArray)) { $result = false; }
		}
		return $result;
	}

	/*
	 * sets the list of buildIs for which the stats shall be found
	 * prepares a conditions string to be added to the selectQuery
	 * Note. the child class has to set this list to limit the possible result set of the query
	 * If not sepcified the query will started for all builds in the database which would heavily affect performance
	 */ 		
	public function setBuildIdCondition(array $buildIdList)
	{
		if(! empty($buildIdList) )
		{
			$arr = array();
			$key = '@@buildid@@';
			foreach ($buildIdList AS $value) 
			{
				if (! Utils::emptystr($value) ) {
					array_push($arr, $key."='".$value."'");
				}
			}
			$this->buildIdCondition = '('.implode(' OR ', $arr).')';
		}
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
				$column = $this->getColumnConfig()->getDbColumnByRealName($key);
				if( isset($column) ) 
				{
					// if join tab is present add it to the query name
					$joinTab = $column->getColumnJoinTab();
					isset($joinTab) ? $key = $joinTab.'.'.$key : null;
				}
				$key = $this->checkAndAddTableTag($key);
				if( is_array($value) ) 
				{
					$tmp = array();
					foreach ($value as $k => $v)
					{
						if ( isset($v) && $v != '' )
						{
							list($v, $op) = $this->getValOpArr($v);
							$tmp[$k] = $key.$op.$v;
						}
					}
					$value = implode(' OR ', $tmp);
					if (! empty($value) )
					{
						array_push($arr, '('.$value.')');
					}
				} else if (! Utils::emptystr($value) ) {
					list($value, $op) = $this->getValOpArr($value);
					array_push($arr, $key.$op."'".$value."'");
				}
			}
			$this->staticConditionsLinkArgs = $conditionList;
			$this->staticConditions = implode(' AND ', $arr);
		}
	}
	
	/*
	 * sets order by list for SQL query
	 */ 		
	public function setOrderbyList(array $columnOrderPair)
	{
		if(! empty($columnOrderPair) )
		{
			$this->orderByList = $columnOrderPair;
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
		$add_columns_to_show = array();
		if(in_array('fname', $this->groupingColumns))
		{
			array_push($add_columns_to_show, 'coverage');
		}
		array_push($add_columns_to_show, 'createdate', 'totalcount', 'passrate');
		return array_merge($this->groupingColumns, $add_columns_to_show);//, 'passcount'));
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
	 * function modifies DbQueryBuilder object by linking an intermediate table
	 * it accespts the $joinTablesDetailsArray reference that can be used to store the joined table, on column and optionally type of join and base table
	 */
	private function addJoinIntermediate(DbQueryBuilder& $selectQuery, $joinViaColumnConfigName, array& $joinTablesDetailsArray = array())
	{
        if($joinViaColumnConfigName !== null) {
			$dbColConfObj = $this->getColumnConfig()->getDbColumnByRealName($joinViaColumnConfigName);
			if( $dbColConfObj !== null)
			{
				if($dbColConfObj->getColumnIndex() !== null) {
						
					if($dbColConfObj->getColumnJoinTab() !== null) {
						$selectQuery->addJoinTable(TABLE_PREFIX.$dbColConfObj->getColumnJoinTab(),$dbColConfObj->getColumnIndex()); // inner join TABLE
						$selectQuery->addColumns(array($dbColConfObj->getColumnIndex(),$dbColConfObj->getColumnRealName()),TABLE_PREFIX.$dbColConfObj->getColumnJoinTab()); 
                        $joinTablesDetailsArray[$dbColConfObj->getColumnIndex()] = array(TABLE_PREFIX.$dbColConfObj->getColumnJoinTab(),$dbColConfObj->getColumnIndex(), SQLJOIN::INNER, null);
                        return TABLE_PREFIX.$dbColConfObj->getColumnJoinTab();
					}
				}
			}
        }
        return null;
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
					
					$joinTab = $dbColConfObj->getColumnJoinTab();
					// define an array to store join tables details if needed
					$joinTablesArray = array();
					if($joinTab !== null) {
						// add intermediate joining table if exists, update $joinTablesArray with relevant JOIN details
						$joinViaTable = $this->addJoinIntermediate($selectQuery, $dbColConfObj->getColumnJoinVia(), $joinTablesArray);
						// add join table on
						$joinTab = TABLE_PREFIX.$joinTab;
						$joinType = ($dbColConfObj->getColumnJoinType() !== null) ? $dbColConfObj->getColumnJoinType() : SQLJOIN::INNER;
						$selectQuery->addJoinTable($joinTab,$dbColConfObj->getColumnIndex(), $joinType, $joinViaTable); // inner join TABLE
						// if needs to join via another table make sure the vedict condition query adds suitable join table queries
						if(isset($joinViaTable))
						{
							$joinTablesArray[$dbColConfObj->getColumnIndex()] = array($joinTab,$dbColConfObj->getColumnIndex(), $joinType, $joinViaTable); 
						}
					}
					$selectQuery->addColumns(array($dbColConfObj->getColumnIndex(),$dbColConfObj->getColumnRealName()), $joinTab); 
					$selectQuery->addGroupBy($dbColConfObj->getColumnIndex(), $joinTab);
					# add column amd relevant join tables details to verdict condition
					$this->addToVerdictConditions($dbColConfObj->getColumnIndex(), $joinTablesArray);
				} else {
					$selectQuery->addColumns(array($dbColConfObj->getColumnRealName()));
					$selectQuery->addGroupBy($dbColConfObj->getColumnRealName());
					$this->addToVerdictConditions($dbColConfObj->getColumnRealName());
				}
			}
		}
	}

	/*
	 * function modifies DbQueryBuilder object according to specified groupingColumns 
	 */	
	private function addOrderBy(DbQueryBuilder& $selectQuery)
	{
		foreach($this->orderByList as $columnName => $order)
		{
			$order = $order === SQLORDERBY::_ASC ? SQLORDERBY::_ASC : SQLORDERBY::_DESC;
			$dbColConfObj = $this->getColumnConfig()->getDbColumnByRealName($columnName);
			if( $dbColConfObj !== null)
			{
				$tableName = null;
				if($dbColConfObj->getColumnIndex() !== null) {
					
					if($dbColConfObj->getColumnJoinTab() !== null) {
						// add intermediate joining table if exists
						$joinViaTable = $this->addJoinIntermediate($selectQuery, $dbColConfObj->getColumnJoinVia());
						// add join table on
						$joinType = ($dbColConfObj->getColumnJoinType() !== null) ? $dbColConfObj->getColumnJoinType() : SQLJOIN::INNER;
						$selectQuery->addJoinTable(TABLE_PREFIX.$dbColConfObj->getColumnJoinTab(),$dbColConfObj->getColumnIndex(), $joinType, $joinViaTable); // inner join TABLE
						$tableName = TABLE_PREFIX.$dbColConfObj->getColumnJoinTab();
					} 
				}
				$selectQuery->addOrderBy($columnName,$order,$tableName);
			}
		}
	}
	
	/*
	 * function adds column name to be used for building conditions for SQL query counting verdicts 
	 * and optionally the needed joined table details
	 * $joinTablesArray can be an empty array (if no ...join table on ... clause is needed) or
	 * an associative array with keys being ON column names and values being arrays of parameters to be passed to the DbQueryBuilder.addJoinTable function
	 */		
	private function addToVerdictConditions($columnName, $joinTablesArray=array())
	{
        if(! isset($this->verdictConditions[$columnName]))
		{
			$this->verdictConditions[$columnName] = $joinTablesArray;
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
	 * function gets total number of results grouped by grouping columns with conditions given
	 */
	protected function getTotalResults()
	{
		$selectQuery = $this->getCountQuery('totalcount');
		
		$selectQuery->prepareQuery();
		
		//print_r($selectQuery->getQuery());	
		$rs = $selectQuery->executeQuery();
		if($rs && !$rs->EOF ) {
			// convert row to DbColumnList
			$this->resultSetTotal = $rs;
			return true;
		} else {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('NOT_FOUND');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			$this->addMessage(Text::_('NOT_FOUND'));
			return false;
		}	
	}

	/*
	 * function retrieves total number of Test results per Inc, Build, Testsuite and Testline for last tested N Builds sorted by testdate (latest first) 
	 * returns true on success, false otherwise 
	 */
	private function getCountQuery($countAsName)
	{
		$selectQuery = new DbQueryBuilder(TABLE_PREFIX.'testresults');
		$selectQuery->addJoinTable(TABLE_PREFIX.'builds','buildid'); // inner join TABLE builds on buildid field
		$selectQuery->addColumns(array('COUNT('.$this->countedColumn.') as '.$countAsName, 'createdate')); // add columns to select from TABLE testresults
		// add conditions - static first
		if(! empty($this->staticConditions) )
		{
			$selectQuery->addCondition($this->staticConditions);
		}
		// add buildIds condition
		if(! empty($this->buildIdCondition) )
		{
			$selectQuery->addCondition($this->buildIdCondition);
		}		
		
		//add inner join, columns, group by statements based on grouping columns
		$this->addGroupingColumns($selectQuery);
		
		// add ordering
		$this->addOrderBy($selectQuery);		
		$selectQuery->addOrderBy('createdate',SQLORDERBY::_DESC);
		//$selectQuery->prepareQuery();
		//print_r($selectQuery->getQuery());
		
		return $selectQuery;
	}
	
	protected function getVerdictsCountQuery()
	{		
		if(empty($this->verdictCountQuery))
		{
			$query = new DbQueryBuilder(TABLE_PREFIX.'testresults');
			$query->addColumns(array('tcverdict','COUNT('.$this->countedColumn.') as c')); // add columns to select from TABLE testresults
			// add static conditions
			if(! empty($this->staticConditions) )
			{
				$query->addCondition($this->staticConditions);
			}
			// add condition
			foreach ($this->verdictConditions AS $key => $joinTablesArray) 
			{
				$column = $key;
				# foreach table to be joined call the relevant function
				# if joinON key is the same as key column add join table name prefix
				foreach($joinTablesArray AS $joinOnKey => $joinTableDetails)
				{
					$query->addJoinTable(...$joinTableDetails);
					if($key == $joinOnKey)
					{
						$column = $joinTableDetails[0] . '.' . $key;
					}
				}
				$query->addCondition($column . '=?');
			}
			// add group by clause
			$query->addGroupBy('tcverdict');
			
			$query->prepareQuery();
			//print_r($query->getQuery());
			$this->verdictCountQuery = $query;
		}
		return $this->verdictCountQuery;
	}
	
	protected function getFeatureCoveragePassedTcQuery()
	{		
		if(empty($this->featureCoverageExecutedTcQuery) or empty($this->featureCoveragePassedTcQuery))
		{
			// prepare subquery to select the relevant tc coverages
			$query = new DbQueryBuilder(TABLE_PREFIX.'testresults');
			$query->addJoinTable(TABLE_PREFIX.'testcases','tcid'); // inner join TABLE testcases on tcid field
			$query->addJoinTable(TABLE_PREFIX.'features','fid',TABLE_PREFIX.'testcases'); // inner join TABLE features on fid field via testcases
			$query->addColumns(array('coverage', 'fid', 'tcname'),TABLE_PREFIX.'testcases'); // add columns to select from TABLE testcases		
			$query->addColumns(array('fname'),TABLE_PREFIX.'features'); // add columns to select from TABLE testlines
			// add condition
			foreach ($this->verdictConditions AS $key => $joinTablesArray) 
			{
				$column = $key;
				# foreach table to be joined call the relevant function
				# if joinON key is the same as key column add join table name prefix
				foreach($joinTablesArray AS $joinOnKey => $joinTableDetails)
				{
					$query->addJoinTable(...$joinTableDetails);
					if($key == $joinOnKey)
					{
						$column = $joinTableDetails[0] . '.' . $key;
					}
 				}
				$query->addCondition($column . '=?');
			}
			// add group by clause
			$query->addGroupBy('tcid');
			
			// prepare main query for feature coverage based on executed tests
			$calcTotalFeatureCoverageQuery = new DbQueryBuilder('(' . $query->getQuery() . ')', null, SQLCOMMAND::_SELECT, 't');
			$calcTotalFeatureCoverageQuery->addColumns(array('IFNULL(SUM(@@coverage@@), 0) as coverage'));
			$calcTotalFeatureCoverageQuery->prepareQuery();
			$this->featureCoverageExecutedTcQuery = $calcTotalFeatureCoverageQuery;
			
			// prepare main query for feature coverage based on executed and passed tests
			$query->addCondition('tcverdict=0');
			$calcPassedFeatureCoverageQuery = new DbQueryBuilder('(' . $query->getQuery() . ')', null, SQLCOMMAND::_SELECT, 't');
			$calcPassedFeatureCoverageQuery->addColumns(array('IFNULL(SUM(@@coverage@@), 0) as coverage'));
			$calcPassedFeatureCoverageQuery->prepareQuery();
			$this->featureCoveragePassedTcQuery = $calcPassedFeatureCoverageQuery;
		}
		return $this->featureCoveragePassedTcQuery;
	}

	protected function getFeatureCoverageExecutedTcQuery()
	{	
		$this->getFeatureCoveragePassedTcQuery();
		return $this->featureCoverageExecutedTcQuery;
	}
	
	protected function getFeatureCoverageQuery()
	{
		if(empty($this->featureCoverageQuery))
		{
			$this->featureCoverageQuery = (new FeatureModel())->getCoverageQuery();
		}
		return $this->featureCoverageQuery;
	}

	/*
	 * function check if $name is on the searchable list 
	 */ 
	protected function checkAndAddTableTag($name)
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
