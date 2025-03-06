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

class TestResultsModel extends ListModel
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		// create empty TestResult obj - used for delete operation for now
		parent::__construct(new TestResult());
		// set default conditions
		//$this->setOrderBy('builds.build', SQLORDERBY::_DESC);
		$this->setOrderBy('testcases.tcname', SQLORDERBY::_DESC);		
		// limit query to last 7 days
		$t = time() - (7 * 24 * 60 * 60);
		$this->setConditions(array('createdate_FROM'=>date('Y-m-d', $t)));
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
			
			// MKMK commented out due to large description part (can be set back when description is moved to other table), For now use explicit list of columns
			//$selectQuery->addColumns(array('*')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('id','createdate','incid','buildid','tsid','tcid','tlid','tcverdict'
							,'extracolumn_0','extracolumn_1','extracolumn_2','extracolumn_3','duration','filepath')); // add columns to select from TABLE testresults
			$selectQuery->addColumns(array('increment'),TABLE_PREFIX.'build_increments'); // add columns to select from TABLE testcases
			$selectQuery->addColumns(array('build'),TABLE_PREFIX.'builds'); // add columns to select from TABLE testcases
			$selectQuery->addColumns(array('tsname'),TABLE_PREFIX.'testsuites'); // add columns to select from TABLE testsuites
			$selectQuery->addColumns(array('tcname', 'fid', 'coverage'),TABLE_PREFIX.'testcases'); // add columns to select from TABLE testcases
            $selectQuery->addColumns(array('tlname'),TABLE_PREFIX.'testlines'); // add columns to select from TABLE testlines
			$selectQuery->addColumns(array('fname', 'hlink'),TABLE_PREFIX.'features'); // add columns to select from TABLE testlines
         
			
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
			$selectQuery->addJoinTable(TABLE_PREFIX.'builds','buildid'); // inner join TABLE builds on buildid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testsuites','tsid'); // inner join TABLE testsuites on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testcases','tcid'); // inner join TABLE testcases on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'testlines','tlid'); // inner join TABLE testlines on tcid field
			$selectQuery->addJoinTable(TABLE_PREFIX.'features','fid', SQLJOIN::LEFT,TABLE_PREFIX.'testcases'); // inner join TABLE features on fid field via testcases
            
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
