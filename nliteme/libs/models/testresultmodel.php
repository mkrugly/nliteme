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

class TestResultModel extends DetailsModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct(new TestResult());
	}
	
	/*
	 * function to returns DbQueryBuilder object with a DetailsModel specific SQL SELECT query
	 */ 
	protected function getSelectQuery()
	{
		if(!empty($this->selectQuery) || ! $this->selectQuery instanceof DbQueryBuilder)
		{
			$selectQuery = new DbQueryBuilder($this->dbRecord->getTableName());
			$selectQuery->addJoinTable(TABLE_PREFIX.'build_increments','incid'); // inner join TABLE build_increments on buildid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'builds','buildid'); // inner join TABLE builds on buildid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testsuites','tsid'); // inner join TABLE testsuites on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','tcid'); // inner join TABLE testcases on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testlines','tlid'); // inner join TABLE testlines on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'features','fid', SQLJOIN::LEFT,TABLE_PREFIX.'testcases'); // inner join TABLE features on fid field via testcases
			
			$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('increment'),TABLE_PREFIX.'build_increments'); // add columns to select from TABLE testcases
			$selectQuery->addColumns(array('build'),TABLE_PREFIX.'builds'); // add columns to select from TABLE testcases
			$selectQuery->addColumns(array('tsname'),TABLE_PREFIX.'testsuites'); // add columns to select from TABLE testsuites
			$selectQuery->addColumns(array('tcname', 'fid', 'coverage'),TABLE_PREFIX.'testcases'); // add columns to select from TABLE testcases
			$selectQuery->addColumns(array('tlname'),TABLE_PREFIX.'testlines'); // add columns to select from TABLE testlines
			$selectQuery->addColumns(array('fname'),TABLE_PREFIX.'features'); // add columns to select from TABLE testlines
			
			$selectQuery->addCondition($this->dbRecord->getKeyColumn().'=?');
			$selectQuery->prepareQuery();
			
			$this->selectQuery = $selectQuery;
		}
		return $this->selectQuery;
	}
}
?>
