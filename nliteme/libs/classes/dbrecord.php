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
* DbRecords abstract class defines a list of DbRecord objects used to update database tables
* $dbRecordList is a list of DbRecord objects
* it is possible to add DbRecord object at anytime
*/
abstract class DbRecords extends JsonObject
{
	public function __construct($dbRecordList = null)
	{
		parent::__construct($dbRecordList);
	}
	
	/*
	 * function add suitable dbRecord to the list of records e.g. Build object to Builds etc.
	 * abstract since it is table specific
	 */
	/* Unfortunately does not work (php requires exact hinting with the same type)
	 * hence commented out
	 * abstract public function addToList(DbRecord $record); 
	 */
	
	/*
	 * function iterates through existing DbRecord and update rows them in DB
	 * TODO. make this more efficient, currently SQL query is prepared (compiled) for every DbRecord
	 * 		but in case all DbRecords use the same cilumn list and the same table it would be better to reuse precompiled query
	 */
	public function updateRecords($conditionsString = null)
	{
		$iterator = $this->getIterator();
		while( $iterator->valid() )
		{
			if( $iterator->current()->update($conditionsString) !== true )
			{
				//$str = get_class($this)."::".__FUNCTION__.": Update Failed! SQL query: " . $iterator->current()->getCurrentQuery()->getQuery() . "\n";
				//Tracer::getInstance()->log($str, LOGLEV_ERROR);
			}

			$iterator->next();
		}
	} 
	
	/*
	 * function iterates through existing DbRecord and inserts rows in DB
	 * TODO. make this more efficient, currently SQL query is prepared (compiled) for every DbRecord
	 * 		but in case all DbRecords use the same cilumn list and the same table it would be better to reuse precompiled query
	 */
	public function insertRecords()
	{
		$iterator = $this->getIterator();
		while( $iterator->valid() )
		{
			if( $iterator->current()->insert() !== true )
			{
				//$str = get_class($this)."::".__FUNCTION__.": Update Failed! SQL query: " . $iterator->current()->getCurrentQuery()->getQuery() . "\n";
				//Tracer::getInstance()->log($str, LOGLEV_ERROR);
			}

			$iterator->next();
		}	
	}
	
	/*
	 * function iterates through existing DbRecord and delete correspoding rows in DB
	 */
	public function deleteRecords($conditionsString = null)
	{
		$iterator = $this->getIterator();
		while( $iterator->valid() )
		{
			if( $iterator->current()->delete($conditionsString) !== true )
			{
				//$str = get_class($this)."::".__FUNCTION__.": Update Failed! SQL query: " . $iterator->current()->getCurrentQuery()->getQuery() . "\n";
				//Tracer::getInstance()->log($str, LOGLEV_ERROR);
			}

			$iterator->next();
		}	
	} 
	
	/* function to encode the DbRecords to json string
	 * 
	 */
	public function encode($objToEncode = null)
	{
		if( empty($objToEncode) ) {
			$objToEncode = array();
			$iterator = $this->getIterator();
			while( $iterator->valid() )
			{
				if( $iterator->current()->getDbColumnList() !== null )
				{
					array_push($objToEncode, $iterator->current()->getDbColumnList());
				}
				$iterator->next();
			}
		}
		return JsonObject::encode($objToEncode);
	}  
	
}

/*
* DbRecord abstract class defines database record object
* Used to insert/update/delete a row (defined as dbColumnList) in a dbTableName
* 
* Note. If dbColumnList does not contain valid table columns or dbTableName does not exist, SQL server will return an error
*/

abstract class DbRecord extends JsonObject
{
	// private variables (to access them use $this->setParam('dbTableName'), $this->getParam('dbTableName'))
	//private string $dbTableName;
	//private DbColumnList $dbColumnList;
	
	public function __construct($tableName, $keyColumn, $dbColumnList = null)
	{
		parent::__construct($dbColumnList);
		$this->setParam('dbTableName', $tableName);
		$this->setKeyColumn($keyColumn);
		//$this->setCurrentQuery('');
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($dbColumnList)
	{
		// parent constructor makes sure the argument is not empty
		// and is either DbColumnList object or array from which the object will be created
		if( $dbColumnList instanceof DbColumnList ) {
			$this->setDbColumnList($dbColumnList);
		} else if( is_array($dbColumnList) ) {
			$this->setDbColumnList(new DbColumnList($dbColumnList));
			//print_r($this->getDbColumnList());
        }
	}
	
	/*
	 * function has to be defined in child class, returns the list of allowed columns i.e. present in DB table
	 * can also return empty list
	 */ 
	abstract public function getAllowedColumns();
	
	/*
	 * function has to be defined in child class, returns the list of ignored columns i.e. columns that shall not be 
	 * added to this DB table.
	 * Function can also return empty list
	 */ 
	abstract public function getIgnoredColumns();

	/*
	 * function validates if the record exists in DB table
	 * abstract since it may be table specific
	 */
	abstract public function is_inDb();
	
	/*
	 * function returns an Id of the record e.g. name of build, name of test case, name of test result etc.
	 * abstract since table specific
	 */ 
	abstract public function getId();

	/*
	 * function returns a DbColumn obj of the id column
	 * abstract since table specific
	 */ 
	abstract public function getIdColumn();

	/*
	 * function returns a id column name
	 * abstract since table specific
	 */ 
	abstract public function getIdColumnName();

	/*
	 * function returns a name of the record e.g. name of build, name of test case, name of test result etc.
	 * abstract since table specific
	 */ 
	abstract public function getName();
	
	/*
	 * function returns a DbColumn obj of the name column
	 * abstract since table specific
	 */ 
	abstract public function getNameColumn();
	
	/*
	 * function returns a name column name
	 * abstract since table specific
	 */ 
	abstract public function getNameColumnName();
	
	/*
	 * function returns a hash made of name of the record e.g. hash of build, hash of test case, hash of test result etc.
	 * abstract since table specific
	 */ 
	abstract public function getHash();

	/*
	 * function retrieves table column names from db (just like SQL statement: desc table_name)
	 * returns an array with column names defined in database table
	 */
	protected function getTableColumnNames()
	{
		return( DbConnectionFactory::getFactory()->getConnection()->MetaColumnNames(TABLE_PREFIX.$this->getTableName()) );
	}
	
	/*
	 *  function used to set query string for the current SQL command
	 */ 
	public function setCurrentQuery(DbQueryBuilder $query)
	{
		$this->setParam('sqlQuery', $query);
	}

	/*
	 *  function returns currently set SQL query command
	 */ 	
	public function getCurrentQuery()
	{
		return $this->getParam('sqlQuery');
	}
	
	/*
	 * function sets the column name used as a key for update and delete queries
	 * (see update and delete functions) 
	 */ 
	public function setKeyColumn($keyColumn)
	{
		$this->setParam('keyColumn', $keyColumn);	
	}
	
	/*
	 * function return a name of a key columns used fo update and delete queries
	 */ 
	public function getKeyColumn()
	{
		return $this->getParam('keyColumn');	
	}
	
	/*
	 * function updates existing record in a relevant DB table
	 * return bool TRUE on success and list of errors on error
	 */
	public function update(DbQueryBuilder &$updateQuery = null)
	{
		// check if columns exist in the table
		$isValidated = $this->validate();
		if($isValidated !== true)
		{
			return $isValidated;
		}
		
		// check if keyColumn is defined in DbColumnList
		if(    $this->getDbColumnList()->getDbColumnByRealName($this->getKeyColumn()) === null 
			|| $this->getDbColumnList()->getDbColumnByRealName($this->getKeyColumn())->hasColumnValue() === false )
		{
			$str = Text::_('UPDATE_OF').get_class($this).' '.Text::_('FAILED').' '.Text::_('KEY_COLUMN_VALUE').' '.Text::_('NOT_DEFINED').': '.$this->getKeyColumn();
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return $str;
		}
		
		// get columns to modify
		$columnNames = $this->getModifiableColumnNames();
		if( empty($columnNames) || ! is_array($columnNames) ) {
			$str = Text::_('UPDATE_OF').get_class($this).' '.$this->getName().' '.Text::_('FAILED').' '.Text::_('MODIFIABLE_COLUMNS').' '.Text::_('NOT_DEFINED');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return $str;
		} else {	
			if( $updateQuery === null || $updateQuery->getSqlCommand() !== SQLCOMMAND::_UPDATE)
			{
				$updateQuery = new DbQueryBuilder($this->getTableName(), $columnNames, SQLCOMMAND::_UPDATE);
				$updateQuery->addCondition($this->getKeyColumn().'=?');
				$updateQuery->prepareQuery();
			}
			// sets current query object
			$this->setCurrentQuery($updateQuery);
	
			// foreach column Name get the corresponding value
			$values = array();
			foreach($columnNames as $column)
			{
				if($this->getDbColumnList()->getDbColumnByRealName($column)->hasColumnValue())
				{
					// compress description
					if($column == 'description') {
						array_push($values, gzcompress($this->getDbColumnList()->getDbColumnByRealName($column)->getColumnValue()));
					} else {
						array_push($values, $this->getDbColumnList()->getDbColumnByRealName($column)->getColumnValue());
					}
				}
			}
			
			// set query and update if number of columns equals number of 
			if( count($columnNames) === count($values) ) {
				// add keyColumn condition value to array (as it is the last '?' in the insert query)
				array_push($values, $this->getDbColumnList()->getDbColumnByRealName($this->getKeyColumn())->getColumnValue());
				
				//print_r($updateQuery->getQuery());
				//print_r($values);
				$result = $updateQuery->executeQuery($values);
				if(! $result )
				{
					$str = Text::_('UPDATE_OF').get_class($this).' '.Text::_('FAILED').' '.Text::_('SQL_ERROR');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return $str;
				}
			} else {
				$str = Text::_('UPDATE_OF').get_class($this).' '.$this->getName().' '.Text::_('FAILED').' count($columnNames) != count($values)';
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return $str;
			}
			
			return true;
		}		
	} 
	
	/*
	 * function inserts a new record in a relevant DB table
	 * the optional argument $insertQuery can be used to speed up bulk inserts (by reusing already precompiled queries) 
	 * return bool TRUE on success and list of errors on error
	 */
	public function insert(DbQueryBuilder &$insertQuery = null)
	{
		// check if columns exist in the table
		$isValidated = $this->validate();
		if($isValidated !== true)
		{
			return $isValidated;
		}

		// get a list of modifiable columnnames and continue if any available
		$columnNames = $this->getModifiableColumnNames();
		if( empty($columnNames) || ! is_array($columnNames) ) {
			$str = Text::_('INSERT_OF').get_class($this).' '.$this->getName().' '.Text::_('FAILED').' '.Text::_('MODIFIABLE_COLUMNS').' '.Text::_('NOT_DEFINED');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return $str;
		} else {
			// prepare query if not given as argument
			if( $insertQuery === null || $insertQuery->getSqlCommand() !== SQLCOMMAND::_INSERT)
			{
				$insertQuery = new DbQueryBuilder($this->getTableName(), $columnNames, SQLCOMMAND::_INSERT);
				$insertQuery->prepareQuery();
			}
			// sets current query object
			$this->setCurrentQuery($insertQuery);
			// foreach column Name get the corresponding value
			$values = array();
			foreach($columnNames as $column)
			{
				if($this->getDbColumnList()->getDbColumnByRealName($column)->hasColumnValue())
				{
					// compress description
					if($column == 'description') {
						array_push($values, gzcompress($this->getDbColumnList()->getDbColumnByRealName($column)->getColumnValue()));
					} else {
						array_push($values, $this->getDbColumnList()->getDbColumnByRealName($column)->getColumnValue());
					}
				}
			}
	
			// insert if number of columns equal number of 
			if( count($columnNames) === count($values) ) {
				//print_r($insertQuery->getQuery());
				//print_r($values);
				$result = $insertQuery->executeQuery($values);
			} else {
				$str = Text::_('INSERT_OF').get_class($this).' '.$this->getName().' '.Text::_('FAILED').' count($columnNames) != count($values)';
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return $str;
			}
			
			// if false was returned it means SQL failed
			if(! $result )
			{
				$str = Text::_('INSERT_OF').get_class($this).' '.$this->getName().' '.Text::_('FAILED').' '.Text::_('SQL_ERROR');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return $str;
			}
			
			return true;
		}
	} 

	/*
	 * function delete a row in a relevant DB table
	 * return bool TRUE on success and list of errors on error
	 * Note. If called without $conditionsString all rows will be deleted
	 */
	public function delete(DbQueryBuilder &$deleteQuery = null)
	{
		$columnNames = $this->getDbColumnList()->getDbColumnsRealNames();
		// check if keyColumn is defined in DbColumnList
		if(    $this->getDbColumnList()->getDbColumnByRealName($this->getKeyColumn()) === null 
			|| $this->getDbColumnList()->getDbColumnByRealName($this->getKeyColumn())->hasColumnValue() === false )
		{
			$str = Text::_('DELETE_OF').get_class($this).' '.Text::_('FAILED').' '.Text::_('KEY_COLUMN_VALUE').' '.Text::_('NOT_DEFINED').': '.$this->getKeyColumn();
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return $str;
		}
		
		
		if( $deleteQuery === null || $deleteQuery->getSqlCommand() !== SQLCOMMAND::_DELETE)
		{
			$deleteQuery = new DbQueryBuilder($this->getTableName(), null, SQLCOMMAND::_DELETE);
			$deleteQuery->addCondition($this->getKeyColumn().'=?');
			$deleteQuery->prepareQuery();
		}
		// sets current query object
		$this->setCurrentQuery($deleteQuery);
		$result = $deleteQuery->executeQuery(array($this->getDbColumnList()->getDbColumnByRealName($this->getKeyColumn())->getColumnValue()));
		// if false was returned it means SQL failed
		if(! $result )
		{
			$id = $this->getDbColumnList()->getDbColumnByRealName($this->getKeyColumn())->getColumnValue();
			$str = Text::_('DELETE_OF').get_class($this).' id='.$id.' '.Text::_('FAILED').' '.Text::_('SQL_ERROR');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return $str;
		}
		return true;
	} 

	/*
	 * function returns a name of DB table
	 */
	public function getTableName()
	{
		return $this->getParam('dbTableName');
	}

	/*
	 * function returns DbColumnList object
	 * if does not exist null is returned
	 */
	public function getDbColumnList()
	{
		return $this->getParam('dbColumnList');
	} 
	
	/*
	 * function sets dbColumnList
	 * an argument has to be a reference to DbColumnList object
	 */
	public function setDbColumnList(DbColumnList $dbColumnList)
	{
		$this->setParam('dbColumnList', $dbColumnList);
	}
	
	/*
	 * function adds a table column (in form of DbColumn object) to the record
	 * Note. It creates empty DbColumnList object if not present yet
	 */
	public function addDbColumn(DbColumn $dbColumn)
	{
		$colList = $this->getDbColumnList();
		if( empty( $colList ) )
		{
			$this->setDbColumnList( new DbColumnList() );
		}
		$this->getDbColumnList()->addColumn($dbColumn);
	}
	
	/*
	 * function returns a table column (in form of DbColumn object) for a given real column name
	 * or null if not found
	 */
	public function getDbColumn($columnRealName)
	{
		$columnToReturn = null;
		$colList = $this->getDbColumnList();
		if(! empty($colList) )
		{
			$columnToReturn = $this->getDbColumnList()->getDbColumnByRealName($columnRealName);
		}
		return $columnToReturn;
	}
	
	/*
	 * function sets the value to $fieldValue of the record's field identified by $columnRealName
	 * if corresponding DbColumn does not exist it will be created
	 */ 
	public function setDbColumnValue($columnRealName, $fieldValue)
	{
		$col = $this->getDbColumn($columnRealName);
		if( empty($dbColumn) ) {
			$tabentry = array();
			$tabentry['realname'] = $columnRealName;
			$tabentry['value'] = $fieldValue;
			$this->addDbColumn( new DbColumn($tabentry) );
		} else {
			$dbColumn->setColumnValue($fieldValue);
		}
	}
	
	/*
	 * function get the value of the record's field identified by $columnRealName
	 * if corresponding DbColumn does not exist it will be created
	 */ 	
	public function getDbColumnValue($columnRealName)
	{
		$col = $this->getDbColumn($columnRealName);
		if(! empty($col) ) {
			return $col->getColumnValue();
		} else {
			return null;
		}
	}
	
	/*
	 * function returns a list of table column names that can modified by Insert/Update SQL queries
	 * or empty if no DB columns are defined 
	 */ 	
	public function getModifiableColumnNames()
	{
		$definedColumns = $this->getDbColumnList()->getDbColumnsRealNames();
		if( is_array($definedColumns) && ! empty($definedColumns) )
		{
			return array_diff($definedColumns, $this->getIgnoredColumns());
		} else {
			return null;
		}
	}

	/*
	 * function validates if columns exist in table etc.
	 * optionally an array of real column names can be given as an argument
	 * if omited, the column names are retrieved from data base
	 * 
	 * returns a list of columns which does not exist in the table, or empty list if evertyhing OK
	 */
	protected function validateColumnNames($columnNames = null)
	{
		// list of column names that does not exist in the database table
		$wrongColumns = array();
		// get table column names from a database
		if( empty($columnNames) )
		{
			$columnNames = $this->getTableColumnNames();
			//print_r($columnNames) );
		}
		
		// validates if colums exist in the table
		foreach ($this->getDbColumnList()->getDbColumnsRealNames() as $columnRealNameToCheck)
		{
			if(! in_array($columnRealNameToCheck, $columnNames) )
			{
				array_push($wrongColumns, $columnRealNameToCheck);
			}
		}
		
		return $wrongColumns;
	}
	
	/*
	 * function used to validate the record
	 * returns true if OK, otherwise list of errors
	 */ 
	public function validate()
	{
		$validationResult = array();
		
		// check column correctness
		$incorrectColumns = $this->validateColumnNames($this->getAllowedColumns());
		if(! empty($incorrectColumns) )
		{
			$str = Text::_('INCORRECT_FIELDS').implode(', ', $incorrectColumns);
			Tracer::getInstance()->log($str, LOGLEV_WARNING);
			array_push($validationResult, $str);
		}
		
		// other checks here
		
		// returns suitable verdict
		if(! empty($validationResult) ) {
			return $validationResult;
		} else {
			return true;
		}
	}
	
	/*
	 * function returns json string with column list
	 * Note. url, table name are not included, just dbColumnList
	 */
	public function encode($objToEncode = null)
	{
		return $this->getDbColumnList()->encode();
	}
	
	/*
	 * function initializes the DbColumnList with null values according to the $this->getAllowedColumns() list
	 */ 
	public function initializeColumns()
	{	
		$cols = $this->getAllowedColumns();
		if( !empty($cols))
		{
			foreach ($this->getAllowedColumns() AS $name) 
			{
				$this->setDbColumnValue($name, null);
			}
		}
	}
}



 
 ?>
