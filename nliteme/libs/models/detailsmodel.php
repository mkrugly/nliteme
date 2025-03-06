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

abstract class DetailsModel extends Model
{
	protected $managerCName = null; // holds the RecordManager class name that handles the DbRecord object
	protected $dbRecord = null;	// holds the DbRecord object to work on
	protected $dbRecordCName = null;	// holds the name of DbRecord class for which $dbRecord obj is created
	protected $dbRecordCNameUpper = null;	// uppercase of $dbRecordCName for tracing purposes
	protected $selectQuery = null;// holds DbQueryBuilder object with a DetailsModel specific SQL SELECT query
	protected $descriptionSelectQuery = null;	// holds DbQueryBuilder object for a description selection
	
	/*
	 * constructor
	 */ 
	public function __construct(DbRecord $dbRecord)
	{
		$this->dbRecord = $dbRecord;
		$this->dbRecordCName = get_class($this->dbRecord);
		$this->dbRecordCNameUpper = strtoupper($this->dbRecordCName);
		$this->managerCName = $this->dbRecordCName.'RecordManager';
	}

	/*
	 * function to perform show details action
	 */ 
	public function showDetails($index)
	{
		return $this->editDetails($index);
	}
	
	/*
	 * function to perform edit (insert/update) action
	 */ 
	public function editDetails($index)
	{
		// if $index is set, get the corresponding entry for update, otherwise generate empty object 
		if(!empty($index)) {
			$rs = $this->getSelectQuery()->executeQuery(array($index));
			if(! $rs->EOF ) {
				// decompress description field (has to be done as early as possible to avoid problems e.g. when trim function is called)
				$rec = $rs->FetchRow();
				$rec['description'] = $this->getDescription($index);
				// convert row to DbColumnList
				$colList = $this->convertToDbColumnList($rec);
				$this->dbRecord = new $this->dbRecordCName($colList);
				return true;
			} else {
				$str = get_class()."::".__FUNCTION__.": ".Text::_($this->dbRecordCNameUpper).' '.$this->dbRecord->getKeyColumn().'='.$index.' '.Text::_('NOT_FOUND');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				$this->addMessage(Text::_($this->dbRecordCNameUpper).' '.$this->dbRecord->getKeyColumn().'='.$index.' '.Text::_('NOT_FOUND'));
				// generate empty form
				$this->dbRecord->initializeColumns();	// initialize proper field structure with no values
				return false;
			}
		} else {
			$this->dbRecord->initializeColumns();	// initialize proper field structure with no values
			return true;
		}
	}
	
	/*
	 * function to perform save action
	 */ 
	public function saveDetails(array $postParams)
	{
		if( !empty($postParams) ) {
			$colList = $this->convertToDbColumnList($postParams);
			$this->dbRecord = new $this->dbRecordCName($colList);
			$manager = new $this->managerCName($this->dbRecord);
			$result = $manager->handleRecord();
			if( $result === true ) {
				// get id if not present in the $this->dbRecord object e.g. a new record was saved
				$tmpId = $this->dbRecord->getId();
				if(empty($tmpId))
				{
					// define local var for better readability
					$tmpid = $manager->isInDb();
					$this->dbRecord->setId($tmpid);
				}
				$this->addMessage(Text::_('SAVE').' '.Text::_('SUCCEEDED').'! ');
				return true;
			} else {
				$this->addMessage(Text::_('SAVE').' '.Text::_('FAILED').'! '.$result);
				return false;
			}
		} else {
			$this->addMessage(Text::_('SAVE').' '.Text::_('FAILED').'! '.Text::_('UNSUPPORTED_UPLOAD_CONTENT '));
			//$this->edit(null); //generate empty content
			return false;
		}
	}
	
	/*
	 * function to perform delete action
	 */ 
	public function deleteDetails($index)
	{
		$this->dbRecord = new $this->dbRecordCName();
		$this->dbRecord->setId($index);
		$res = $this->dbRecord->delete();
		if($res === true){
			$this->addMessage(Text::_('DELETE_OF').' '.Text::_($this->dbRecordCNameUpper).' '.Text::_('SUCCEEDED'));
			return true;
		} else {
			$this->addMessage($res);
			return false;
		}
	}
	
	/*
	 * function to returns DbQueryBuilder object with a DetailsModel specific SQL SELECT query
	 */ 
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());		
			$selectQuery->addColumns(array('*')); // add columns to select from suitable DB table		
			$selectQuery->addCondition($this->dbRecord->getKeyColumn().'=?');
			$selectQuery->prepareQuery();	
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}

	/*
	 * function convert the hash name=>value to dbcolumn like format i.e. 
	 */ 
	protected function convertToDbColumnList(array $nameValueMap)
	{
		if(!empty($nameValueMap)) {
			$colList = new DbColumnList();
			foreach ($nameValueMap AS $name => $value) 
			{
				$fields = array();
				$fields['realname'] = $name;
				$fields['value'] = $value;
				$colList->addColumn(new DbColumn($fields));
			}
			return $colList;
		} else {
			$str = get_class()."::".__FUNCTION__.": ".$classType.Text::_('WRONG_ARGUMENT');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return null;
		}
	}

	/*
	 * function returns a description field from a relevant description table for a given index
	 */ 
	protected function getDescription($index)
	{
		$description = '';
		$query = $this->getDescriptionSelectQuery();
		if(!empty($query) && !empty($index))
		{
			$rs = $query->executeQuery(array($index));
			if(! $rs->EOF ) {
				// decompress description field (has to be done as early as possible to avoid problems e.g. when trim function is called)
				$rec = $rs->FetchRow();
				$description = Utils::emptystr($rec['description']) ? '' : gzuncompress($rec['description']);
			} 
		} 
		return $description;
	}

	/*
	 * function returns DbQueryBuilder object with a Details Description SQL SELECT query
	 */ 
	protected function getDescriptionSelectQuery()
	{
		if(!empty($this->descriptionSelectQuery) || ! $this->descriptionSelectQuery instanceof DbQueryBuilder)
		{
			$descriptionCName = $this->dbRecordCName.'Description';
			if(class_exists($descriptionCName))
			{
				$descriptionDbRecord = new  $descriptionCName();
				$selectQuery = new DbQueryBuilder($descriptionDbRecord->getTableName());		
				$selectQuery->addColumns(array('*')); // add columns to select from suitable DB table		
				$selectQuery->addCondition($descriptionDbRecord->getKeyColumn().'=?');
				$selectQuery->prepareQuery();	
				$this->descriptionSelectQuery = $selectQuery;
			}
		}
		return $this->descriptionSelectQuery;
	}
	
	/*
	 * function to returns DbQueryBuilder object with a DetailsModel specific SQL SELECT query
	 */ 
	public function getDbRecord()
	{
		return $this->dbRecord;
	}
}

/*
 * class to handle BuildIncrement details
 */ 
class BuildIncrementModel extends DetailsModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new BuildIncrement());
	}
}

/*
 * class to handle Build details
 */ 
class BuildModel extends DetailsModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new Build());
	}
	
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addJoinTable(TABLE_PREFIX.'build_increments','incid'); // inner join TABLE build_increments on buildid field
			$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('increment'),TABLE_PREFIX.'build_increments'); // add columns to select from TABLE testcases		
			$selectQuery->addCondition($this->dbRecord->getKeyColumn().'=?');
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}	
}

/*
 * class to handle TestLine details
 */ 
class TestLineModel extends DetailsModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new TestLine());
	}
}

/*
 * class to handle TestSuite details
 */ 
class TestSuiteModel extends DetailsModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new TestSuite());
	}
}

/*
 * class to handle TestCase details
 * TODO. Check how to handle many to many relation between TestCase<=>TestSuite
 */ 
class TestCaseModel extends DetailsModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new TestCase());
	}
    
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addJoinTable(TABLE_PREFIX.'features','fid'); // inner join TABLE features on fid field
			$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('fname'),TABLE_PREFIX.'features'); // add columns to select from TABLE testcases		
			$selectQuery->addCondition($this->dbRecord->getKeyColumn().'=?');
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}	
}

class FeatureModel extends DetailsModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new Feature());
	}
	
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','fid'); // inner join TABLE testcases on fid field	
			$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('COUNT(@@tcid@@) as num_testcases', 'SUM(@@coverage@@) as coverage'),TABLE_PREFIX.'testcases'); // add columns to select from TABLE testcases
			$selectQuery->addCondition('@@'.$this->dbRecord->getKeyColumn().'@@=?');
			$selectQuery->prepareQuery();
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}	
	
	public function getCoverageQuery()
	{
		return $this->getSelectQuery();
	}
}


?>
