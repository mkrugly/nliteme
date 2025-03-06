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

/*****
 * class BuildCompareModel prepares a data model for a build compare main content table
 *****/
class BuildCompareModel extends BuildCompareSearcherModel
{
	protected $sortingColumnList = null; // DbColumnList containing column config information for sortable columns
	protected $compareByColumn = null;   // Name of the column by which the compare should be done e.g. tcverdict
	protected $compareByCfgKey = null;   // Name of the key to the compareByConfiguration settings, compareBy category
	protected $conditionList = array();    // List of parameters and values to limit the selection
	protected $buildResultSet = null;    // contains the SQL resultset for build selection query
	protected $numOfBuildToCompare = 10;	// limits the number of builds to show to 10
	
	public function __construct($compareByCfgKey = null)
	{
		parent::__construct();
		$this->setCompareByColumnName($compareByCfgKey);
		$this->getSortingColumnList();	
	}
	
	/*
	 * function to retrieves the suitable search fields
	 */ 
	public function showList()
	{
		$this->fillSortingColumnsData();
		$this->fillBuildResultSet();

		// ToDo. Do not return, just prepare the data, let the view to parse it and decide the format
		return $this->sortingColumnList;
	}

	/*
	 * function returns the SQL ResultSet for the builds
	 */ 	
	public function getResultSet()
	{
		return $this->buildResultSet;
	}	

	/*
	 * function sets name of the column used as comparision criteria
	 */	
	protected function setCompareByColumnName($columnName)
	{
		if(!empty($columnName))
		{
			$this->compareByCfgKey = $columnName;
		} else {
			$this->compareByCfgKey = 'tcverdict';
		}
		$this->compareByColumn = $this->getCompareByConfiguration()->getCompareByConfigList()->getCompareByConfig($this->compareByCfgKey)->getCompareByColumn();
	}
	
	/*
	 * function returns a name of the column used as comparision criteria
	 */
	public function getCompareByColumnName()
	{
		return $this->compareByColumn;
	}
	
	/*
	 * function returns a name of the column used as comparision criteria
	 */
	public function getCompareByCfgKey()
	{
		return $this->compareByCfgKey;
	}
	
	/*
	 * function sets input condition's based on $_GET array
	 */ 	
	public function setConditions(array $conditionList)
	{
		$this->conditionList = $conditionList;
	}

	/*
	 * function return a dbColumnList with searchable columns
	 */ 
	protected function fillBuildResultSet()
	{
		// make sure the build column is searchable within the test results
		$buildDbColumn = $this->getSearchableColumnList()->getDbColumnByRealName('build');
		if(! empty($buildDbColumn))
		{
			$get_args = $this->parseGetArgsForColumn($buildDbColumn);
			// select increment Id based on the given args
			$incIds = $this->parseGetArgsForIncIds($get_args);

			// prepare build selection query
			$selectQuery = new DbQueryBuilder(TABLE_PREFIX.'builds');
		    $selectQuery->addColumns(array('*'));
			$selectQuery->addJoinTable(TABLE_PREFIX.'build_increments','incid'); // inner join TABLE build_increments on incid field
			$selectQuery->addColumns(array('increment'),TABLE_PREFIX.'build_increments');
			$selectQuery->addOrderBy('createdate',SQLORDERBY::_DESC);	
			$selectQuery->addOrderBy('increment',SQLORDERBY::_DESC,TABLE_PREFIX.'build_increments');
			// filter build that were not tested
			$selectQuery->addCondition("@@testdate@@<>'0000-00-00'");		
			// prepare static condition for build selection query
			$cond_arr = $this->buildCondition($get_args);
			foreach($cond_arr as $value)
			{
				$selectQuery->addCondition($value);
			}
		    $numOfBuildsToShow = $this->numOfBuildToCompare;
			// apply different handling of the query depedning on the conditions
			// i.e. if no incId is present just add limit; otherwise:
			// prepare union of select queries adding a condition for each incid to each query within union
			// this way for each incId an indepent search will done and one common result set for all incids will be returned

			if(empty($incIds))
			{
				$selectQuery->addLimit(0,$numOfBuildsToShow);
			} else {
			    // create cloned query to be used for query union
				$selectQueryTemplate = clone $selectQuery;
				// reduce the number of build per increment
				if(count($incIds) > 1)
				{
					$numOfBuildsToShow = max(3,round($numOfBuildsToShow/count($incIds)));	
				}
				// iterate through incIds and add the union queries toselectQuery
				foreach($incIds as $inx => $incid)
				{
					$cond = '@@incid@@='.$incid;
					// if 1st incid use the current query; otherwise prepare union query
					if($inx == 0) {
						$selectQuery->addCondition($cond);
						$selectQuery->addLimit(0,$numOfBuildsToShow);
					} else {
						// here a union query has to be prepared
						// 1. clone query from $selectQueryTemplate
						$unionSelect = clone $selectQueryTemplate;
						$unionSelect->addCondition($cond);
						$unionSelect->addLimit(0,$numOfBuildsToShow);
						// 2. add the union query to the main query
						$selectQuery->addUnionQuery($unionSelect->getQuery());
					}	
				}
			}
			// ensure the final reseultset is sorted by the build increment
			$selectQuerySorted = new DbQueryBuilder('(' . $selectQuery->getQuery() . ')', null, SQLCOMMAND::_SELECT, 't');
			$selectQuerySorted->addColumns(array('*'));
			$selectQuerySorted->addOrderBy('increment',SQLORDERBY::_DESC);
			$selectQuerySorted->prepareQuery();
			$this->buildResultSet = $selectQuerySorted->executeQuery();
			
			// old approach without final sorting by increment
			//print_r($selectQuery->getQuery().'</BR>');
			//$selectQuery->prepareQuery();
			//$this->buildResultSet = $selectQuery->executeQuery();		
		}
	}

	/*
	 * function checks $GET for incid or increment and prepares a list of incIds for build selection query
	 * return an array of incids
	 */
	private function parseGetArgsForIncIds(array& $get_args)
	{	
		$incIds = array();
		// if increment is given in form of string, get the list of increment ids first
		if (isset($get_args['incid']) && !empty($get_args['incid']) && is_array($get_args['incid']) ) {
			$incIds = $get_args['incid'];
			// remove this element from the $get_args, since it is not needed anymore
			unset($get_args['incid']);			
		} if(isset($get_args['increment']) && !empty($get_args['increment'])) {
			$incDbColumn = $this->getSearchableColumnList()->getDbColumnByRealName('increment');
			if(! empty($incDbColumn))
			{
				$this->setColumnValues($incDbColumn);
				array_push($incIds, array_keys($incDbColumn->getColumnPredefinedValues()));
			}
			// remove this element from the $get_args, since it is not needed anymore
			unset($get_args['increment']);			
		} 
		return $incIds;
	}	
	
	/*
	 * function return a dbColumnList with searchable columns
	 */ 
	public function getSortingColumnList()
	{
		if( empty($this->sortingColumnList) )
		{
			$sortingColumnList = new DbColumnList();
			// get sorting column for a requested compareByColumn from the CompareByConfiguration
			$compareConfig = $this->getCompareByConfiguration()->getCompareByConfigList()->getCompareByConfig($this->getCompareByCfgKey());
			if(! empty($compareConfig))
			{
				$sortingColumnNames = $compareConfig->getSortingColumns();
				foreach ($sortingColumnNames AS $columnName)
				{
					$sortingColumn = $this->getSearchableColumnList()->getDbColumnByRealName($columnName);
					if(! empty($sortingColumn))
					{
						$sortingColumnList->addColumn($sortingColumn);
					}
				}
			}				
			$this->sortingColumnList = $sortingColumnList;				
		}
		return $this->sortingColumnList;
	}
	
	/*
	 * function fills the value arrays of selected sorting columns with data from DB
	 */ 	
	protected function fillSortingColumnsData()
	{
		$iterator = $this->getSortingColumnList()->getIterator();
		while( $iterator->valid() )
		{
			$current = $iterator->current();
			$this->setColumnValues($current);
			$iterator->next();
		}
	}
	
	/*
	 * function fills the relevant values arrays with data
	 * using the $this->conditionList as filter
	 */
	protected function setColumnValues(DbColumnConfig& $dbColumnConfig)
	{
		$indexName = $dbColumnConfig->getColumnIndex();
		if( isset($indexName) && ! $dbColumnConfig->is_predefined() )
		{
			$tabName = $dbColumnConfig->getColumnJoinTab();
			if( empty($tabName) )
			{
				$tabName = $this->columnConfigName;
			}
			$selectQuery = new DbQueryBuilder(TABLE_PREFIX.$tabName,array($indexName,$dbColumnConfig->getColumnRealName()),SQLCOMMAND::_SELECT_DISTINCT);
			// modify query based on $this->conditionList
			$this->modifyQueryForDbColumn($selectQuery, $dbColumnConfig);
			// sort by column realname (e.g. for testcases by testcase name)
			$selectQuery->addOrderBy($dbColumnConfig->getColumnRealName(),SQLORDERBY::_ASC);
			//print_r($selectQuery->getQuery().'</BR>');
			$result = $selectQuery->executeQuery();
			if($result)
			{						
				$arr = array();
				foreach($result->GetRows() AS $row)
				{
					$arr[$row[$indexName]] = $row[$dbColumnConfig->getColumnRealName()];
				}
				$dbColumnConfig->setColumnPredefinedValues($arr);
			}
		} else if ($dbColumnConfig->is_predefined()) {
			$this->filterColumnPredefinedValues($dbColumnConfig);
		}
	}
	
	/*
	 * function modifies a DB query for a given DbColumnConfig based on $_GET parameter if specified
	 */
	private function modifyQueryForDbColumn(DbQueryBuilder& $selectQuery, DbColumnConfig& $dbColumnConfig)
	{	
		$columnName = $dbColumnConfig->getColumnRealName();
		$get_args = $this->parseGetArgsForColumn($dbColumnConfig);
		// if array is not empty modify the query according to the included conditions
		if(!empty($get_args))
		{
			$modified_args = array();
			foreach($get_args as $key => $value)
			{
				// modify query based on the given arg, give some arg's names the correct table prefix
				// note that special handling is needed for testcases (additional join on tcid<=>tsid mapping) and build (handling of increments)
				if($columnName == 'tcname') {
					if($key == 'tsid') {
						$selectQuery->addJoinTable(TABLE_PREFIX.'suites_cases_map','tcid'); // inner join TABLE suites_cases_map on tcid field
						$modified_args['suites_cases_map.'.$key] = $value;
					} else if($key == 'tsname') {
						$selectQuery->addJoinTable(TABLE_PREFIX.'testsuites','tsid'); // inner join TABLE testsuites on tsid field
						$modified_args['testsuites.'.$key] = $value;	
					} else if($key == 'fid') {
						$modified_args[$key] = $value;
					} else if($key == 'fname') {
						$selectQuery->addJoinTable(TABLE_PREFIX.'features','fid'); // inner join TABLE features on fid field
						$modified_args['features.'.$key] = $value;	
					} else {
						$modified_args[$key] = $value;
					}
				} else if($columnName == 'build' && $key == 'increment') {
					$selectQuery->addJoinTable(TABLE_PREFIX.'build_increments','incid'); // inner join TABLE build_increments on incid field					
					$modified_args['build_increments.'.$key] = $value;		
				} else {
					$modified_args[$key] = $value;	
				}
			}
			$cond_arr = $this->buildCondition($modified_args);
			foreach($cond_arr as $value)
			{
				$selectQuery->addCondition($value);
			}
		}
	}
	
	/*
	 * function parses $_GET for parameters specific for this $dbColumnConfig
	 */
	private function parseGetArgsForColumn(DbColumnConfig& $dbColumnConfig)
	{	
		// retrieve index and name as they can be used as selection criteria
		$columnName = $dbColumnConfig->getColumnRealName();
		$columnIndex = $dbColumnConfig->getColumnIndex();
		$checkArgs = array();
		array_push($checkArgs, $columnName);
		array_push($checkArgs, $columnIndex);
		// additionaly in case of testcases or build consider additional criteria
		if($columnName == 'tcname') {
			array_push($checkArgs, 'tsid');
			array_push($checkArgs, 'tsname');
			array_push($checkArgs, 'fid');
			array_push($checkArgs, 'fname');
		} else if ($columnName == 'build') {
			array_push($checkArgs, 'incid');
			array_push($checkArgs, 'increment');
			array_push($checkArgs, 'createdate_FROM');
			array_push($checkArgs, 'createdate_TO');
		}
		$getArr = array();
		// check if the selected attributes are present on the $_GET list
		foreach($checkArgs as $key)
		{
			if(isset($this->conditionList[$key]) && !empty($this->conditionList[$key]))
			{
				$getArr[$key] = $this->conditionList[$key];
			}
		}
		return $getArr;
	}	
	
	/*
	 * function filters the DbColumnConfig predefined values based on $_GET parameter if specified
	 * (shall be used for the DbColumnConfig with is_predefined() === true)
	 */
	private function filterColumnPredefinedValues(DbColumnConfig& $dbColumnConfig)
	{
		$columnName = $dbColumnConfig->getColumnRealName();
		if(isset($this->conditionList[$columnName]) && ! empty($this->conditionList[$columnName]))
		{
			$get_arg = $this->conditionList[$columnName];
			$predefinedValues = $dbColumnConfig->getColumnPredefinedValues();
			$newPredefinedValues = array();
			$filterValues = is_array($get_arg) ? $get_arg : array($get_arg);
			foreach($filterValues as $key)
			{
				if(isset($predefinedValues[$key]))
				{
					$newPredefinedValues[$key] = $predefinedValues[$key];
				}
			}
			$dbColumnConfig->setColumnPredefinedValues($newPredefinedValues);
		}
	}
	
	/*
	 * function prepares a condition array
	 */
	protected function buildCondition(array $conditionList)
	{
		$arr = array();
		foreach ($conditionList AS $key => $value) 
		{
			if( is_array($value) ) 
			{
				$tmp = array();
				$key = $this->checkAndAddTableTag($key);
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
				// handle datetime ranges
				if(preg_match('/(.*?)_FROM/', $key, $matches)) {
					$key = $matches[1];
					$key = $this->checkAndAddTableTag($key);
					$value = $key.' >= '."'".$value."'";
				} else if (preg_match('/(.*?)_TO/', $key, $matches)) {
					$key = $matches[1];
					$key = $this->checkAndAddTableTag($key);
					$value = $key.' <= '."'".$value."'";
				} else if (preg_match('/.*?id/', $key) 
						|| preg_match('/extracolumn_\d+/', $key)
						|| 'tcverdict' == $key) {
					$key = $this->checkAndAddTableTag($key);
					$value = $key."=".$value;				
				} else {
					$key = $this->checkAndAddTableTag($key);
					$valarr = explode(",", $value);
					$tmp = array();
					foreach ($valarr as $v)
					{
						if ( isset($v) && $v != '' )
						{
							array_push($tmp, $key." LIKE CONCAT('".trim($v)."','%')");
						}
					}
					$value = implode(' OR ', $tmp);
				}
				array_push($arr, $value);
			}
		}
		return $arr;
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
	
/*****
 * class BuildCompareSearcherModel prepares a data model for a build compare search form
 *****/
class BuildCompareSearcherModel extends TestResultsSearcherModel
{
	protected $compareByConfiguration = null; // structure containing build compare configuration information retrieved from preferences
	
	public function __construct()
	{
		parent::__construct();
		$this->getCompareByConfiguration();
	}

	/*
	 * function to retrieves the suitable search fields
	 */ 
	public function showList()
	{
		parent::showList();
		$this->fillCompareColumnsData();
		$this->stripSearchableList();
		return $this->searchableColumnConfig;
	}
	
	/*
	 * function returns a structure containing build compare configuration information retrieved from preferences
	 */ 
	protected function getCompareByConfiguration()
	{
		if( empty($this->compareByConfiguration) )
		{
			$this->compareByConfiguration = new BuildCompareConfig(Config::getInstance()->getPreference('buildcompare'));
		}
		return $this->compareByConfiguration;
	}
	
	/*
	 * function fills the builds related params for the search form
	 */ 	
	protected function fillCompareColumnsData()
	{
		$compareColumn = $this->getSearchableColumnList()->getDbColumnByRealName('compareby');
		if(empty($compareColumn))
		{
			$compareColumn = new DbColumnConfig(array('realname'=>'compareby'));
			//$compareColumn->setColumnRealName('compareby');
			$compareColumn->setSearcherFieldType(FIELDTYPES::_SS);
			$compareColumn->setParam('searchable','yes');
		}
		$comparebyColumnNames = $this->getCompareByConfiguration()->getCompareByConfigList()->getCompareByColumnNames();
		$compareColumn->setColumnPredefinedValues($comparebyColumnNames);
		$this->searchableColumnConfig->addColumn($compareColumn);
	}
	
	/*
	 * function strips unnecessary columns from the list
	 */ 	
	protected function stripSearchableList()
	{
		$this->getSearchableColumnList()->delColumn('tcverdict');
		$this->getSearchableColumnList()->delColumn('extracolumn_2');
	}	
}

/*****
 * class BuildCompareTBodyModel prepares a data model for a build compare main table body
 *****/
class BuildCompareTBodyModel extends TestResultsModel
{
	protected $columnConfig = null; // DbColumnList containing column config information retrieved from preferences
	protected $searchableColumnConfig = null; // subset of $columnConfig with dbColumn which are searchable
	protected $sortingColumns = null; // subset of $searchableColumnConfig with dbColumn which constitute a compare build row selection criteria
	protected $compareByConfiguration = null; // structure containing build compare configuration information retrieved from preferences
	protected $compareByColumn = null; // name of the column for which the compare shall be done
	protected $compareByCfgKey = null;   // Name of the key to the compareByConfiguration settings, compareBy category
	protected $sortingColumnInxs = array(); // list of column indices for the corresponding $sortingColumns
	/*
	 * constructor
	 */ 
	public function __construct($compareByCfgKey)
	{
		parent::__construct();
		$this->getColumnConfig('testresults');
		$this->setCompareByColumnName($compareByCfgKey); 	//'tcverdict';
	}
	
	/*
	 * function sets name of the column used as comparision criteria
	 */	
	protected function setCompareByColumnName($columnName)
	{
		if(!empty($columnName))
		{
			$this->compareByCfgKey = $columnName;
		} 
		$compareByConfig = $this->getCompareByConfiguration()->getCompareByConfigList()->getCompareByConfig($this->compareByCfgKey);
		if(!empty($compareByConfig))
		{
			$this->compareByColumn = $compareByConfig->getCompareByColumn();
		}
	}
	
	/*
	 * function returns a name of the column used as comparision criteria
	 */
	public function getCompareByColumnName()
	{
		return $this->compareByColumn;
	}
	
	/*
	 * function returns a name of the column used as comparision criteria
	 */
	public function getCompareByCfgKey()
	{
		return $this->compareByCfgKey;
	}
	
	/*
	 * function returns a list of index column names matching with sortingColumn names e.g. 'tcid' for 'tcname'
	 * Note. This function shall be called after $this->getSelectQuery to return meaningfull results
	 */	
	public function getSortingColumnInxs()
	{
		return $this->sortingColumnInxs;
	}
	
	/*
	 * parent function overridden due to pagination limits as so on
	 */  
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addColumns(array('id','createdate','incid','buildid','tsid','tcid','tlid','tcverdict'
							,'extracolumn_0','extracolumn_1','extracolumn_2','extracolumn_3','duration','filepath')); // add columns to select from TABLE testresults
			
			// add ordering and relevant columns to the selection
			$this->addSortingColumns($selectQuery);
			
			// add conditions
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
			
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}
	
	/*
	 * parent function overriden, since no need to count rows and use pagination
	 */ 
	public function showList()
	{
		// get resultset
		//print_r($this->getSelectQuery()->getQuery());
		//$str = $this->getSelectQuery()->getQuery();
		//Tracer::getInstance()->log($str, LOGLEV_ERROR);	
		$this->resultSet = $this->getSelectQuery()->executeQuery();
	}
	
	/*
	 * function modifies DbQueryBuilder object by linking an intermediate table
	 * it accepts the $joinTablesDetailsArray reference that can be used to store the joined table, on column and optionally type of join and base table
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
	private function addSortingColumns(DbQueryBuilder& $selectQuery)
	{
		foreach($this->getSortingColumnList() as $dbColConfObj)
		{
			if( $dbColConfObj !== null)
			{
				if($dbColConfObj->getColumnIndex() !== null) {
					// old implementation comented 
					//if($dbColConfObj->getColumnJoinTab() !== null) {
					//	$selectQuery->addJoinTable(TABLE_PREFIX.$dbColConfObj->getColumnJoinTab(),$dbColConfObj->getColumnIndex()); // inner join TABLE
					//	$selectQuery->addColumns(array($dbColConfObj->getColumnIndex(),$dbColConfObj->getColumnRealName()),TABLE_PREFIX.$dbColConfObj->getColumnJoinTab());
					//	$selectQuery->addOrderBy($dbColConfObj->getColumnRealName(), SQLORDERBY::_ASC,TABLE_PREFIX.$dbColConfObj->getColumnJoinTab());
					//} else {
					//	$selectQuery->addColumns(array($dbColConfObj->getColumnIndex(),$dbColConfObj->getColumnRealName()));
					//	$selectQuery->addOrderBy($dbColConfObj->getColumnIndex(), SQLORDERBY::_ASC);
					//}				
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
						$selectQuery->addOrderBy($dbColConfObj->getColumnRealName(),  SQLORDERBY::_ASC,  $joinTab);
					} else {
						$selectQuery->addOrderBy($dbColConfObj->getColumnIndex(), SQLORDERBY::_ASC);
					}				
					$columnsToAdd = array($dbColConfObj->getColumnIndex(),$dbColConfObj->getColumnRealName());
					// dirty hack for including 'hlink' in case sorting by feature is used
					// ToDo. Figure out how to handle this think in a generic way
					if($dbColConfObj->getColumnIndex() == 'fid')
					{
						array_push($columnsToAdd, 'hlink');
					}
					$selectQuery->addColumns($columnsToAdd, $joinTab); 					
					array_push($this->sortingColumnInxs,$dbColConfObj->getColumnIndex());
				} else {
					$selectQuery->addColumns(array($dbColConfObj->getColumnRealName()));
					$selectQuery->addOrderBy($dbColConfObj->getColumnRealName(), SQLORDERBY::_ASC);
					array_push($this->sortingColumnInxs,$dbColConfObj->getColumnRealName());
				}
			}
		}
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
	 * function return a dbColumnList with searchable columns
	 */ 
	protected function getSearchableColumnList()
	{
		if( empty($this->searchableColumnConfig) )
		{
			$searchableColumnConfig = new DbColumnList();
			$iterator = $this->getColumnConfig()->getIterator();
			while( $iterator->valid() )
			{
				if($iterator->current()->is_enabled() && $iterator->current()->is_searchable())
				{
					$searchableColumnConfig->addColumn(clone $iterator->current());
				}	
				$iterator->next();
			}
			$this->searchableColumnConfig = $searchableColumnConfig;
		}
		return $this->searchableColumnConfig;
	}
	
	/*
	 * function return a dbColumnList with searchable columns
	 */ 
	protected function getSortingColumnList()
	{
		if( empty($this->sortingColumns) )
		{
			$sortingColumnList = new DbColumnList();
			// get sorting column for a requested compareByColumn from the CompareByConfiguration
			$sortingColumnNames = $this->getCompareByConfiguration()->getCompareByConfigList()->getCompareByConfig($this->getCompareByCfgKey())->getSortingColumns();
			foreach ($sortingColumnNames AS $columnName)
			{
				$sortingColumn = $this->getSearchableColumnList()->getDbColumnByRealName($columnName);
				if(! empty($sortingColumn))
				{
					$sortingColumnList->addColumn($sortingColumn);
				}
			}
			$this->sortingColumns = $sortingColumnList;
		}
		return $this->sortingColumns;
	}
	
	/*
	 * function returns a structure containing build compare configuration information retrieved from preferences
	 */ 
	protected function getCompareByConfiguration()
	{
		if( empty($this->compareByConfiguration) )
		{
			$this->compareByConfiguration = new BuildCompareConfig(Config::getInstance()->getPreference('buildcompare'));
		}
		return $this->compareByConfiguration;
	}	
}

/*****
 * class BuildCompareTBodyPassRateModel prepares a data model for a build compare main table body
 *****/
class BuildCompareTBodyPassrateModel extends TestResultsRateModel
{
	protected $columnConfig = null; // DbColumnList containing column config information retrieved from preferences
	protected $searchableColumnConfig = null; // subset of $columnConfig with dbColumn which are searchable
	protected $sortingColumns = null; // subset of $searchableColumnConfig with dbColumn which constitute a compare build row selection criteria
	protected $groupingColumns = null; // subset of $searchableColumnConfig with dbColumn which constitute a compare build row selection criteria
	protected $compareByConfiguration = null; // structure containing build compare configuration information retrieved from preferences
	protected $compareByColumn = null; // name of the column for which the compare shall be done
	protected $compareByCfgKey = null;   // Name of the key to the compareByConfiguration settings, compareBy category
	protected $sortingColumnInxs = array(); // list of column indices for the corresponding $sortingColumns
	
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		$this->getColumnConfig('testresults');
		$this->compareByColumn = 'passrate'; 
		$this->compareByCfgKey = 'passrate';
		$groupingColumnNames = $this->getCompareByConfiguration()->getCompareByConfigList()->getCompareByConfig($this->compareByCfgKey)->getGroupingColumns();
		parent::__construct($groupingColumnNames);
		$this->setOrder();
	}
	
	/*
	 * function returns a name of the column used as comparision criteria
	 */
	public function getCompareByColumnName()
	{
		return $this->compareByColumn;
	}
	
	/*
	 * function returns a name of the column used as comparision criteria
	 */
	public function getCompareByCfgKey()
	{
		return $this->compareByCfgKey;
	}	
	
	/*
	 * function returns a list of index column names matching with sortingColumn names e.g. 'tcid' for 'tcname'
	 * Note. This function shall be called after $this->getSelectQuery to return meaningfull results
	 */	
	public function getSortingColumnInxs()
	{
		if(empty($this->sortingColumnInxs))
		{
			foreach($this->getSortingColumnList() as $dbColConfObj)
			{
				if( $dbColConfObj !== null)
				{
					if($dbColConfObj->getColumnIndex() !== null) {
						array_push($this->sortingColumnInxs,$dbColConfObj->getColumnIndex());
					} else {
						array_push($this->sortingColumnInxs,$dbColConfObj->getColumnRealName());
					}
				}
			}
		}
		return $this->sortingColumnInxs;
	}

	/*
	 * function returns a structure containing build compare configuration information retrieved from preferences
	 */ 
	protected function setOrder()
	{
		$columnOrderPairs = array();
		foreach($this->getSortingColumnList() as $dbColConfObj)
		{
			if( $dbColConfObj !== null)
			{
				$columnOrderPairs[$dbColConfObj->getColumnRealName()] = SQLORDERBY::_ASC;
			}
		}
		$this->setOrderbyList($columnOrderPairs);
	}
	
	/*
	 * parent function overriden, since no need to count rows and use pagination
	 */ 
	public function show()
	{
		return parent::show();
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
	 * function return a dbColumnList with searchable columns
	 */ 
	protected function getSearchableColumnList()
	{
		if( empty($this->searchableColumnConfig) )
		{
			$searchableColumnConfig = new DbColumnList();
			$iterator = $this->getColumnConfig()->getIterator();
			while( $iterator->valid() )
			{
				if($iterator->current()->is_enabled() && $iterator->current()->is_searchable())
				{
					$searchableColumnConfig->addColumn(clone $iterator->current());
				}	
				$iterator->next();
			}
			$this->searchableColumnConfig = $searchableColumnConfig;
		}
		return $this->searchableColumnConfig;
	}
	
	/*
	 * function return a dbColumnList with searchable columns
	 */ 
	protected function getSortingColumnList()
	{
		if( empty($this->sortingColumns) )
		{
			$sortingColumnList = new DbColumnList();
			// get sorting column for a requested compareByColumn from the CompareByConfiguration
			$sortingColumnNames = $this->getCompareByConfiguration()->getCompareByConfigList()->getCompareByConfig($this->getCompareByCfgKey())->getSortingColumns();
			foreach ($sortingColumnNames AS $columnName)
			{
				$sortingColumn = $this->getSearchableColumnList()->getDbColumnByRealName($columnName);
				if(! empty($sortingColumn))
				{
					$sortingColumnList->addColumn($sortingColumn);
				}
			}
			$this->sortingColumns = $sortingColumnList;
		}
		return $this->sortingColumns;
	}
	
	/*
	 * function returns a structure containing build compare configuration information retrieved from preferences
	 */ 
	protected function getCompareByConfiguration()
	{
		if( empty($this->compareByConfiguration) )
		{
			$this->compareByConfiguration = new BuildCompareConfig(Config::getInstance()->getPreference('buildcompare'));
		}
		return $this->compareByConfiguration;
	}	
}

/****************************************************************************************************************/
/*
 * Structure of the Build Compare feature preferences stored in preferences table under the name 'buildcompare'
 */ 

/*****
 * BuildCompareCompareByConfig class defines a configuration of build compare feature for a given compareBy selection
 * e.g. for compare builds by tcverdict
 *****/
class BuildCompareCompareByConfig extends JsonObject
{	
	public function __construct($compareByConfigFields = null)
	{
		parent::__construct($compareByConfigFields);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{
		foreach ($datastructure AS $key => $value) 
		{
			$this->setParam($key, $value);
		}
	}
	
	public function getCompareByCfgKey()
	{
		return $this->getParam('key');
	}
	
	public function getCompareByColumn()
	{
		return $this->getParam('comparebycolumn');	
	}
	
	public function getSortingColumns()
	{
		return $this->getParam('sortingcolumns');	
	}
	
    public function getGroupingColumns()
	{
		return $this->getParam('groupingcolumns');	
	}

	public function setCompareByCfgKey($key)
	{
		$this->setParam('key', $key);
	}	
	
	public function setCompareByColumn($compareByColumn)
	{
		$this->setParam('comparebycolumn', $compareByColumn);	
	}
	
	public function setSortingColumns(array $sortingColumnList)
	{
		$this->setParam('sortingcolumns', $sortingColumnList);	
	}
	
	public function setGroupingColumns(array $groupingColumnList)
	{
		$this->setParam('groupingcolumns', $groupingColumnList);	
	}
}

/*****
 * Class is a container for the BuildCompareCompareByConfigList objects,
 * that can be encoded to json string
 *****/
class BuildCompareCompareByConfigList extends JsonObject
{
	public function __construct($comparebyConfigList = null)
	{
		parent::__construct($comparebyConfigList);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{
		foreach ($datastructure AS $key => $value) 
		{
			if( $value instanceof BuildCompareCompareByConfig ) {
				$this->setCompareByConfig($value);
			} else {
				$this->setCompareByConfig(new BuildCompareCompareByConfig($value) );
			}  
		}
	}
	/*
	 * function add BuildCompareCompareByConfig object to the list
	 */ 
	public function setCompareByConfig(BuildCompareCompareByConfig $comparebyConfig)
	{
		$compareByName = $comparebyConfig->getCompareByCfgKey();
		if(is_null($compareByName)) {
			$this->append( $comparebyConfig );
		} else {
			$this->setParam($compareByName, $comparebyConfig);
		}
	}
	
	/*
	 * function returns a BuildCompareCompareByConfig object for a given compareByColumn name
	 */ 
	public function getCompareByConfig($key)
	{
		return $this->getParam($key);
	}
	
	/*
	 * function returns an array with names of compareByColumns BuildCompareCompareByConfig object for a given compareByColumn name
	 */ 
	public function getCompareByColumnNames()
	{
		return $this->array_keys();
	}	
	
}
/*****
 * class defines a format of BuildCompare feature preferences stored in preferences table
 *****/
class BuildCompareConfig extends JsonObject
{	
	public function __construct($buildcompareconfig = null)
	{
		parent::__construct($buildcompareconfig);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{
		foreach ($datastructure AS $key => $value) 
		{
			if( $key === 'comparebycolumns'
			    && !($value instanceof BuildCompareCompareByConfigList)
			   )
			{
			   $value = new BuildCompareCompareByConfigList($value);			   
			}
			$this->setParam($key, $value);
		}
	}
	
	/*
	 * 'sortingcolumns' is a list of all columns that are allowed to be used
	 * for records sorting by a particular compareBy feature
	 */
	public function getSortingColumns()
	{
		return $this->getParam('sortingcolumns');	
	}

	/*
	 * 'groupingcolumns' is a list of all columns that are allowed to be used
	 * for records grouping by a particular compareBy feature
	 */	
        public function getGroupingColumns()
	{
		return $this->getParam('groupingcolumns');	
	}

	/*
	 * 'comparebycolumns' references a BuildCompareCompareByConfigList object
	 * containing settings for different compareBy features
	 */	
    public function getCompareByConfigList()
	{
		return $this->getParam('comparebycolumns');	
	}
	
	public function setSortingColumns(array $sortingColumnList)
	{
		$this->setParam('sortingcolumns', $sortingColumnList);	
	}
	
	public function setGroupingColumns(array $groupingColumnList)
	{
		$this->setParam('groupingcolumns', $groupingColumnList);	
	}
	
    public function setCompareByConfigList(BuildCompareCompareByConfigList $sortingColumnList)
	{
		$this->setParam('comparebycolumns', $sortingColumnList);	
	}
}

?>
