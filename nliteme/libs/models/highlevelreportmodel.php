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
 * model that holds pass rate per increment, build and test suite
 *****/
class HighLevelReportModel extends TestResultsRateModel
{
	private $buildLimit = 10;   // limits the number of buildId for the build selection query
	private $pageIndex = null;   // indicate the limit range for the build selection query
	private $buildIds = array(); // array containing a list of build id for which the Test Results Rate is calculated
							     // has to be set otherwise the TestResultsRateModel will not return a resultset (performance reasons)
	private $buildSelectionCountQuery = null; // holds a query for a total number of builds from a build selection query
	private $buildSelectionQuery = null;      // holds a query for selecting the buildId
	private $buildSelectionConditions = array(); // list of conditions to be used in a build selection query
	private $numberOfBuildRecords = null; // total number of build fullfilling the build selection query
	private $numberOfPages = null; // total number of pages for give n build selction query
	/*
	 * constructor
	 */ 
	public function __construct(array $groupingColumns, $countedColumn = 'DISTINCT @@tcid@@')
	{
		parent::__construct($groupingColumns, $countedColumn);
		$this->pageIndex = 0;
		$this->numberOfBuildRecords = 0;
		$this->numberOfPages = 0;
	}

	/*
	 * function to perform show details action
	 */ 
	public function show()
	{
		$result = false;
		// retrieve a list of buildId to be used for the selection and update the number of pages
		$this->updateBuildIdsFromSelectionQuery();
		// if buildIds list is not empty retrieve a correspoding rate results
		if(!empty($this->buildIds))
		{
			// set build condition for the rate result query
			$this->setBuildIdCondition($this->buildIds);
		
			// call the rate results for a given condition
			$result = parent::show();
		}
		return $result;
	}
	
	/*
	 * functions sets the buildIds for which the High Level Report shall be presented
	 * argument: array with buildIds
	 */ 	
	public function setBuildIds(array $buildIdsArray)
	{
		if(! empty($buildIdsArray) )
		{
			$this->buildIds = $buildIdsArray;
		}
	}

	/*
	 * functions sets the max number of builds for build selection (to limit the page size)
	 * argument: maximum number of build to select per page
	 */ 	
	public function setBuildLimit($buildLimit)
	{
		$this->buildLimit = $buildLimit;
	}
		
	/*
	 * function sets page number for build selection query
	 * to be used in case BuildIds are not set explicitly from the controller
	 * e.g. in case the build selection query parameters are given and the build selection shall be done internally
	 */ 	
	public function setPage($pageIndex)
	{
		isset($pageIndex) ? $this->pageIndex = $pageIndex : 0;
	}
	
	/*
	 * function sets an input condition's list for the BuildIds selection query
	 */ 
	public function setBuildSelectionConditions(array $conditionList)
	{
		$this->buildSelectionConditions = $this->buildConditions($conditionList);
	}
	
	/*
	 * function returns the number of builds matching the build selection criteria
	 */ 	
	public function getTotalNumberOfBuild()
	{
		return $this->numberOfBuildRecords;
	}
	
	/*
	 * function returns the number of pages based for a given build selection criteria
	 */ 	
	public function getNumberOfPages()
	{
		return $this->numberOfPages;
	}

	/*
	 * function returns the page index used for limiting resultset
	 */ 	
	public function getCurrentPage()
	{
		return $this->pageIndex;
	}
	
	/*
	 * function sets the list of for BuildIds based on the build selection query
	 * in case buildIds list is not empty update the number of build records and number of pages
	 */ 	
	protected function updateBuildIdsFromSelectionQuery()
	{
		if(empty($this->buildIds)) {
		
			// update total number of build for a given query
			$res = $this->getBuildsCountFromSelectionQuery()->executeQuery();
			if($res)
			{
				$row = $res->FetchRow();
				$this->numberOfBuildRecords = $row['c'];
			}
			// update number of pages
			// calculate number of pages
			$this->numberOfPages = ceil($this->numberOfBuildRecords / $this->buildLimit);
			
			$buildQuery = $this->getBuildsFromSelectionQuery();
			$rs = $buildQuery->executeQuery();
			if(! $rs->EOF )
			{
				$buildArr = array();
				foreach($rs->GetRows() AS $row)
				{
					array_push($buildArr, $row['buildid']);
				}
				$this->setBuildIds($buildArr);
			}
		} else {
			$this->numberOfBuildRecords = count($this->buildIds);
			$this->numberOfPagesumber = ceil($this->numberOfBuildRecords / $this->buildLimit);
		}
	}	
	
	/*
	 * function returns a DbQueryBuilder object for BuildIds selection query
	 */ 	
	protected function getBuildsFromSelectionQuery()
	{
		if(!empty($this->buildSelectionQuery) || ! $this->buildSelectionQuery instanceof DbQueryBuilder)
		{
			$buildQuery = new DbQueryBuilder(TABLE_PREFIX.'builds');
			$buildQuery->addColumns(array('buildid'));
			foreach ($this->buildSelectionConditions AS $value) 
			{
				$buildQuery->addCondition($value);
			}			
			$buildQuery->addOrderBy('testdate',SQLORDERBY::_DESC);
			$buildQuery->addLimit($this->pageIndex*$this->buildLimit,$this->buildLimit);
			$buildQuery->prepareQuery();
			$this->buildSelectionQuery = $buildQuery;
		}
		return $this->buildSelectionQuery;
	}

	/*
	 * function returns a DbQueryBuilder object for a total count of build from the buildIds selection query
	 * used to determine the number of pages to iterate through
	 */ 	
	protected function getBuildsCountFromSelectionQuery()
	{
		if(!empty($this->buildSelectionCountQuery) || ! $this->buildSelectionCountQuery instanceof DbQueryBuilder)
		{
			$buildCountQuery = new DbQueryBuilder(TABLE_PREFIX.'builds');
			$buildCountQuery->addColumns(array('COUNT(@@buildid@@) as c'));
			foreach ($this->buildSelectionConditions AS $value) 
			{
				$buildCountQuery->addCondition($value);
			}
			$buildCountQuery->prepareQuery();
			$this->buildSelectionCountQuery = $buildCountQuery;
		}
		return $this->buildSelectionCountQuery;
	}	
	
	/*
	 * function prepares a condition array
	 */
	protected function buildConditions(array $conditionList)
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
}

/*****
 * model that holds the pass rate per test line for a given buildId
 *****/
class HighLevelReportDetailPassRateModel extends TestResultsRateModel
{
	/*
	 * constructor
	 */ 
	public function __construct(array $buildIds = array(), array $groupingColumns = array())
	{
		parent::__construct($groupingColumns, 'DISTINCT @@tcid@@');
		// set build condition for the rate result query
		$this->setBuildIdCondition($buildIds);
	}
}

/*****
 * model calculates number of failed distinct test cases due to a given defect per test line for a given buildId
 *****/
class HighLevelReportDetailDefectTLModel extends HighLevelReportDetailPassRateModel
{
	/*
	 * constructor
	 */ 
	public function __construct(array $buildIds = array(), array $groupingColumns = array())
	{
		parent::__construct($buildIds, $groupingColumns);
	}
}

/*****
 * same as HighLevelReportDetailPassRateModel, but used for a tooltip content generation
 *****/
class HighLevelReportDetailPassRateTooltipModel extends HighLevelReportDetailPassRateModel
{
	public function __construct(array $buildIds = array(), array $groupingColumns = array())
	{
		parent::__construct($buildIds, $groupingColumns);
	}
}

/*****
 * class HighLevelReportListSearcherModel prepares a data model for a HLR search form
 *****/
class HighLevelReportSearcherModel extends TestResultsSearcherModel
{
	private $searchColumns = array('createdate','build','increment','tsname','fname', 'extracolumn_2');
	private $groupbyFields = array('tsname', 'fname', 'extracolumn_2');

	/*
	 * function return a dbColumnList with searchable columns
	 */ 
	protected function getSearchableColumnList()
	{
		if( empty($this->searchableColumnConfig) )
		{
			$searchableColumnConfig = new DbColumnList();
			$searchableColumnConfig->addColumn($this->getGroupbyColumn());
			$iterator = parent::getSearchableColumnList()->getIterator();
			while( $iterator->valid() )
			{
				if(in_array($iterator->current()->getColumnRealName(), $this->searchColumns))
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
	 * function fills the builds related params for the search form
	 */ 	
	protected function getGroupbyColumn()
	{
		$column = new DbColumnConfig(array('realname'=>'groupby'));
		$column->setSearcherFieldType(FIELDTYPES::_SM);
		$column->setParam('searchable','yes');
		$column->setColumnPredefinedValues($this->groupbyFields);
		return $column;
	}	
}

/*****
 * model that holds the pass rate per test line for a given buildId
 *****/
class HighLevelReportDetailVerdictModel extends TestResultsModel
{
	/*
	 * function prepare a select query for HLR Detail Verdict
	 */  
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName(),array(),SQLCOMMAND::_SELECT_DISTINCT);

			$selectQuery->addJoinTable(TABLE_PREFIX.'build_increments','incid'); // inner join TABLE build_increments on buildid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'builds','buildid'); // inner join TABLE builds on buildid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testsuites','tsid'); // inner join TABLE testsuites on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','tcid'); // inner join TABLE testcases on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'features','fid',SQLJOIN::LEFT,TABLE_PREFIX.'testcases'); // inner join TABLE features on fid field via testcases
			
			// MKMK commented out due to large description part (can be set back when description is moved to other table), For now use explicit list of columns
			//$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('incid','buildid','tsid','tcid', 'extracolumn_2','MIN(@@tcverdict@@) as tcverdict')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('increment'),TABLE_PREFIX.'build_increments'); // add columns to select from TABLE testcases
			$selectQuery->addColumns(array('build'),TABLE_PREFIX.'builds'); // add columns to select from TABLE testcases
			$selectQuery->addColumns(array('tsname'),TABLE_PREFIX.'testsuites'); // add columns to select from TABLE testsuites
			$selectQuery->addColumns(array('tcname', 'fid', 'coverage'),TABLE_PREFIX.'testcases'); // add columns to select from TABLE testcases		
			$selectQuery->addColumns(array('fname', 'hlink'),TABLE_PREFIX.'features'); // add columns to select from TABLE testlines
            //		
			// add conditions
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
			
			// add group by
			$selectQuery->addGroupBy('tcid');
			
			// add ordering
			$selectQuery->addOrderBy('tcname',SQLORDERBY::_ASC,TABLE_PREFIX.'testcases');
			$selectQuery->addOrderBy('tcverdict');
			
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}

	protected function getSelectCountQuery()
	{
		if(!empty($this->selectCountQuery) || ! $this->selectCountQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName(),array('COUNT(distinct @@tcid@@) as c'));	
			$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','tcid'); // inner join TABLE testcases on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'features','fid', SQLJOIN::LEFT,TABLE_PREFIX.'testcases'); // inner join TABLE features on fid field via testcases
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
	
			$selectQuery->prepareQuery();
			$this->selectCountQuery = $selectQuery;
		}
		return $this->selectCountQuery;
	}
}

/*****
 * model for listing testcases with failing executions due to a given defect
 *****/
class HighLevelReportDetailDefectTcModel extends HighLevelReportDetailVerdictModel
{
	/*
	 * function prepare a select query for HLR Detail Verdict
	 */  
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName(),array(),SQLCOMMAND::_SELECT_DISTINCT);

			$selectQuery->addJoinTable(TABLE_PREFIX.'build_increments','incid'); // inner join TABLE build_increments on buildid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'builds','buildid'); // inner join TABLE builds on buildid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testsuites','tsid'); // inner join TABLE testsuites on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','tcid'); // inner join TABLE testcases on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'features','fid',SQLJOIN::LEFT,TABLE_PREFIX.'testcases'); // inner join TABLE features on fid field via testcases
			
			// MKMK commented out due to large description part (can be set back when description is moved to other table), For now use explicit list of columns
			//$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('incid','buildid','tsid','tcid', 'extracolumn_2')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('increment'),TABLE_PREFIX.'build_increments'); // add columns to select from TABLE testcases
			$selectQuery->addColumns(array('build'),TABLE_PREFIX.'builds'); // add columns to select from TABLE testcases
			$selectQuery->addColumns(array('tsname'),TABLE_PREFIX.'testsuites'); // add columns to select from TABLE testsuites
			$selectQuery->addColumns(array('tcname', 'fid', 'coverage'),TABLE_PREFIX.'testcases'); // add columns to select from TABLE testcases		
			$selectQuery->addColumns(array('fname', 'hlink'),TABLE_PREFIX.'features'); // add columns to select from TABLE testlines
            //		
			// add conditions
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
			
			// add group by
			$selectQuery->addGroupBy('tcid');
			
			// add ordering
			$selectQuery->addOrderBy('tcname',SQLORDERBY::_ASC,TABLE_PREFIX.'testcases');
			
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}

	protected function getSelectCountQuery()
	{
		if(!empty($this->selectCountQuery) || ! $this->selectCountQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName(),array('COUNT(distinct @@tcid@@) as c'));	
			$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','tcid'); // inner join TABLE testcases on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'features','fid', SQLJOIN::LEFT,TABLE_PREFIX.'testcases'); // inner join TABLE features on fid field via testcases
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
	
			$selectQuery->prepareQuery();
			$this->selectCountQuery = $selectQuery;
		}
		return $this->selectCountQuery;
	}
}

class HighLevelReportDetailTabModel extends DashboardTabModel
{	
	/*
	 * function returns dashboard tab configuration 
	 */ 
	protected function getConfiguration()
	{
		// TBD. this will be read from DB where user specific dashboard is defined
		return HighLevelReportDetailConfig::getInstance()->getDashboardConfig($this->getCfgId())->getDashboardTabList()->getItem($this->getTabIndex());
	}
}

class HighLevelReportDetailModel extends DashboardModel
{		
	/*
	 * function returns dashboard configuration 
	 */ 
	protected function getConfiguration()
	{
		// TBD. this will be read from DB where user specific dashboard is defined
		return HighLevelReportDetailConfig::getInstance()->getDashboardConfig($this->getCfgId());
	}
}

/*
 * class holding HighLevelReportDetail dashboard configuration
 */
 class HighLevelReportDetailConfig
{
	private static $instance;
	private static $dashboardConfigList = array();
	private function __construct() {}
	private function __clone() {}

    /*
     * function returns a reference to Config singleton instance 
     */ 
	public static function getInstance()
	{
        if (! isset(self::$instance) ) {
            self::$instance = new HighLevelReportDetailConfig();
            self::reinitialize();
        }
        return self::$instance;
    }
    
    /*
     * function to re/initialize the singleton
     */ 
    public static function reinitialize()
    {
		// read preferences table to the temp array
		if(empty(self::$dashboardConfigList))
		{
			$dashboardConfigStringTestuite = '{"dashboardtablist":{"0":{"dashboardcolumnlist":{"0":{"widgetconfiglist":{"0":{"name":"HighLevelReportDetailPassRate","url":"action=com.nliteme.HighLevelReportDetailPassRate","useiframe":"no"},"1":{"name":"HighLevelReportDetailVerdict","url":"action=com.nliteme.HighLevelReportDetailVerdict","useiframe":"no"}}}},"name":"High Level Report - Testsuites"}}}';
			$dashboardConfigStringFeature  = '{"dashboardtablist":{"0":{"dashboardcolumnlist":{"0":{"widgetconfiglist":{"0":{"name":"HighLevelReportDetailPassRate","url":"action=com.nliteme.HighLevelReportDetailPassRate","useiframe":"no"},"1":{"name":"HighLevelReportDetailVerdict","url":"action=com.nliteme.HighLevelReportDetailVerdict","useiframe":"no"}}}},"name":"High Level Report - Features"}}}';
			$dashboardConfigStringFeatTs   = '{"dashboardtablist":{"0":{"dashboardcolumnlist":{"0":{"widgetconfiglist":{"0":{"name":"HighLevelReportDetailPassRate","url":"action=com.nliteme.HighLevelReportDetailPassRate","useiframe":"no"},"1":{"name":"HighLevelReportDetailVerdict","url":"action=com.nliteme.HighLevelReportDetailVerdict","useiframe":"no"}}}},"name":"High Level Report - Features & Testsuites"}}}';
			$dashboardConfigStringDefectTc = '{"dashboardtablist":{"0":{"dashboardcolumnlist":{"0":{"widgetconfiglist":{"0":{"name":"HighLevelReportDetailDefectTL","url":"action=com.nliteme.HighLevelReportDetailDefectTL","useiframe":"no"},"1":{"name":"HighLevelReportDetailDefectTc","url":"action=com.nliteme.HighLevelReportDetailDefectTc","useiframe":"no"}}}},"name":"High Level Report - Failed Test Cases for Defect"}}}';
			$dashboardConfigStringDefectTr = '{"dashboardtablist":{"0":{"dashboardcolumnlist":{"0":{"widgetconfiglist":{"0":{"name":"HighLevelReportDetailPassRate","url":"action=com.nliteme.HighLevelReportDetailPassRate","useiframe":"no"},"1":{"name":"HighLevelReportDetailVerdict","url":"action=com.nliteme.HighLevelReportDetailVerdict","useiframe":"no"}}}},"name":"High Level Report - Test Runs per Defect"}}}';
			
			array_push(self::$dashboardConfigList, 
				new DashboardConfig($dashboardConfigStringTestuite),
				new DashboardConfig($dashboardConfigStringFeature),
				new DashboardConfig($dashboardConfigStringFeatTs),
				new DashboardConfig($dashboardConfigStringDefectTc),
				new DashboardConfig($dashboardConfigStringDefectTr));
		}
	}
	
	/*
	 * function returns an Dashboard configuration object containing WidgetConfig
	 */ 
	public function getDashboardConfig($cfg = 0)
	{
		if (! array_key_exists($cfg, self::$dashboardConfigList))
		{
			$cfg = array_key_first(self::$dashboardConfigList);
		}
		return self::$dashboardConfigList[$cfg];
	}
}
?>
