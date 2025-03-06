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
* TestResultStats is a placeholder for total/passed test resutls queries objects
*/
class TestResultStats
{
	private $tableName = null;	// table where the stats select queries will be executed
	private $conditions = null; // string with sql WHERE conditions
	private $totalResultsQuery = null;	// holds DbQueryBuilder obj for total results query
	private $passedResultsQuery = null; // holds DbQueryBuilder obj for passed results query
	private $date = null;	// used to setup a date
	
	/*
	 * constructor
	 */ 
	public function __construct($sqlconditions=null)
	{
		$this->tableName = 'testresults';
		if( !empty($sqlconditions) )
		{
			$this->conditions = $sqlconditions;
		}
	}
	
	public function getTotalResultsQuery()
	{
		if(empty($this->totalResultsQuery))
		{
			$this->prepareQueries();
		}
		return $this->totalResultsQuery;
	}
	
	public function getPassedResultsQuery()
	{
		if(empty($this->passedResultsQuery))
		{
			$this->prepareQueries();
		}
		return $this->passedResultsQuery;
	}
	
	public function getTotalResults(array $values)
	{
		$count = 0;
		$res = $this->getTotalResultsQuery()->executeQuery($values);
		if($res)
		{
			$row = $res->FetchRow();
			$count = $row['c'];
			$this->date = $row['d'];
		}
		return $count;
	}
	
	public function getPassedResults(array $values)
	{
		$count = 0;
		$res = $this->getPassedResultsQuery()->executeQuery($values);
		if($res)
		{
			$row = $res->FetchRow();
			$count = $row['c'];
			//$this->date = $row['d'];
		}
		return $count;
	}

	public function getDate()
	{
		return $this->date;
	}
	
	public function prepareQueries()
	{
		// total query first
		$totalQuery = new DbQueryBuilder(TABLE_PREFIX.$this->tableName);	
		$totalQuery->addColumns(array('COUNT(0) as c', 'createdate as d')); 	
		if(!empty($this->conditions))
		{
			if(is_array($this->conditions)) {
				foreach ($this->conditions AS $value) 
				{
					$totalQuery->addCondition($value);
				}
			} else {
				$totalQuery->addCondition($this->conditions);
			}
		}
		$totalQuery->prepareQuery();
		$this->totalResultsQuery = $totalQuery;
		
		// passed query
		$passedQuery = new DbQueryBuilder(TABLE_PREFIX.$this->tableName);	
		$passedQuery->addColumns(array('COUNT(0) as c', 'createdate as d')); 	
		$passedQuery->addCondition('tcverdict=0');
		if(!empty($this->conditions))
		{
			if(is_array($this->conditions)) {
				foreach ($this->conditions AS $value) 
				{
					$passedQuery->addCondition($value);
				}
			} else {
				$passedQuery->addCondition($this->conditions);
			}			
		}
		$passedQuery->prepareQuery();
		$this->passedResultsQuery = $passedQuery;
	}
	
	/*
	 * function sets WHERE conditions (string or array)
	 */ 	
	public function setConditions($sqlcondition)
	{
		$this->conditions = $sqlcondition;
	}
}

 
 ?>
