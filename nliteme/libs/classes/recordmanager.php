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
 * RecordManager class for managing the DB record content i.e. inserting, updating and removing builds, test cases and test results
 * The idea is to have one common class which can be used by admin page and other uploaders etc.
 */

abstract class RecordManager
{
   	protected $dbRecord;
   	protected $useHashColumn;
   	
   	/* 
	 * constructor
	 */
	public function __construct(DbRecord& $dbRecord, $useHashColumn = false)
    {
		$this->dbRecord = $dbRecord;
		$this->useHashColumn = $useHashColumn;
	}
	
	/* 
	 * destructor
	 */
	public function __destruct()
    {
	}
	
	/*
	 * function returns an unique name if record with index exists in DB, otherwise false 
	 * uses PrecompiledQueries storage for optimization
	 */		  
	public function isInDbByInx()
	{
		$table = $this->dbRecord->getTableName();
		$query = PrecompiledQueries::getInstance()->getPrecompiledQuery($table.'_ByInx');
		if(empty($query))
		{
			$nameCol = $this->dbRecord->getNameColumnName();
			$idCol = $this->dbRecord->getIdColumnName();
			
			$query = new DbQueryBuilder($table, array($nameCol));
			$query->addCondition($idCol.'=?');
			$query->prepareQuery();
			PrecompiledQueries::getInstance()->addPrecompiledQuery($table.'_ByInx', $query);
		}

		$rs = $query->executeQuery(array($this->dbRecord->getId()));
		if(! $rs->EOF ) {
			$row = $rs->FetchRow();
			// since row is an array with one element (can associative) get it's 1st element
			return current($row);
		} else {
			return false;
		}
	}

	/*
	 * function returns an unique index if record with a $this->dbRecord name/hash exists in DB , otherwise false
	 * uses PrecompiledQueries storage for optimization
	 */
	public function isInDb()
	{
		$table = $this->dbRecord->getTableName();
		$query = PrecompiledQueries::getInstance()->getPrecompiledQuery($table.'_ByName');
		if(empty($query))
		{
			$nameCol = ($this->useHashColumn === true) ? 'hash' : $this->dbRecord->getNameColumnName();
			$idCol = $this->dbRecord->getIdColumnName();
			
			$query = new DbQueryBuilder($table, array($idCol));
			$query->addCondition($nameCol.'=?');
			$query->prepareQuery();
			PrecompiledQueries::getInstance()->addPrecompiledQuery($table.'_ByName', $query);
		}

		$value = ($this->useHashColumn === true) ? $this->dbRecord->getHash() : $this->dbRecord->getName();
		$rs = $query->executeQuery(array($value));
		if(! $rs->EOF ) {
			$row = $rs->FetchRow();
			// since row is an array with one element (can associative) get it's 1st element
			return intval(current($row));
		} else {
			//$str = get_class()."::".__FUNCTION__.": NAME/HASH: ".$value.Text::_('NOT_IN_DB').": ".$table;
			//Tracer::getInstance()->log($str, LOGLEV_DEBUG);
			return false;
		}
	}
	
	/*
	 * function handles (prepares/check if exists/inserts/updates/delete) a dbRecord
	 * arguments: DbRecord object reference, 
	 *
	 * returns true on success, otherwsie a relevant error notification
	 * (use === on boolean opertor for check)
	 */ 
    public function handleRecord()
    {
		$finalReturn = true;
		$classType = get_class($this->dbRecord);
		// check if the test result has a defined Id
		if(! Utils::emptystr($this->dbRecord->getId())) {
			// if ID is defined, check if the record with a given ID exists in DB
			if( $this->isInDbByInx() === false ) {
				$str = get_class()."::".__FUNCTION__.": ".$classType." id".Text::_('HAS_WRONG_VALUE').": ".$this->dbRecord->getId();
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return $classType." id".Text::_('HAS_WRONG_VALUE').": ".$this->dbRecord->getId();
			} else {
				// if the name is set make sure it does not collide with existing names in DB
				// Remember: $dbRecord->$getName() has to be unique
				$checkName = $this->isInDb();
				if($checkName !== false && intval($checkName) !== intval($this->dbRecord->getId()))
				{
					$name = $this->dbRecord->getNameColumn()->getColumnRealName();
					$str = get_class()."::".__FUNCTION__.": ".$classType." ".Text::_(strtoupper($name))." ".Text::_('IS_NOT_UNIQUE');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return $classType." ".Text::_(strtoupper($name))." ".Text::_('IS_NOT_UNIQUE');			
				}
				// update existing record using id - only allowed if id is present
				// be carefull there aren't any checks done here, so $dbRecord has to contain valid values
				// or you may spoil the DB 
				// update dbRecord in DB if allowed (use precompiled query if exists)
				$preparedQuery = $this->dbRecord->getCurrentQuery();
				$finalReturn = $this->dbRecord->update($preparedQuery);
			}
		} else {
			// add build to DB if allowed (use precompiled query if exists)
			$preparedQuery = $this->dbRecord->getCurrentQuery();
			// before the existance in the DB first make sure the name is defined
			// name must be defined here
			if( Utils::emptystr($this->dbRecord->getName()) )
			{
				$str = get_class()."::".__FUNCTION__.": ".$classType.' '.Text::_('NAME').Text::_('NOT_DEFINED');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return $classType.' '.Text::_('NAME').Text::_('NOT_DEFINED');
			}
			// check using name/hash if the dbRecord exists in DB
			$checkIndex = $this->isInDb();
			// if dbRecord exists, first id corresponding to the given name has to be set (as it is a key for the update)
			if( $checkIndex !== false )  {
				$this->dbRecord->setId($checkIndex);
			}			
			// now perform the record preparation, continue only if successfull
			$ret = $this->prepareRecord();
			if( $ret !== true )
			{
				return $ret;
			}
			// decide if update or insert needed
			if( $checkIndex === false )  {
				// if id is not found insert a new record
				$finalReturn = $this->dbRecord->insert($preparedQuery);
			} else {
				// since record exists, try to update it			
				$finalReturn = $this->dbRecord->update($preparedQuery);
			}
		}
		
		// if main record update was succesfull
		// let's update description table for this record if applicable
		if($finalReturn === true)
		{
			// make sure the $this->dbRecord has correct id set
			if( Utils::emptystr($this->dbRecord->getId()) )
			{
				$checkIndex = $this->isInDb();
				if($checkIndex !== false)
				{
					$this->dbRecord->setId($checkIndex);
				}
			}

			$finalReturn = $this->handleDescription();			
		}

		return $finalReturn;
	} 
	
	/*
	 * function prepare a dbRecord object for updating database. It also does some preliminary database updates 
	 * (e.g. creates a build increment if a Build record is being added)
	 * It is record/table type specific hence must be implemented in the child classes (hence abstract)
	 */ 
	abstract protected function prepareRecordSpecific();
	
    /*
     * wrapper function that call proper prepare function depending on the dbRecord object type
     * return a handler return code (true on success, erros string on failure)
     */
    public function prepareRecord()
    {	
		$result = true;
		$classType = get_class($this->dbRecord);
		
		/* 
		 * common preparation
		 */ 
		// name must be defined
		if( Utils::emptystr($this->dbRecord->getName()) )
		{
			$str = get_class()."::".__FUNCTION__.": ".$classType.' '.Text::_('NAME').Text::_('NOT_DEFINED');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return $classType.' '.Text::_('NAME').Text::_('NOT_DEFINED');
		}
		// if creation date does not exists and record does not yet exists in the DB, set current date
		if( method_exists($this->dbRecord,'getCreateDate') && Utils::emptystr($this->dbRecord->getCreateDate()) && Utils::emptystr($this->dbRecord->getId()))
		{
			$this->dbRecord->setCreateDate(Utils::getCurTime());
		}
		
		// in case of the new, not existing records:
		// columns that shall have predefined value are mandatory, therefore
		// check for predefined values, make sure the predefined index is returned back
		// e.g. this will be a case for extracolumns, test verdict etc.
		$ret = $this->updatePredefinedColumns($this->dbRecord);
		if( $ret !== true )
		{
			return $ret;
		}
		
		/* 
		 * record type specific preparation
		 */
		$result = $this->prepareRecordSpecific();
		
		return $result;
	}
	
	/*
	 * generic handling of description fields
	 * It is assumed that the description is handled via specific Description object
	 * e.g. for TestResult there is a TestResultDescription etc.
	 * Therefore this function checks if the class type with a name get_class($this->dbRecord).'Description' exists
	 * and if so creates a DescriptionRecordManager object with new get_class($this->dbRecord).'Description' object as argument
	 */
	public function handleDescription()
	{
		$dbRecordClassType = get_class($this->dbRecord);
		$descriptionClassName = $dbRecordClassType.'Description';
		if( class_exists($descriptionClassName)
		 && ! Utils::emptystr($this->dbRecord->getId())
		 && $this->dbRecord->getDbColumnList()->getDbColumnByRealName('description') !== null 
		 )
		{
			$descClassName = new $descriptionClassName();
			$descRecordManager = new DescriptionRecordManager($descClassName);
			$descRecordManager->dbRecord->setId($this->dbRecord->getId());
			$descRecordManager->dbRecord->setDescription($this->dbRecord->getDescription());
			$ret = $descRecordManager->handleRecord();
			if( $ret !== true )
			{
				return $ret;
			}
		}

		return true;
	}
	
	/*
	 * generic function for Predefined Columns in the DbRecord object
	 * checks and updates the predefined value id in columns marked as predefined
	 * argument: DbRecord Object reference
	 * returns:
	 * - TRUE on success
	 * - error string on failure
	 * (use === on boolean opertor for check)
	 */ 
	public function updatePredefinedColumns(DbRecord& $dbRecordObj)
	{
		// columns that shall have predefined value a mandatory, therefore
		// check for predefined values, make sure the predefined index is returned back
		// e.g. this will be a case for build type, test line, test verdict etc.
		$allowedColums = $dbRecordObj->getAllowedColumns();
		foreach ($allowedColums AS $columnrealname) 
		{
			if( $this->isPredefinedValueAllowedInConfigColumn($columnrealname) === true)
			{
				$predefValue = $dbRecordObj->getDbColumnValue($columnrealname);
				if( $predefValue === null ) {
					// if record does not exist yet, issue an error 
					// (because for the newly created records the predefined values are mandatory)
					// otherwise continue with the next allowed column
					if ( Utils::emptystr($this->dbRecord->getId()) ) {
						$str = get_class()."::".__FUNCTION__.": ".$columnrealname.Text::_('NOT_DEFINED');
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
						return ": ".Text::_(strtoupper($columnrealname)).Text::_('NOT_DEFINED');
					} else {
						continue;
					}
				} else {
					// if is integer assume it is an index to the predefined value in config table so check if it exists
					if( is_numeric($predefValue) ) {
						$val = $this->isPredefinedIndexInConfigColumn($columnrealname, $predefValue);
						if($val === false) 
						{
							$str = get_class()."::".__FUNCTION__.": ".$columnrealname.Text::_('HAS_WRONG_VALUE').": ".$predefValue;
							Tracer::getInstance()->log($str, LOGLEV_ERROR);
							return ": ".Text::_(strtoupper($columnrealname)).Text::_('HAS_WRONG_VALUE').": ".$predefValue;
						}
					} else {
						$inx = $this->addPredefinedValueInConfigColumn($columnrealname, $predefValue);
						if($inx === false) {
							$str = get_class()."::".__FUNCTION__.": ".$columnrealname.Text::_('HAS_WRONG_VALUE').": ".$predefValue;
							Tracer::getInstance()->log($str, LOGLEV_ERROR);
							return ": ".Text::_(strtoupper($columnrealname)).Text::_('HAS_WRONG_VALUE').": ".$predefValue;
						} else {
							$dbRecordObj->setDbColumnValue($columnrealname, $inx);
						}
					}
				}
			}
        }
        return true;	
	}

	/*
	 * function returns TRUE if ConfigColumn is allowed to have a predefined values
	 * otherwise returns FALSE
	 */
	public function isPredefinedValueAllowedInConfigColumn($columnRealName)
	{
		$column = Config::getInstance()->getColumnConfig()->getDbColumnByRealName($columnRealName);
		if(! empty($column) ) {
			
			return ($column->is_predefined() && $column->is_enabled());
		} else {
			return false;
		}
	}
	
	/*
	 * function returns a value for a predefined index if is allowed to exist and exists and  in the ColumnConfiguration in preferences table
	 * otherwise returns FALSE (use === on boolean opertor for check)
	 */
	public function isPredefinedIndexInConfigColumn($columnRealName, $indexToCheck)
	{
		$column = Config::getInstance()->getColumnConfig()->getDbColumnByRealName($columnRealName);
		if(! empty($column) && $column->is_predefined() === true && $column->is_enabled() === true) {
			return $column->getColumnPredefinedValuebyIndex($indexToCheck);
		} else {
			return false;
		}
	}
	
	/*
	 * function returns an index for a predefined value if is allowed to exist and exists and  in the ColumnConfiguration in preferences table
	 * otherwise returns FALSE (use === on boolean opertor for check)
	 */
	public function isPredefinedValueInConfigColumn($columnRealName, $valueToCheck)
	{
		$column = Config::getInstance()->getColumnConfig()->getDbColumnByRealName($columnRealName);
		if(! empty($column) && $column->is_predefined() === true && $column->is_enabled() === true) {
			return $column->getIndexOfPredefinedValue($valueToCheck);
		} else {
			return false;
		}
	}
	
	/*
	 * function adds a predefined value for a given column returns an index for a predefined value if is allowed to exist and exists
	 * return an index of a predefined value on success, otherwise returns FALSE (use === on boolean opertor for check)
	 */
	public function addPredefinedValueInConfigColumn($columnRealName, $valueToCheck)
	{
		$column = Config::getInstance()->getColumnConfig()->getDbColumnByRealName($columnRealName);
		if(! empty($column) && $column->is_predefined() === true && $column->is_enabled() === true) {
			$indexOfValue = $column->getIndexOfPredefinedValue($valueToCheck);
			if($indexOfValue === false)
			{
				// get array of current values
				$curValues = $column->getColumnPredefinedValues();
				if(! is_array($curValues) )
				{
					$curValues = array();
				}
				// append the new one
				array_push($curValues, $valueToCheck);
				// update DbColumn Object
				$column->setColumnPredefinedValues($curValues);
				// update the ColumnConfig list in Config object
				$ret = Config::getInstance()->updateColumnInColumnConfig($column);
				// handle somehow the errors from updateColumnInColumnConfig()
				$indexOfValue = $column->getIndexOfPredefinedValue($valueToCheck);
			}
			return $indexOfValue;
		} else {
			return false;
		}
	} 
	
	/*
	 * function modifies an existing predefined value for a given column 
	 */
	public function modifyPredefinedValueInConfigColumn($columnRealName, $indexOfValue, $newValue)
	{
		if( $this->isPredefinedIndexInConfigColumn($columnRealName, $indexOfValue) !== false ) 
		{
		
			// modify the value indicated by a given index in a ConfigColumn
			$column = Config::getInstance()->getColumnConfig()->getDbColumnByRealName($columnRealName);
			$curValues = $column->getColumnPredefinedValues();
			$curValues[$indexOfValue] = $newValue;
			$column->setColumnPredefinedValues($curValues);
			Config::getInstance()->updateColumnInColumnConfig($column);
		} 
	} 
	
	/*
	 * function deletes a predefined value for a given column.
	 * if the index to the value exists in the corresponding column in any of the tables (testcases, testresults, builds), 
	 * the correspoding tables entries are deleted too.
	 */
	public function deletePredefinedValueInConfigColumn($columnRealName, $indexOfValue)
	{
		if( $this->isPredefinedIndexInConfigColumn($columnRealName, $indexOfValue) !== false )
		{
			//check all tables if the index is present and delete the corresponding entries
			$this->cleanupTables($columnRealName, $indexOfValue);
			// delete the index and it's value from the ConfigColumn
			$column = Config::getInstance()->getColumnConfig()->getDbColumnByRealName($columnRealName);
			$curValues = $column->getColumnPredefinedValues();
			unset($curValues[$indexOfValue]);
			$column->setColumnPredefinedValues($curValues);
			Config::getInstance()->updateColumnInColumnConfig($column);
		}
	} 
	/*
	 * 
	 */
	public function cleanupTables($columnRealName, $indexOfValue)
	{
		$dbObjs = array(new Build(), new BuildIncrement(), new TestCase(), new TestSuite(), new TestLine(), new TestResult(), new Feature());
		foreach ($dbObjs AS $dbObj)
		{
			$allowedColumns = $dbObj->getAllowedColumns()->getDbColumnsRealNames();
			if( in_array($columnRealName, $allowedColumns) )
			{
				// Here we cannot use the $dbObj->delete because it does the deletion based on recordKeyColumn which is not set here
				// therefore we have to do this from scratch 
				$deleteQuery = new DbQueryBuilder($dbObj->getTableName(), null, SQLCOMMAND::_DELETE);
				$deleteQuery->addCondition($columnRealName.'='.$indexOfValue);
				$deleteQuery->prepareQuery();
				$deleteQuery->executeQuery();
			}
		}	
	} 
}

/*
 * Here are the classes using RecordManager
 */
 
class BuildIncrementRecordManager extends RecordManager
{
	public function __construct(BuildIncrement& $dbRecord)
	{
		parent::__construct($dbRecord);
	}
	
	/*
     * function prepares record specific part of BuildIncrement object 
     * return true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     */ 
    protected function prepareRecordSpecific()
    {
		return true;
	}
}

class BuildRecordManager extends RecordManager
{
	public function __construct(Build& $dbRecord)
	{
		parent::__construct($dbRecord);
	}
	
    /*
     * function prepares a Build object 
     * return true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     */ 
    public function prepareRecordSpecific()
    {
		/*
		 * Handle increment properly
		 */ 
		$ret = $this->checkAndPrepareBuildIncrement();
		if($ret !== true)
		{
			return $ret;
		}
		return true;
	}
	
	/*
	 * function checks if build increment record exists in DB and if not creates it with it's dependencies
	 * 
	 */
	private function checkAndPrepareBuildIncrement()
	{  
		// if incid && increment does not exist and this is a new DB record return error
		if( $this->dbRecord->getIncId() === null && $this->dbRecord->getIncrement() === null ) {
			// if this is a new DB record to be added
			if ( Utils::emptystr($this->dbRecord->getId()) ) {
				$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_BUILD_INCREMENT');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return Text::_('NO_BUILD_INCREMENT');
			}
		} else {
			// create BuildIncrementRecordManager object to perform BuildIncrement related checks/updates
			$buildInc = new BuildIncrement();
			$incrementRecordManager = new BuildIncrementRecordManager($buildInc);
			if( $this->dbRecord->getIncId() === null && $this->dbRecord->getIncrement() !== null  ) {
			// build increment is specified by name	
				// try to check for the build in DB and get the corresponding buildid		
				$incrementRecordManager->dbRecord->setName($this->dbRecord->getIncrement());
				$incid = $incrementRecordManager->isInDb();
				// if does not exist
				if( $incid === false)  
				{
					// if inteligent Manager disabled print error
					if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
					{
						$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD_INCREMENT');
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
						return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD_INCREMENT');
					} else {
						// set build testdate to the createdate of build
						$incrementRecordManager->dbRecord->setCreateDate($this->dbRecord->getCreateDate());
						// handle increment object
						$ret = $incrementRecordManager->handleRecord();
						if( $ret !== true )
						{
							return $ret;
						}
						// update incid in Build Obj
						$incid = $incrementRecordManager->isInDb();
					}
				}
				// update incid in Build Obj
				$this->dbRecord->setIncId($incid);
				$incrementRecordManager->dbRecord->setId($incid);
			} else {
			// build increment is specified by incid	
				// check if increment exists in DB
				$incrementRecordManager->dbRecord->setId($this->dbRecord->getIncId());
				if($incrementRecordManager->isInDbByInx() === false) {
					$str = get_class()."::".__FUNCTION__.": incid ".Text::_('HAS_WRONG_VALUE').": ".$this->dbRecord->getIncId();
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return "incid ".Text::_('HAS_WRONG_VALUE').": ".$this->dbRecord->getIncId();
				} 
			}
		}
		return true;	
	}
	/*
	 * function used to update testdate for a given buildId
	 */
	public function updateBuildTestDate($testDate)
	{
		if( is_numeric($this->dbRecord->getId()) && ! empty($testDate) ) {
			// prepare a query if it does not exists yet
			$query = PrecompiledQueries::getInstance()->getPrecompiledQuery('updateBuildTestDateByBuildId');
			if(empty($query))
			{
				$table = $this->dbRecord->getTableName();
				$idCol = $this->dbRecord->getIdColumn()->getColumnRealName();
				$query = new DbQueryBuilder($table, array('testdate'), SQLCOMMAND::_UPDATE);
				$query->addCondition($idCol."=?");
				$query->prepareQuery();
				PrecompiledQueries::getInstance()->addPrecompiledQuery('updateBuildTestDateByBuildId', $query);
			}

			$rs = $query->executeQuery(array($testDate, $this->dbRecord->getId()));
			if( $rs ) {
				return true;
			} else {
				$str = get_class()."::".__FUNCTION__.": ".Text::_('SQL_ERROR');
				$str = $str." (query:".$query->getQuery().")";
				Tracer::getInstance()->log($str, LOGLEV_DEBUG);
				return false;
			}			
		} else {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('WRONG_ARGUMENT');
			Tracer::getInstance()->log($str, LOGLEV_DEBUG);
			return false;
		}
	} 	
}

class TestSuiteRecordManager extends RecordManager
{
	public function __construct(TestSuite& $dbRecord)
	{
		parent::__construct($dbRecord);
	}
	
	/*
     * function prepares TestSuite record specific part of TestSuite object 
     * return true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     */ 
    protected function prepareRecordSpecific()
    {
		return true;
	}
}

class TestCaseRecordManager extends RecordManager
{
	public function __construct(TestCase& $dbRecord)
	{
		parent::__construct($dbRecord);
	}
    
	/*
	 * function checks if feature record exists in DB and if not creates it with it's dependencies
	 * 
	 */
	private function checkAndPrepareFeature()
	{  
		// if featureId or fname are not sepcified simply ignore (the DB takes care to set the default feature i.e. unknown
		if( $this->dbRecord->getFeatureId() === null && $this->dbRecord->getFeature() === null ) {
			//$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_FEATURE');
			//Tracer::getInstance()->log($str, LOGLEV_ERROR);
			//return Text::_('NO_FEATURE');
			return true;
		} else {
			// create FeatureRecordManager object to perform Feature related checks/updates
			$feature = new Feature();
			$featureRecordManager = new FeatureRecordManager($feature);
			if( $this->dbRecord->getFeatureId() === null && $this->dbRecord->getFeature() !== null  ) {
			// build increment is specified by name	
				// try to check for the feature in DB and get the corresponding fid		
				$featureRecordManager->dbRecord->setName($this->dbRecord->getFeature());
				$fid = $featureRecordManager->isInDb();
				// if does not exist
				if( $fid === false)  
				{
					// if inteligent Manager disabled print error
					if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
					{
						$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('FEATURE');
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
						return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('FEATURE');
					} else {
						// set build testdate to the createdate of build
						$featureRecordManager->dbRecord->setCreateDate($this->dbRecord->getCreateDate());
						// optionally add feature external hyperlink
						if( $this->dbRecord->getFeatureHlink() !== null) {
							$featureRecordManager->dbRecord->setHlink($this->dbRecord->getFeatureHlink());
						}
						// handle increment object
						$ret = $featureRecordManager->handleRecord();
						if( $ret !== true )
						{
							return $ret;
						}
						// update fid in TestCase Obj
						$fid = $featureRecordManager->isInDb();
					}
				}
				// update incid in Build Obj
				$this->dbRecord->setFeatureId($fid);
				$featureRecordManager->dbRecord->setId($fid);
			} else {
			// feature is specified by fid	
				// check if feature exists in DB
				$featureRecordManager->dbRecord->setId($this->dbRecord->getFeatureId());
				if($featureRecordManager->isInDbByInx() === false) {
					$str = get_class()."::".__FUNCTION__.": incid ".Text::_('HAS_WRONG_VALUE').": ".$this->dbRecord->getFeatureId();
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return "incid ".Text::_('HAS_WRONG_VALUE').": ".$this->dbRecord->getFeatureId();
				} 
			}
		}
		return true;	
	}
    
	/*
     * function prepares record TestCase specific part of TestSuite object 
     * return true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     */ 
    protected function prepareRecordSpecific()
    {
		/*
		 * Handle feature properly
		 */ 
		// if incid && increment does not exists return error
		$ret = $this->checkAndPrepareFeature();
		if($ret !== true)
		{
			return $ret;
		}
		
		return true;
	} 
}

class TestLineRecordManager extends RecordManager
{
	public function __construct(TestLine& $dbRecord)
	{
		parent::__construct($dbRecord);
	}
	
	/*
	 * function prepares record TestLine specific part of TestSuite object 
	 * return true on success, otherwise a relevant error notification
	 * (use === on boolean opertor for check)
	 */ 
	protected function prepareRecordSpecific()
	{
		return true;
	}  
}

class FeatureRecordManager extends RecordManager
{
	public function __construct(Feature& $dbRecord)
	{
		parent::__construct($dbRecord);
	}
	
	/*
	 * function prepares Feture record specific part of Feature object 
	 * return true on success, otherwise a relevant error notification
	 * (use === on boolean opertor for check)
	 */ 
	protected function prepareRecordSpecific()
	{
		$this->setExtLink();
		return true;
	}

	/*
	 * function set the hlink using the based ext hyper link and ext ID set in the name 
	 * if the external hyper link for features is set in the DB preferences
	 */ 
	private function setExtLink()
	{
		$extFeatureServer = Config::getInstance()->getPreference('feature_otherserver_hlink');
		if(empty($this->dbRecord->getHlink()) && !empty($this->dbRecord->getName()) && !empty($extFeatureServer))
		{
			$this->dbRecord->setHlink($extFeatureServer . $this->dbRecord->getName());
		} 
		return true;
	}
}

class TestResultRecordManager extends RecordManager
{
	public function __construct(TestResult& $dbRecord)
	{
		parent::__construct($dbRecord, true);
	}
	
    /*
     * function prepares a Test result object 
     * returns true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     * Note. the argument testResult object needs to have the following fields
     * - filepath - which uniquely identifies the entry (hash key is calculated from it)
     * - columns with predefined values i.e. marked with PredefinedInPrefs=yes - see column configuration
     * - build - either given by name (string) or by it's index in builds table (for internal use)
     * - testcase - either given by name (string) or by it's index in builds table (for internal use)
     */ 
	public function prepareRecordSpecific()
    {
		/*
		 * Handle increment properly
		 */ 
		// if incid && increment does not exists return error
		$ret = $this->checkAndPrepareBuildIncrement();
		if($ret !== true)
		{
			return $ret;
		}
				
		/*
		 * Handle build properly
		 */ 
		// check and update build id
		$ret = $this->checkAndPrepareBuild();
		if($ret !== true)
		{
			return $ret;
		}

		/*
		 * Handle test suite properly
		 */ 
		 // check and update ts id
		$ret = $this->checkAndPrepareTestSuite();
		if($ret !== true)
		{
			return $ret;
		}

		/*
		 * Handle test case properly
		 */ 
		 // check and update tc id
		$ret = $this->checkAndPrepareTestCase();
		if($ret !== true)
		{
			return $ret;
		}
		
		/*
		 * Handle tsid <=> tcid mapping
		 * i.e. if (mapping does not exists) {
		 * 			if intelligentManager flag  != 1 {issue error}
		 * 			else {add mapping}
		 * Note 1. this mapping is currently not actively used i.e. there are no views which use it
		 * Note 2. the mapping can only be created/updated if both tsid and tcid are specified in the dbrecord
		 * Note 3. the implication of the note 2 is that the following code is mandatory for the newly created record 
		 *         (because both tcid and tsid have to be present at this stage already)
		 *         and optional in case of record update (i.e. only performed if both ids are present)
		 */ 
		if(	$this->dbRecord->getTsId() !== null && $this->dbRecord->getTcId() !== null 
			&& PrecompiledQueries::getInstance()->isTsTcMap($this->dbRecord->getTsId(), $this->dbRecord->getTcId()) === false )
		{
			// if inteligent Manager disabled print error
			if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
			{
				$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTLINE');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTLINE');
			} else {
				$ret = PrecompiledQueries::getInstance()->addTsTcMap($this->dbRecord->getTsId(), $this->dbRecord->getTcId());
				if( $ret !== true )
				{
					return $ret;
				}
			}
		}
		
		/*
		 * Handle test line properly
		 */ 
		 // check and update tl id
		$ret = $this->checkAndPrepareTestLine();
		if($ret !== true)
		{
			return $ret;
		}
		 
		return true;
	}
	
	/*
	 * function checks if build increment record exists in DB and if not creates it with it's dependencies
	 * 
	 */
	private function checkAndPrepareBuildIncrement()
	{  
		// if incid && increment does not exists return error
		if( $this->dbRecord->getIncId() === null && $this->dbRecord->getIncrement() === null ) {
			// if this is a new DB record to be added
			if ( Utils::emptystr($this->dbRecord->getId()) ) {
				$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_BUILD_INCREMENT');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return Text::_('NO_BUILD_INCREMENT');
			}
		} else {
			// create BuildIncrementRecordManager object to perform BuildIncrement related checks/updates
			$buildIncrement = new BuildIncrement();
			$incrementRecordManager = new BuildIncrementRecordManager($buildIncrement);
			if( $this->dbRecord->getIncId() === null && $this->dbRecord->getIncrement() !== null  ) {
			// build increment is specified by name	
				// try to check for the build in DB and get the corresponding buildid		
				$incrementRecordManager->dbRecord->setName($this->dbRecord->getIncrement());
				$incid = $incrementRecordManager->isInDb();
				// if does not exist
				if( $incid === false)  
				{
					// if inteligent Manager disabled print error
					if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
					{
						$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD_INCREMENT');
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
						return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD_INCREMENT');
					} else {
						// set build testdate to the createdate of build
						$incrementRecordManager->dbRecord->setCreateDate($this->dbRecord->getCreateDate());
						// handle increment object
						$ret = $incrementRecordManager->handleRecord();
						if( $ret !== true )
						{
							return $ret;
						}
						// update incid in Build Obj
						$incid = $incrementRecordManager->isInDb();
					}
				}
				// update incid in Build Obj
				$this->dbRecord->setIncId($incid);
				$incrementRecordManager->dbRecord->setId($incid);
			} else {
			// build increment is specified by incid	
				// check if increment exists in DB
				$incrementRecordManager->dbRecord->setId($this->dbRecord->getIncId());
				if($incrementRecordManager->isInDbByInx() === false) {
					$str = get_class()."::".__FUNCTION__.": incid ".Text::_('HAS_WRONG_VALUE').": ".$this->dbRecord->getIncId();
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return "incid ".Text::_('HAS_WRONG_VALUE').": ".$this->dbRecord->getIncId();
				} 
			}
		}
		return true;	
	}
	
	/*
	 * function checks if build record exists in DB and if not creates it with it's dependencies
	 * 
	 */
	private function checkAndPrepareBuild()
	{
	// check and update build id 
		if( $this->dbRecord->getBuildId() === null && $this->dbRecord->getBuild() === null ) {
			// if this is a new DB record to be added
			if ( Utils::emptystr($this->dbRecord->getId()) ) {
				$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_BUILD');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return Text::_('NO_BUILD');
			}
		} else {
			// create BuildRecordManager object to perform Build related checks/updates
			$build = new Build();
			$buildRecordManager = new BuildRecordManager($build);
			if( $this->dbRecord->getBuildId() === null && $this->dbRecord->getBuild() !== null  ) {
			// build is specified by name	
				// try to check for the build in DB and get the corresponding buildid		
				$buildRecordManager->dbRecord->setName($this->dbRecord->getBuild());
				$buildid = $buildRecordManager->isInDb();	
				// if does not exist
				if( $buildid === false)  {
					$buildRecordManager->dbRecord->setIncId($this->dbRecord->getIncId());
					$buildRecordManager->dbRecord->setIncrement($this->dbRecord->getIncrement());
					// if intelligent Manager disabled print error
					if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
					{
						$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD');
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
						return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD');
					} else {
						// set build testdate to the createdate of test result
						$buildRecordManager->dbRecord->setCreateDate($this->dbRecord->getCreateDate());
						// handle increment object
						$ret = $buildRecordManager->handleRecord();
						if( $ret !== true )
						{
							return $ret;
						}
						// update buildid
						$buildid = $buildRecordManager->isInDb();
					}
				} 
				// update buildid in testResultObj
				$this->dbRecord->setBuildId($buildid);
				$buildRecordManager->dbRecord->setId($buildid);			
			} else {
			// build is specified by buildid	
				// check if build exists in DB
				$buildRecordManager->dbRecord->setId($this->dbRecord->getBuildId());
				if($buildRecordManager->isInDbByInx() === false) {
					$str = get_class()."::".__FUNCTION__.": buildid=".$this->dbRecord->getBuildId().' '.Text::_('NOT_IN_DB');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return "buildid=".$this->dbRecord->getBuildId().' '.Text::_('NOT_IN_DB');
				}
			}
			
			// Now update testdate of the build according to tr create date
			if( $buildRecordManager->updateBuildTestDate($this->dbRecord->getCreateDate()) === false) {
				$str = get_class()."::".__FUNCTION__.Text::_('BUILD_TESTDATE_UPDATE').' '.Text::_('FAILED').' '.Text::_('SQL_ERROR');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return Text::_('BUILD_TESTDATE_UPDATE').' '.Text::_('FAILED').' '.Text::_('SQL_ERROR');
			}
		}
		return true;
	}

	/*
	 * function checks if test suite record exists in DB and if not creates it with it's dependencies
	 * 
	 */
	private function checkAndPrepareTestSuite()
	{	
		/*
		 * Handle test suite properly
		 */ 
		 // check and update ts id
		if( $this->dbRecord->getTsId() === null && $this->dbRecord->getTestSuite() === null ) {
			// if this is a new DB record to be added
			if ( Utils::emptystr($this->dbRecord->getId()) ) {
				$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_TESTSUITE');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return Text::_('NO_TESTSUITE');
			}
		} else {
			// create TestSuiteRecordManager object to perform TestSuite related checks/updates
			$testsuite = new TestSuite();
			$testsuiteRecordManager = new TestSuiteRecordManager($testsuite);
			if( $this->dbRecord->getTsId() === null && $this->dbRecord->getTestSuite() !== null  ) {
			// test suite is specified by name	
				// try to check for the test suite in DB and get the corresponding tsid
				$testsuiteRecordManager->dbRecord->setName($this->dbRecord->getTestSuite());
				$tsid = $testsuiteRecordManager->isInDb();
				// if does not exist
				if( $tsid === false)  {
					// if inteligent Manager disabled print error
					if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
					{
						$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTSUITE');
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
						return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTSUITE');
					} else {
						// set build testdate to the createdate of test result
						$testsuiteRecordManager->dbRecord->setCreateDate($this->dbRecord->getCreateDate());
						// handle increment object
						$ret = $testsuiteRecordManager->handleRecord();
						if( $ret !== true )
						{
							return $ret;
						}
						// update tsid
						$tsid = $testsuiteRecordManager->isInDb();
					}
				} 
				// update tsid in testResultObj
				$this->dbRecord->setTsId($tsid);
				$testsuiteRecordManager->dbRecord->setId($tsid);
			} else {
			// testcase is specified by tsid	
				// check if tsid exists in DB
				$testsuiteRecordManager->dbRecord->setId($this->dbRecord->getTsId());
				if($testsuiteRecordManager->isInDbByInx() === false) {
					$str = get_class()."::".__FUNCTION__.": tsid=".$this->dbRecord->getTsId().' '.Text::_('NOT_IN_DB');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return "tsid=".$this->dbRecord->getTsId().' '.Text::_('NOT_IN_DB');
				} 
			}
		}
		return true;
	}
	
	/*
	 * function checks if test case record exists in DB and if not creates it with it's dependencies
	 * 
	 */
	private function checkAndPrepareTestCase()
	{	
		/*
		 * Handle test case properly
		 */ 
		 // check and update tc id
		if( $this->dbRecord->getTcId() === null && $this->dbRecord->getTestCase() === null ) {
			// if this is a new DB record to be added
			if ( Utils::emptystr($this->dbRecord->getId()) ) {			
				$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_TESTCASE');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return Text::_('NO_TESTCASE');
			}
		} else {
			// create TestCaseRecordManager object to perform TestCase related checks/updates
			$testcase = new TestCase();
			$testcaseRecordManager = new TestCaseRecordManager($testcase);
			if( $this->dbRecord->getTcId() === null && $this->dbRecord->getTestCase() !== null  ) {
			// test case is specified by name	
				// try to check for the test case in DB and get the corresponding tcid	
				$testcaseRecordManager->dbRecord->setName($this->dbRecord->getTestCase());
				$tcid = $testcaseRecordManager->isInDb();					
				// if does not exist
				if( $tcid === false)  {
					// if inteligent Manager disabled print error
					if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
					{
						$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTCASE');
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
						return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTCASE');
					} else {
						// if feature name and optionally coverage and feature hlink if set are specified add them too
						if( $this->dbRecord->getFeature() !== null) {
							$testcaseRecordManager->dbRecord->setFeature($this->dbRecord->getFeature());
							$testcaseRecordManager->dbRecord->setCoverage($this->dbRecord->getCoverage() === null ? 0 : $this->dbRecord->getCoverage());
							if( $this->dbRecord->getFeatureHlink() !== null) {
								$testcaseRecordManager->dbRecord->setFeatureHlink($this->dbRecord->getFeatureHlink());
							}
						}
						// set createdate of test result
						$testcaseRecordManager->dbRecord->setCreateDate($this->dbRecord->getCreateDate());
						// handle increment object
						$ret = $testcaseRecordManager->handleRecord();
						if( $ret !== true )
						{
							return $ret;
						}
						// update tcid
						$tcid = $testcaseRecordManager->isInDb();	
					}
				} 
				// update tcid in testResultObj
				$this->dbRecord->setTcId($tcid);
				$testcaseRecordManager->dbRecord->setId($tcid);
			} else {
			// testcase is specified by tcid	
				// check if tcid exists in DB
				$testcaseRecordManager->dbRecord->setId($this->dbRecord->getTcId());
				if($testcaseRecordManager->isInDbByInx() === false) {				
					$str = get_class()."::".__FUNCTION__.": tcid=".$this->dbRecord->getTcId().' '.Text::_('NOT_IN_DB');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return "tcid=".$this->dbRecord->getTcId().' '.Text::_('NOT_IN_DB');
				}
			}
		}
		return true;
	}

	/*
	 * function checks if test line record exists in DB and if not creates it with it's dependencies
	 * 
	 */
	private function checkAndPrepareTestLine()
	{		
		/*
		 * Handle test line properly
		 */ 
		 // check and update tl id
		if( $this->dbRecord->getTlId() === null && $this->dbRecord->getTestLine() === null ) {
			// if this is a new DB record to be added
			if ( Utils::emptystr($this->dbRecord->getId()) ) {
				$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_TESTLINE');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return Text::_('NO_TESTLINE');
			}
		} else {
			// create TestLineRecordManager object to perform TestLine related checks/updates
			$testline = new TestLine();
			$testlineRecordManager = new TestLineRecordManager($testline);
			if( $this->dbRecord->getTlId() === null && $this->dbRecord->getTestLine() !== null  ) {
			// test line is specified by name	
				// try to check for the test case in DB and get the corresponding tlid	
				$testlineRecordManager->dbRecord->setName($this->dbRecord->getTestLine());
				$tlid = $testlineRecordManager->isInDb();						
				// if does not exist
				if( $tlid === false)  {
					// if inteligent Manager disabled print error
					if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
					{
						$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTLINE');
						Tracer::getInstance()->log($str, LOGLEV_ERROR);
						return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTLINE');
					} else {
						// set createdate of test result
						$testlineRecordManager->dbRecord->setCreateDate($this->dbRecord->getCreateDate());
						// handle increment object
						$ret = $testlineRecordManager->handleRecord();
						if( $ret !== true )
						{
							return $ret;
						}
						// update tlid
						$tlid = $testlineRecordManager->isInDb();
					}
				} 
				// update tlid in testResultObj
				$this->dbRecord->setTlId($tlid);
				$testlineRecordManager->dbRecord->setId($tlid);
			} else {
			// testcase is specified by tlid	
				// check if tlid exists in DB
				$testlineRecordManager->dbRecord->setId($this->dbRecord->getTlId());
				if($testlineRecordManager->isInDbByInx() === false) {	
					$str = get_class()."::".__FUNCTION__.": tlid=".$this->dbRecord->getTlId().' '.Text::_('NOT_IN_DB');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return "tlid=".$this->dbRecord->getTcId().' '.Text::_('NOT_IN_DB');
				} 
			}
		} 
		return true;
	}	
}

class DescriptionRecordManager extends RecordManager
{
	public function __construct(Description& $dbRecord)
	{
		parent::__construct($dbRecord);
	}
	
	/*
     * function prepares record specific part
     * return true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     */ 
    protected function prepareRecordSpecific()
    {
		// make sure an empty string is set in case there is no description		
		if(empty($this->dbRecord->getDescription()))
		{
			$this->dbRecord->setDescription('');
		} 
		return true;
	}
	
	/*
	 * overriding the parent function as handling of description is slightly different from the other records
	 * i.e. insert or update record only if $this->dbRecord->getId() is specified
	 * Note. the description id is the same as it's correspoding main record id
	 * i.e. is TestResult entry exists in testresults table it's unique ID is used to create an entry in the testresult_description table
	 */		  
    public function handleRecord()
    {
		$finalReturn = true;
		// check if the test result has a defined Id
		if(! Utils::emptystr($this->dbRecord->getId())) {
			$ret = $this->prepareRecord();
			if( $ret !== true )
			{
				return $ret;
			}
			// add build to DB if allowed (use precompiled query if exists)
			$preparedQuery = $this->dbRecord->getCurrentQuery();
			// check using name/hash if the dbRecord exists in DB
			$checkIndex = $this->isInDb();
			if( $checkIndex === false )  {
				// if id is not found insert a new record
				$finalReturn = $this->dbRecord->insert($preparedQuery);
			} else {
				// since record exists, try to update it
				// first id corresponding to the given name has to be set (as it is a key for the update)
				$this->dbRecord->setId($checkIndex);
				$finalReturn = $this->dbRecord->update($preparedQuery);
			}
		}
		
		return $finalReturn;
	} 
}

?>
