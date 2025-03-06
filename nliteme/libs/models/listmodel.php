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

abstract class ListModel extends Model
{
	protected $selectQuery = null;// holds DbQueryBuilder object with a ListModel specific SQL SELECT query
	protected $selectCountQuery = null; // holds DbQueryBuilder object with a ListModel specific SQL SELECT Count(*) query
	protected $dbRecord = null;	// holds the DbRecord object to work on
	protected $conditions = array();
	protected $sortByTable = null;
	protected $sortByColumn = null;
	protected $sortByOrder = null;
	protected $pageIndex = null;
	
	// results
	protected $numberOfRecords = null;
	protected $numberOfPages = null;
	protected $resultSet = array();
	
	/*
	 * constructor
	 */ 
	public function __construct(DbRecord $dbRecord)
	{
		$this->dbRecord = $dbRecord;
		$this->pageIndex = 0;
		$this->numberOfRecords = 0;
		$this->numberOfPages = 0;
	}

	/*
	 * function to perform show details action
	 * shall be overridden in child class if needed 
	 */ 
	public function showList()
	{
		// find out number of records matching search critera
		$res = $this->getSelectCountQuery()->executeQuery();
		if($res)
		{
			$row = $res->FetchRow();
			$this->numberOfRecords = $row['c'];
		}
		// calculate number of pages
		$this->numberOfPages = ceil($this->numberOfRecords / Config::getInstance()->getSettings()->getParam("paginationLimit"));
		// get resultset
		//print_r($this->getSelectQuery()->getQuery());
		$this->resultSet = $this->getSelectQuery()->executeQuery();
	}
	
	/*
	 * default function to return DbQueryBuilder object with a ListModel specific SQL SELECT query
	 * shall be overridden in child class if needed 
	 */ 
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
			
			// add ordering
			$selectQuery->addOrderBy($this->sortByColumn,$this->sortByOrder,$this->sortByTable);
			
			// add limit
			$pagLimit = Config::getInstance()->getSettings()->getParam("paginationLimit");
			$selectQuery->addLimit($this->pageIndex*$pagLimit,$pagLimit);
			
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}
	
	/*
	 * default function to returns DbQueryBuilder object with a ListModel specific SQL SELECT query used for counting
	 * shall be overridden in child class if needed 
	 */ 
	protected function getSelectCountQuery()
	{
		if(!empty($this->selectCountQuery) || ! $this->selectCountQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());

			$selectQuery->addColumns(array('COUNT(@@'.$this->dbRecord->getKeyColumn().'@@) as c')); 	
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
	
			$selectQuery->prepareQuery();
			$this->selectCountQuery = $selectQuery;
		}
		return $this->selectCountQuery;
	}
	
	/*
	 * function to perform edit (insert/update) action
	 */ 
	public function deleteList(array $indexList)
	{
		$retCode = true;
		$deleteQuery = null;
		foreach ($indexList AS $index)
		{
			$this->dbRecord->setId($index);
			$res = $this->dbRecord->delete($deleteQuery);
			if($res !== true)
			{
				$this->addMessage($res);
				$retCode = false;
			}
			$deleteQuery = $this->dbRecord->getCurrentQuery();
		}
		return $retCode;
	}
	
	/*
	 * function sets SQL ORDER BY condition (one allowed to speed up searches)
	 */ 
	public function setOrderBy($columnName, $order = SQLORDERBY::_ASC)
	{
		if(isset($columnName))
		{
			// if column is given in table_name.column_name form
			if(preg_match('/(\w+)\.(\w+)/', $columnName, $matches) ) {
				$this->sortByTable = TABLE_PREFIX.$matches[1];
				$this->sortByColumn = $matches[2];
			} else {
				$this->sortByTable = null;
				$this->sortByColumn = $columnName;
			}
			
		}
		if(isset($order))
		{
			$this->sortByOrder = $order;
		}
	}

	/*
	 * function sets page number for the results to return
	 */ 	
	public function setPage($pageIndex)
	{
		isset($pageIndex) ? $this->pageIndex = $pageIndex : null;
	}

	/*
	 * function sets input condition's list for SQL WHERE
	 */ 	
	public function setConditions(array $conditionList)
	{
		$this->conditions = $this->buildConditions($conditionList);
	}

	/*
	 * function returns the SQL select result set
	 */ 	
	public function getResultSet()
	{
		return $this->resultSet;
	}
	
	/*
	 * function returns the number of records matching the select criteria
	 */ 	
	public function getNumberOfRecords()
	{
		return $this->numberOfRecords;
	}
	
	/*
	 * function returns the number of pages for paginations
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
	 * function to returns DbRecord object
	 */ 
	public function getDbRecord()
	{
		return $this->dbRecord;
	}

	/*
	 * function prepares a condition array
	 */
	protected function buildConditions(array $conditionList)
	{
		$arr = array();
		foreach ($conditionList AS $key => $value) 
		{
		  if($this->isConditionKeyAllowed($key) === true)
		  {
			if( is_array($value) ) 
			{
				$tmp = array();
				$key = $this->checkAndAddTableTag($key);
				foreach ($value as $k => $v)
				{
					if ( isset($v) && $v != '' )
					{
						list($v, $op) = $this->getValOpArr($v);
						if (! is_numeric($v))
						{
							$v = "'".$v."'";
						}
						
						$tmp[$k] = $key.$op.$v;
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
					list($value, $op) = $this->getValOpArr($value);
					$value = $key.$op.$value;				
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
					if (! empty($value) )
					{
						$value = '('.$value.')';
					}
				}
				array_push($arr, $value);
			}
		  }
		}
		return $arr;
	}

	/*
	 * function checks if a condition $key can be used for the DB query 
	 */ 	
	protected function isConditionKeyAllowed($key)
	{
		// strip the table name from the $key if needed
		preg_match('/(\w+)\.(.*)/', $key, $matches) ?  $key = $matches[2] : $key = $key;
		if(in_array($key,$this->dbRecord->getAllowedColumns())
		   || preg_match('/.*?_TO/', $key)
		   || preg_match('/.*?_FROM/', $key)
		   )
		{
			return true;	
		} else {
			return false;	
		}
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

/*
 * Here are the classes using ListModel
 */
  
/*
 * class to handle BuildIncrement details
 */ 
class BuildIncrementsModel extends ListModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new BuildIncrement());
		// set default conditions
		$this->setOrderBy('increment', SQLORDERBY::_DESC);
		
		// limit query to last 7 days
		//$t = time() - (7 * 24 * 60 * 60);
		//$this->setConditions(array('createdate_FROM'=>date('Y-m-d', $t)));
	}
}

/*
 * class to handle Build details
 */ 
class BuildsModel extends ListModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new Build());
		// set default conditions
		$this->setOrderBy('build', SQLORDERBY::_DESC);
		
		// limit query to last 30 days
		$t = time() - (30 * 24 * 60 * 60);
		$this->setConditions(array('createdate_FROM'=>date('Y-m-d', $t)));
	}
	
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addJoinTable(TABLE_PREFIX.'build_increments','incid'); // inner join TABLE build_increments on buildid field
			
			$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('increment'),TABLE_PREFIX.'build_increments'); // add columns to select from TABLE testcases
			
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
			
			// add ordering
			$selectQuery->addOrderBy($this->sortByColumn,$this->sortByOrder,$this->sortByTable);
			
			// add limit
			$pagLimit = Config::getInstance()->getSettings()->getParam("paginationLimit");
			$selectQuery->addLimit($this->pageIndex*$pagLimit,$pagLimit);
			
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}
	
	protected function getSelectCountQuery()
	{
		if(!empty($this->selectCountQuery) || ! $this->selectCountQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addJoinTable(TABLE_PREFIX.'build_increments','incid'); // inner join TABLE build_increments on buildid field
			
			$selectQuery->addColumns(array('COUNT(@@'.$this->dbRecord->getKeyColumn().'@@) as c')); 	
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

/*
 * class to handle TestLine details
 */ 
class TestLinesModel extends ListModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new TestLine());
		// set default conditions
		$this->setOrderBy('tlname', SQLORDERBY::_DESC);
		
		// limit query to last 7 days
		//$t = time() - (7 * 24 * 60 * 60);
		//$this->setConditions(array('createdate_FROM'=>date('Y-m-d', $t)));		
	}
}

/*
 * class to handle TestSuite details
 */ 
class TestSuitesModel extends ListModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new TestSuite());
		// set default conditions
		$this->setOrderBy('tsname', SQLORDERBY::_DESC);
		
		// limit query to last 7 days
		//$t = time() - (7 * 24 * 60 * 60);
		//$this->setConditions(array('createdate_FROM'=>date('Y-m-d', $t)));		
	}
}

/*
 * class to handle TestCase details
 * TODO. Check how to handle many to many relation between TestCase<=>TestSuite
 */ 
class TestCasesModel extends ListModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new TestCase());
		// set default conditions
		$this->setOrderBy('tcname', SQLORDERBY::_DESC);
		
		// limit query to last 7 days
		//$t = time() - (7 * 24 * 60 * 60);
		//$this->setConditions(array('createdate_FROM'=>date('Y-m-d', $t)));		
	}
    
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addJoinTable(TABLE_PREFIX.'features','fid', SQLJOIN::LEFT); // inner join TABLE features on fid field
			
			$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('fname', 'hlink'),TABLE_PREFIX.'features'); // add columns to select from TABLE testcases
			
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
			
			// add ordering
			$selectQuery->addOrderBy($this->sortByColumn,$this->sortByOrder,$this->sortByTable);
			
			// add limit
			$pagLimit = Config::getInstance()->getSettings()->getParam("paginationLimit");
			$selectQuery->addLimit($this->pageIndex*$pagLimit,$pagLimit);
			
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}
	
	protected function getSelectCountQuery()
	{
		if(!empty($this->selectCountQuery) || ! $this->selectCountQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addJoinTable(TABLE_PREFIX.'features','fid', SQLJOIN::LEFT); // inner join TABLE features on fid field
			
			$selectQuery->addColumns(array('COUNT(@@'.$this->dbRecord->getKeyColumn().'@@) as c')); 	
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

class FeaturesModel extends ListModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new Feature());
		// set default conditions
		$this->setOrderBy('fname', SQLORDERBY::_DESC);
		
		// limit query to last 7 days
		//$t = time() - (7 * 24 * 60 * 60);
		//$this->setConditions(array('createdate_FROM'=>date('Y-m-d', $t)));		
	}
    
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','fid', SQLJOIN::LEFT); // inner join TABLE testcases on fid field
			
			$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('IFNULL(COUNT(@@tcid@@), 0) as num_testcases', 'IFNULL(SUM(@@coverage@@), 0) as coverage'),TABLE_PREFIX.'testcases'); // add columns to select from TABLE testcases
			
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
            
            // add group by
            $selectQuery->addGroupBy($this->dbRecord->getKeyColumn());
			
			// add ordering
			$isSortAlias = (in_array($this->sortByColumn, array('num_testcases', 'coverage'))) ? True : False;
			$selectQuery->addOrderBy($this->sortByColumn,$this->sortByOrder,$this->sortByTable, $isSortAlias);
			
			// add limit
			$pagLimit = Config::getInstance()->getSettings()->getParam("paginationLimit");
			$selectQuery->addLimit($this->pageIndex*$pagLimit,$pagLimit);
			
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}
}

/*
 * class to handle TestSuite details
 */ 
class TsTcMapModel extends ListModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new TestSuite());
		// set default conditions
		$this->setOrderBy('tsid', SQLORDERBY::_DESC);
		
		// limit query to last 7 days
		//$t = time() - (7 * 24 * 60 * 60);
		//$this->setConditions(array('createdate_FROM'=>date('Y-m-d', $t)));		
	}
	
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder(TABLE_PREFIX.'suites_cases_map');
			$selectQuery->addColumns(array('tsid', 'tcid')); // add columns to select from TABLE suites_cases_map
			$selectQuery->addJoinTable($this->dbRecord->getTableName(),'tsid'); // inner join TABLE testsuites on tsid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','tcid'); // inner join TABLE testcases on tcid field
			$selectQuery->addColumns(array('tsname'),$this->dbRecord->getTableName()); // add columns to select from TABLE testsuites	
			$selectQuery->addColumns(array('tcname'),TABLE_PREFIX.'testcases'); // add columns to select from TABLE testcases		
			
			foreach ($this->conditions AS $value) 
			{
				$selectQuery->addCondition($value);
			}
			
			// add ordering
			$selectQuery->addOrderBy($this->sortByColumn,$this->sortByOrder,$this->sortByTable);
			
			// add limit
			$pagLimit = Config::getInstance()->getSettings()->getParam("paginationLimit");
			$selectQuery->addLimit($this->pageIndex*$pagLimit,$pagLimit);
			
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}
	
	protected function getSelectCountQuery()
	{
		if(!empty($this->selectCountQuery) || ! $this->selectCountQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder(TABLE_PREFIX.'suites_cases_map');
			$selectQuery->addJoinTable($this->dbRecord->getTableName(),'tsid'); // inner join TABLE testsuites on tsid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','tcid'); // inner join TABLE testcases on tcid field
			$selectQuery->addColumns(array('COUNT(@@'.$this->dbRecord->getKeyColumn().'@@) as c')); 	
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

?>
