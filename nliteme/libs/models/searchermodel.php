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

abstract class SearcherModel extends Model
{
	private $columnConfig = null; // DbColumnList containing column config information retrieved from preferences
	protected $columnConfigName = null; // name of columnconfig from preferences (same as table name this config is made for)
	protected $searchableColumnConfig = null; // DbColumnList containing column config information for searchable columns retrieved from preferences

	/*
	 * constructor
	 */ 
	public function __construct($columnConfigName)
	{
		$this->columnConfigName = $columnConfigName;
		$this->getColumnConfig($columnConfigName);
		$this->getSearchableColumnList();
	}

	/*
	 * function to retrieves the suitable search fields
	 */ 
	public function showList()
	{
		$this->fillColumnsData();
		return $this->getSearchableColumnList();
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
	 * function fills the relevant values arrays with data
	 */ 	
	protected function fillColumnsData()
	{
		$iterator = $this->getSearchableColumnList()->getIterator();
		while( $iterator->valid() )
		{
			$current = $iterator->current();
			$this->setColumnValues($current);
			$iterator->next();
		}
	}
	
	/*
	 * function fills the relevant values arrays with data
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
			$selectQuery = new DbQueryBuilder(TABLE_PREFIX.$tabName);
			$selectQuery->addColumns(array($indexName,$dbColumnConfig->getColumnRealName()));
			$selectQuery->addOrderBy($dbColumnConfig->getColumnRealName(),SQLORDERBY::_ASC);
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
		}
	}
}

class TestResultsSearcherModel extends SearcherModel
{
	public function __construct()
	{
		parent::__construct('testresults');
	}
}

class TestSuitesSearcherModel extends SearcherModel
{
	public function __construct()
	{
		parent::__construct('testsuites');		
	}
}

class TestCasesSearcherModel extends SearcherModel
{
	public function __construct()
	{
		parent::__construct('testcases');		
	}
}

class TestLinesSearcherModel extends SearcherModel
{
	public function __construct()
	{
		parent::__construct('testlines');
	}
}

class FeaturesSearcherModel extends SearcherModel
{
	public function __construct()
	{
		parent::__construct('features');
	}
}

class BuildsSearcherModel extends SearcherModel
{
	public function __construct()
	{
		parent::__construct('builds');
	}
}

class BuildIncrementsSearcherModel extends SearcherModel
{
	public function __construct()
	{
		parent::__construct('build_increments');
	}
}

?>
