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
 
 /**
  *  SQL JOIN enum emulation 
  */
class SQLJOIN
{
    const INNER = 'INNER';
    const LEFT = 'LEFT';
    const RIGHT = 'RIGHT';
    const FULL = 'FULL';
}
 /**
  *  SQL Logical operators enum emulation 
  */
class SQLLOGOPER
{
    const _OR = 'OR';
    const _AND = 'AND';
}
 /**
  *  SQL JOIN enum emulation 
  */
class SQLORDERBY
{
    const _ASC = 'ASC';
    const _DESC = 'DESC';
}

 /**
  *  Supported SQL COMMAND enum emulation 
  */
class SQLCOMMAND
{
    const _SELECT = 'SELECT';
    const _SELECT_DISTINCT = 'SELECT DISTINCT';
    const _INSERT = 'INSERT';
    const _UPDATE = 'UPDATE';
    const _DELETE = 'DELETE';
}

 /**
  *  SQL UNION Type enum emulation 
  */
class SQLUNION
{
    const _UNION = 'UNION';
    const _UNIONALL = 'UNION ALL';
    const _UNIONDISTINCT = 'UNION DISTINCT';
}


/*
 * 	class defines the way SQL queries are done,
 * 	it allows to execute query
 */ 
class DbQueryBuilder{
	/** 
	 * members
	 */
	private $preparedQuery = '';
	private $sqlCommand = '';
	private $columns = array();
	private $dateLimit = '';
	private $conditions = array();
	private $sorts = array();
	private $groupby = array();
	private $fromTable = '';
	private $fromTableAlias = null;
	private $joinTables = array();
	private $resultLimit = '';
	private $unions = array();
	
	/* constructor
	 * 
	 */
	public function __construct($fromTableToSet, $arrayOfColumnsToAdd=NULL, $sqlCommand = SQLCOMMAND::_SELECT, $fromTableAliasToSet = null)
    {
		$this->fromTable = $fromTableToSet;
		if($sqlCommand == SQLCOMMAND::_SELECT)
		{
			$this->fromTableAlias = $fromTableAliasToSet;
		}
		$this->sqlCommand = $sqlCommand;

		if( isset($arrayOfColumnsToAdd) )
		{
			$this->addColumns($arrayOfColumnsToAdd);
		}
	}
	/* destructor
	 * 
	 */
	public function __destruct()
    {
	}

	/**
	 * function gets FROM/INTO table
	 */
	public function getTable()
	{
		return isset($this->fromTableAlias) ? $this->fromTableAlias : $this->fromTable;
	}
	/**
	 * function gets SQL Command type
	 */
	public function getSqlCommand()
	{
		return $this->sqlCommand;
	}
	/**
	 * function returns sql JOIN rules as string
	 */
	public function getJoinTablesAsString()
	{
		if(empty($this->joinTables)) { 
			return '';
		} else {
			return implode(' ', $this->joinTables);
		}
	}
	/**
	 * function returns columns to select/update/insert as string
	 */
	public function getColumnsAsString()
	{
		if(empty($this->columns)) { 
			return '';
		} else {
			return implode(', ', $this->columns);
		}
	}

	/**
	 * function returns columns to select/update/insert as array
	 */
	public function getColumns()
	{
		return $this->columns;
	}	

	/**
	 * function returns sql WHERE rules as string
	 */
	public function getConditionsAsString()
	{
		if(empty($this->conditions)) { 
			return '';
		} else {
			return implode(' ', $this->conditions);
		}
	}
	/**
	 * function returns sql ORDER BY rules as string
	 */
	public function getSortingAsString()
	{
		if(empty($this->sorts)) { 
			return '';
		} else {
			return implode(', ', $this->sorts);
		}
	}
	/**
	 * function returns sql GROUP BY rules as string
	 */
	public function getGroupByAsString()
	{
		if(empty($this->groupby)) { 
			return '';
		} else {
			return implode(', ', $this->groupby);
		}
	}	

	/**
	 * function returns sql WHERE rules as string
	 */
	public function getUnionsAsString()
	{
		if(empty($this->unions)) { 
			return '';
		} else {
			return implode(' ', $this->unions);
		}
	}
	
	/**
	 * function adds columns to select
	 */
	public function addColumns($columnsArray, $tableName = NULL)
	{
		// if $tableName is empty, set FROM table for the columns to be added
		if(! isset($tableName) ) {
			$tableName = $this->getTable();
		}
		// if we are here then the $tableName must have been set, so check if it exists on FROm or JOIN list
		else if(! $this->existsTable($tableName)) {
			$str = get_class($this)."::".__FUNCTION__.": ". $tableName. "has not been added to FROM or JOIN Tables list\n";
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return false;
		}
			
		if(is_array($columnsArray))
		{
			foreach($columnsArray as $key => $column)
			{
				$columnsArray[$key] = $this->addTableName($column,$tableName);
			}
			$this->columns = array_merge($this->columns, $columnsArray);
		} else {
			array_push($this->columns, $this->addTableName($columnsArray,$tableName) ); 
		}
			
		return true;
	}

	/**
	 * function adds additional table to join with from table by certain column name
	 */
	public function addJoinTable($tableName, $onColumnName, $typeOfJoin = SQLJOIN::INNER, $baseTableName = null)
	{
		if($this->getSqlCommand() == SQLCOMMAND::_SELECT || $this->getSqlCommand() == SQLCOMMAND::_SELECT_DISTINCT) {
			if($baseTableName === null) {
				$baseTableName = $this->getTable();
			}
			if($baseTableName == $tableName) {
				$str = get_class($this)."::".__FUNCTION__.": ".$tableName . " is specified already as FROM table\n";
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return false;
			} else if ( $this->existsTable($tableName) ) {
				$str = get_class($this)."::".__FUNCTION__.": ".$tableName . " is specified already added to JOIN Tables list\n";
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return false;
			} else if (! $this->existsTable($baseTableName) ) {
				$str = get_class($this)."::".__FUNCTION__.": ".$baseTableName . " VIA table is NOT yet added to JOIN Tables list\n";
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return false;
			} else {
				switch ($typeOfJoin) {
				
					case SQLJOIN::INNER:
					case SQLJOIN::LEFT:
					case SQLJOIN::RIGHT:
					case SQLJOIN::FULL:
						$this->joinTables[$tableName] = $typeOfJoin . " JOIN " . $tableName . " ON " . $baseTableName . '.'. $onColumnName . '=' . $tableName . '.' . $onColumnName;
						break;
					default:
						$str = get_class($this)."::".__FUNCTION__.": Invalid table JOIN type: " . $typeOfJoin." for table: ".$tableName . "\n";
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
						return false;
				}
				return true;
			}
		} else {
			$str = get_class($this)."::".__FUNCTION__.": JOIN table statement not supported in SQL Command: ". $this->getSqlCommand() . "\n";
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return false;
		}
	}
	
	/**
	 * function adds query condition
	 */
	public function addCondition($condString,$operator=SQLLOGOPER::_AND)
	{
		if($this->getSqlCommand() != SQLCOMMAND::_INSERT) {
			if(count($this->conditions)>0) {
				switch($operator) {
					case SQLLOGOPER::_OR:
					case SQLLOGOPER::_AND:
						break;
					default:
						$operator = SQLLOGOPER::_OR;
						break;
				}
			} else {
				$operator = ' WHERE ';
			}
			// add From Table Name 
			$condString = array_push($this->conditions, $operator." ".$condString);
		} else {
			$str = get_class($this)."::".__FUNCTION__.": Cannot add WHERE condition into SQL Command: ". $this->getSqlCommand() . "\n";
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return false;
		}
	}
	
	/**
	 * function sets the limit for returned records
	 */
	public function addLimit($offset, $numOfRecords = 100)
	{
		if($this->getSqlCommand() != SQLCOMMAND::_INSERT) {
			$this->resultLimit = "LIMIT " . $offset . ", " . $numOfRecords;
		} else {
			$str = get_class($this)."::".__FUNCTION__.": Cannot add LIMIT into SQL Command: ". $this->getSqlCommand() . "\n";
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return false;
		}
	}
	
	/**
	 * function adds order condition
	 */
	public function addOrderBy($onColumnName, $order = SQLORDERBY::_ASC, $tableName = NULL, $isColumnAlias = False)
	{
		if($this->getSqlCommand() != SQLCOMMAND::_INSERT) {
			// if $tableName is empty, set FROM table for the columns to be added
			if(! isset($tableName) ) {
				$tableName = $this->getTable();
			}
			
			if(! $this->existsTable($tableName) )
			{
				$str = get_class($this)."::".__FUNCTION__.": table " . $tableName . " is not on FROM or JOIN list\n";
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return false;
			}
			
			switch($order) {
					case SQLORDERBY::_ASC:
					case SQLORDERBY::_DESC:
						break;
					default:
						$str = get_class($this)."::".__FUNCTION__.": Invalid ORDER BY type: " . $order . "\n";
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
						return false;
			}
			$sep = "ORDER BY ";
			if(count($this->sorts)>0) {
				$sep = "";
			} 
			
			$tabtag = ($isColumnAlias === True) ? '' : $tableName . '.';
			array_push($this->sorts, $sep . $tabtag . $onColumnName . " " . $order);
			return true;
		} else {
			$str = get_class($this)."::".__FUNCTION__.": Cannot add ORDERBY into SQL Command: ". $this->getSqlCommand() . "\n";
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return false;
		}
	}

	/**
	 * function adds group by condition
	 */
	public function addGroupBy($onColumnName, $tableName = NULL)
	{
		if($this->getSqlCommand() != SQLCOMMAND::_INSERT) {
			// if $tableName is empty, set FROM table for the columns to be added
			if(! isset($tableName) ) {
				$tableName = $this->getTable();
			}
			
			if(! $this->existsTable($tableName) )
			{
				$str = get_class($this)."::".__FUNCTION__.": table " . $tableName . " is not on FROM or JOIN list\n";
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return false;
			}
			
			$sep = "GROUP BY ";
			if(count($this->groupby)>0) {
				$sep = "";
			} 
			array_push($this->groupby, $sep . $tableName . '.' . $onColumnName);
			return true;
		} else {
			$str = get_class($this)."::".__FUNCTION__.": Cannot add GROUP BY into SQL Command: ". $this->getSqlCommand() . "\n";
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return false;
		}
	}

	/**
	 * function adds union query string to the current query
	 * Note. The $queryString has to be a SELECT query string
	 * Note1. The UNION is only added if $this->queryType and $queryString are SELECT
	 */
	public function addUnionQuery($queryString,$unionType=SQLUNION::_UNIONALL)
	{
		if($this->getSqlCommand() == SQLCOMMAND::_SELECT && preg_match('/^\s*SELECT/i',$queryString)) {

			array_push($this->unions, $unionType." (".$queryString.")");
		} else {
			$str = get_class($this)."::".__FUNCTION__.": Cannot add UNION into SQL Command: ". $this->getSqlCommand() . "\n";
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return false;
		}
	}
	
	/**
	 * function precompiles the query - to speed up
	 * especialy usefull for bulk insert/update queries
	 */
	public function prepareQuery()
	{
		// first unset the existing preparedQuery so that $this->getQuery works
		$this->preparedQuery = '';
		// get sql string and precompile it
		$this->preparedQuery = DbConnectionFactory::getFactory()->getConnection()->Prepare($this->getQuery());
		//$str = get_class($this)."::".__FUNCTION__.": preparing".$this->getQuery()."\n";
		//Tracer::getInstance()->log($str, LOGLEV_ERROR);
		//print($this->getQuery()."\n</br></br>");
	}
	
	/**
	 * function returns created query
	 */
	public function getQuery()
	{
		// if preparedQuery exists return it, otherwise generate a new one
		if(! empty($this->preparedQuery) ) {
			$queryString = $this->preparedQuery;
		} else {
			$queryString = $this->getSqlCommand();
			if($this->getSqlCommand() == SQLCOMMAND::_INSERT) {
				$queryString .= ' INTO '.$this->getTable();
				$queryString .= ' ('.$this->getColumnsAsString().')';
				// use parameterized sql statement i.e. list here number of bound variables for each column to insert ?,?,? and so on 
				$queryString .= ' VALUES ('.implode( ',', array_fill(0,count($this->getColumns()),'?') ) .')';
			} else {
				switch($this->getSqlCommand()) {
					case SQLCOMMAND::_SELECT_DISTINCT:
					case SQLCOMMAND::_SELECT:
						$queryString .= ' '.$this->getColumnsAsString();
						if (isset($this->fromTableAlias)) {
							$queryString .= ' FROM '.$this->fromTable.' AS '.$this->fromTableAlias;	
						} else {
							$queryString .= ' FROM '.$this->getTable();	
						}
						$queryString .= ' '.$this->getJoinTablesAsString();	
						break;
					case SQLCOMMAND::_UPDATE:
						$queryString .= ' '.$this->getTable();					
						// use parametrized sql i.e. col1=?, col2=? and so on
						$tmpArray = $this->getColumns();
						array_walk($tmpArray, function(&$val) {
						$val .= '=?'; 
						});
						$queryString .= ' SET '.implode(',', $tmpArray);
						break;
					case SQLCOMMAND::_DELETE:
						$queryString .= ' FROM '.$this->getTable();						
						break;
					default:
						$str = get_class($this)."::".__FUNCTION__.": Unsupported SQL Command used: " . $this->getSqlCommand() . "\n";
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
				}
				$queryString .= ' '.$this->getConditionsAsString();
				$queryString .= ' '.$this->getGroupByAsString();
				$queryString .= ' '.$this->getSortingAsString();
				$queryString .= ' '.$this->resultLimit;
				
				// add unions
				// modify query if union query list is not empty
				$unionsString = $this->getUnionsAsString();
				if(!empty($unionsString))
				{
					// make sure the main query string is separated by () - otherwise the ORDER BY may colide with UNION etc.
					$queryString = '('.$queryString.') ';
					$queryString .= $unionsString;
				}
			}		
			// replace all remaining @@colname@@ with From tablename.colname
			$queryString = $this->addTableNameInPlaceholder($queryString, $this->getTable());			
		}	
		return $queryString;
	}
	
	/*
	 * function to execute built query in the DB
	 * returns 
	 */ 
	public function executeQuery($valuesToAdd=null)
	{
		if( ! empty($valuesToAdd) && is_array($valuesToAdd) ) {
			return DbConnectionFactory::getFactory()->getConnection()->execute($this->getQuery(), $valuesToAdd);
		} else {
			return DbConnectionFactory::getFactory()->getConnection()->execute($this->getQuery());
		}
	}

	/**
	 * function checks if table name is already registered either as from or join table
	 */
	public function existsTable($tableName)
	{
		if($this->getTable() == $tableName || array_key_exists($tableName, $this->joinTables) 
			|| (isset($this->fromTableAlias) && $this->fromTableAlias == $tableName)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * internal function to merge column and table names for select statement
	 * in case a select column is using SQL function e.g. FROM_UNIXTIME(testdate, \'%Y-%m-%d %H:%i:%s\') where testdate is a column name
	 * it has to be written as FROM_UNIXTIME(@@testdate@@, \'%Y-%m-%d %H:%i:%s\') (i.e. using @@ separators to make the proper replacement
	 * inputs:
	 * 	- $column 	  - column to select to be combined with $tableName
	 *  - $tableName  - reference to array containg split column name in format $match[1] = FROM_UNIXTIME(`; $match[2] = testdate; $match[3] = ')
	 */
	private function addTableName($column, $tableName)
	{
		$count = 0;
		$strToReturn = preg_replace('/@@(.*?)@@/', $tableName.'.${1}',$column,-1,$count);
		if(! $count)
		{
			$strToReturn = $tableName.'.'.$column;
		}
		return $strToReturn;
	}
	
	/**
	 * internal function to merge column and table names for all remaning occurences of '/@@(.*?)@@/' pattern in a query string
	 * inputs:
	 * 	- $str 	  - query string
	 *  - $tableName  - name of the table
	 * returns:
	 *  - modified string
	 */
	private function addTableNameInPlaceholder($str, $tableName)
	{
		$count = 0;
		$strToReturn = preg_replace('/@@(.*?)@@/', $tableName.'.${1}',$str,-1,$count);
		return $strToReturn;
	}
}

?>
