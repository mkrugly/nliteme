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
 * PrecompiledQueries is a singleton class storing often used queries in a precompiled form
 * This allow to reuse them without a need to precompile at every query call
 * (for a better perfomance)
 */ 
class PrecompiledQueries
{
	private static $instance;
	private static $queriesList;
	private function __construct() {}
	private function __clone() {}
	
//	/*
//	 * function returns a name (column build, tcname or filepath respecitively) if index exists in table, otherwise false
//	 * it uses precompiled query if exists or creates a new precompiled query 
//	 * (to speed up query process)
//	 */ 
//	private function isInDbByInx($table, $inx)
//	{
//		if(! self::$queriesList->offsetExists($table.'_ByInx'))
//		{
//			$name = '';
//			if( preg_match('/.*builds/',$table) ) {
//				$name = "build";
//				$queryCond = 'buildid=?';
//			} else if( preg_match('/.*build_increments/',$table) ) {
//				$name = "increment";
//				$queryCond = 'incid=?';
//			} else if( preg_match('/.*testsuites/',$table) ) {
//				$name = "tsname";
//				$queryCond = 'tsid=?';
//			} else if( preg_match('/.*testcases/',$table) ) {
//				$name = "tcname";
//				$queryCond = 'tcid=?';
//			} else if( preg_match('/.*testlines/',$table) ) {
//				$name = "tlname";
//				$queryCond = 'tlid=?';
//			} else if( preg_match('/.*testresults/',$table) ) {
//				$name = "filepath";
//				$queryCond = 'id=?';
//			}
//			$query = new DbQueryBuilder($table, array($name));
//			$query->addCondition($queryCond);
//			$query->prepareQuery();
//			self::$queriesList->offsetSet($table.'_ByInx', $query);
//		}
//		$rs = self::$queriesList->offsetGet($table.'_ByInx')->executeQuery(array($inx));
//		if(! $rs->EOF ) {
//			$row = $rs->FetchRow();
//			// since row is an array with one element (can associative) get it's 1st element
//			return current($row);
//		} else {
//			return false;
//		}
//	}
//	
//	/*
//	 * function return index if hash/name exists in table, 
//	 * otherwise returns FALSE (use === on boolean opertor for check)
//	 * it uses precompiled query if exists or creates a new precompiled query 
//	 * (to speed up query process)
//	 */ 	
//	private function isInDb($table, $value)
//	{
//		if(! self::$queriesList->offsetExists($table))
//		{
//			$id = '';
//			$name = '';
//			if( preg_match('/.*builds/',$table) ) {
//				$id = "buildid";
//				$name = "build";
//			} else if( preg_match('/.*build_increments/',$table) ) {
//				$id = "incid";
//				$name = "increment";
//			} else if( preg_match('/.*testsuites/',$table) ) {
//				$id = "tsid";
//				$name = "tsname";
//			} else if( preg_match('/.*testcases/',$table) ) {
//				$id = "tcid";
//				$name = "tcname";
//			} else if( preg_match('/.*testlines/',$table) ) {
//				$id = "tlid";
//				$name = "tlname";
//			} else if( preg_match('/.*testresults/',$table) ) {
//				$id = "id";
//				$name = "hash";
//			}
//			$query = new DbQueryBuilder($table, array($id));
//			$query->addCondition($name."=?");
//			$query->prepareQuery();
//			self::$queriesList->offsetSet($table, $query);
//		}
//		$rs = self::$queriesList->offsetGet($table)->executeQuery(array($value));
//		if(! $rs->EOF ) {
//			$row = $rs->FetchRow();
//			// since row is an array with one element (can associative) get it's 1st element
//			return intval(current($row));
//		} else {
//			//$str = get_class()."::".__FUNCTION__.": NAME/HASH: ".$value.Text::_('NOT_IN_DB').": ".$table;
//			//Tracer::getInstance()->log($str, LOGLEV_DEBUG);
//			return false;
//		}
//	}

    /*
     * function returns a reference to PrecompiledQueries singleton instance 
     */ 
	public static function getInstance()
	{
        if (! isset(self::$instance) ) {
            self::$instance = new PrecompiledQueries();
            self::$queriesList = new ArrayObject();
        }
        return self::$instance;
    }

//	/*
//     * function returns true if build index exists in table, false otherwise 
//     */ 
//    public function isBuildByInx($inx)
//    {
//		return self::isInDbByInx(TABLE_PREFIX.'builds', $inx);
//	}
//	/*
//     * function returns true if build name exists in table, false otherwise 
//     */ 	
//	public function isBuild($name)
//    {
//		return self::isInDb(TABLE_PREFIX.'builds', $name);
//	}
//	/*
//     * function returns true if build_inrement index exists in table, false otherwise 
//     */ 
//    public function isBuildIncrementByInx($inx)
//    {
//		return self::isInDbByInx(TABLE_PREFIX.'build_increments', $inx);
//	}
//	/*
//     * function returns true if build_increment name exists in table, false otherwise 
//     */ 	
//	public function isBuildIncrement($name)
//    {
//		return self::isInDb(TABLE_PREFIX.'build_increments', $name);
//	}
//	/*
//     * function returns true if test suite index exists in table, false otherwise 
//     */ 	
//    public function isTestSuiteByInx($inx)
//    {
//		return self::isInDbByInx(TABLE_PREFIX.'testsuites', $inx);
//	}
//	/*
//     * function returns true if test suite name exists in table, false otherwise 
//     */ 	
//	public function isTestSuite($name)
//    {
//		return self::isInDb(TABLE_PREFIX.'testsuites', $name);
//	}
//	/*
//     * function returns true if test case index exists in table, false otherwise 
//     */ 	
//    public function isTestCaseByInx($inx)
//    {
//		return self::isInDbByInx(TABLE_PREFIX.'testcases', $inx);
//	}
//	/*
//     * function returns true if test case name exists in table, false otherwise 
//     */ 	
//	public function isTestCase($name)
//    {
//		return self::isInDb(TABLE_PREFIX.'testcases', $name);
//	}
//	/*
//     * function returns true if test line index exists in table, false otherwise 
//     */ 	
//    public function isTestLineByInx($inx)
//    {
//		return self::isInDbByInx(TABLE_PREFIX.'testlines', $inx);
//	}
//	/*
//     * function returns true if test line name exists in table, false otherwise 
//     */ 	
//	public function isTestLine($name)
//    {
//		return self::isInDb(TABLE_PREFIX.'testlines', $name);
//	}
//	/*
//     * function returns true if test result index exists in table, false otherwise 
//     */ 	
//	public function isTestResultByInx($inx)
//    {
//		return self::isInDbByInx(TABLE_PREFIX.'testresults', $inx);
//	}
//	/*
//     * function returns true if test result hash exists in table, false otherwise 
//     */ 	
//	public function isTestResult($hash)
//    {
//		return self::isInDb(TABLE_PREFIX.'testresults', $hash);
//	}

	/*
     * function returns true if test case and test suite are mapped, false otherwise 
     */ 	
	public function estimateTableCount($tablename)
    {
		if(! self::$queriesList->offsetExists('tablesizeestimator'))
		{

			$query = new DbQueryBuilder('information_schema.tables', array('table_rows'));
			$query->addCondition("table_name=?");
			$query->prepareQuery();
			self::$queriesList->offsetSet('tablesizeestimator', $query);
		}
		$rs = self::$queriesList->offsetGet('tablesizeestimator')->executeQuery(array($tablename));
		if(! $rs->EOF ) {
			$row = $rs->FetchRow();
			return $row['table_rows'];
		} else {
			return null;
		}
	}
	
	/*
     * function returns true if test case and test suite are mapped, false otherwise 
     */ 	
	public function isTsTcMap($tsid, $tcid)
    {
		if(! self::$queriesList->offsetExists('tstcmapexists'))
		{

			$query = new DbQueryBuilder(TABLE_PREFIX.'suites_cases_map', array('tsid'));
			$query->addCondition("tsid=? AND tcid=?");
			$query->prepareQuery();
			self::$queriesList->offsetSet('tstcmapexists', $query);
		}
		$rs = self::$queriesList->offsetGet('tstcmapexists')->executeQuery(array($tsid, $tcid));
		if(! $rs->EOF ) {
			return true;
		} else {
			//$str = get_class()."::".__FUNCTION__.Text::_('MAPPING')." tsid, tcid ".Text::_('NOT_IN_DB').": ".TABLE_PREFIX.'nliteme_suites_cases_map';
			//Tracer::getInstance()->log($str, LOGLEV_DEBUG);
			return false;
		}
	}
	
	/*
     * function add a mapping of tsid, tcid into suites_cases_map table
     * returns true on success, error string otherwise
     */ 	
	public function addTsTcMap($tsid, $tcid)
    {
		if(! self::$queriesList->offsetExists('tstcmapadd'))
		{

			$query = new DbQueryBuilder(TABLE_PREFIX.'suites_cases_map', array('tsid', 'tcid'), SQLCOMMAND::_INSERT);
			$query->prepareQuery();
			self::$queriesList->offsetSet('tstcmapadd', $query);
		}
		//$str = self::$queriesList->offsetGet('tstcmapadd')->getQuery();
		//Tracer::getInstance()->log($str, LOGLEV_ERROR);
		$rs = self::$queriesList->offsetGet('tstcmapadd')->executeQuery(array($tsid, $tcid));
		// if false was returned it means SQL failed
		if(! $rs )
		{
			$str = Text::_('INSERT_OF').' '.Text::_('MAPPING')." {tsid, tcid} = {".$tsid.','.$tcid."}".Text::_('FAILED').' '.Text::_('SQL_ERROR');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return $str;
		}
		return true;
	}
	
	/*
     * function deletes a mapping of tsid, tcid in suites_cases_map table
     * returns true on success, error string otherwise
     */ 	
	public function deleteTsTcMap($tsid, $tcid)
    {
		if(! self::$queriesList->offsetExists('tstcmapdelete'))
		{
			$query = new DbQueryBuilder(TABLE_PREFIX.'suites_cases_map', null, SQLCOMMAND::_DELETE);
			$query->addCondition("tsid=? AND tcid=?");
			$query->prepareQuery();
			self::$queriesList->offsetSet('tstcmapdelete', $query);
		}
		$rs = self::$queriesList->offsetGet('tstcmapdelete')->executeQuery(array($tsid, $tcid));
		// if false was returned it means SQL failed
		if(! $rs )
		{
			$str = Text::_('DELETE_OF').' '.Text::_('MAPPING')." tsid, tcid ".Text::_('FAILED').' '.Text::_('SQL_ERROR');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return $str;
		}
		return true;
	}
	
	/*
	 * function performs an update of testdate in the build table for a given buildId
	 * added to speed up testresult insert process, since for every test resul the test date in build table has to be updated to
	 * returns true on success, false otherwise
	 */
	public function updateBuildTestDateByBuildId($buildid, $buildTestDate)
	{
		if( is_numeric($buildid) && ! empty($buildTestDate) ) {
			// prepare a query if it does not exists yet
			if(! self::$queriesList->offsetExists('updateBuildTestDateByBuildId'))
			{
				$query = new DbQueryBuilder(TABLE_PREFIX.'builds', array('testdate'), SQLCOMMAND::_UPDATE);
				$query->addCondition("buildid=?");
				$query->prepareQuery();
				self::$queriesList->offsetSet('updateBuildTestDateByBuildId', $query);
			}

			$rs = self::$queriesList->offsetGet('updateBuildTestDateByBuildId')->executeQuery(array($buildTestDate, $buildid));
			if( $rs ) {
				return true;
			} else {
				$str = get_class()."::".__FUNCTION__.": ".Text::_('SQL_ERROR');
				$str = $str." (query:".self::$queriesList->offsetGet('updateBuildTestDateByBuildId')->getQuery().")";
				Tracer::getInstance()->log($str, LOGLEV_DEBUG);
				return false;
			}			
		} else {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('WRONG_ARGUMENT');
			Tracer::getInstance()->log($str, LOGLEV_DEBUG);
			return false;
		}
	}
	/*
	 * function adds a DbQueryBuilder object with a precompiled query to the list
	 * the query is identified by the name given as a 1st argument - therefore is shall be unique
	 */ 
	public function addPrecompiledQuery($queryName, DbQueryBuilder $precompiledQuery)
    {
		$str = get_class()."::".__FUNCTION__.": Adding Precompiled Query: ".$queryName;
		Tracer::getInstance()->log($str, LOGLEV_DEBUG);
		self::$queriesList->offsetSet($queryName, $precompiledQuery);
	}
	/*
	 * function returns a precompiled identified by the name given as an argument
	 */ 
	public function getPrecompiledQuery($queryName)
    {
		$str = get_class()."::".__FUNCTION__.": Retrieving Precompiled Query: ".$queryName;
		Tracer::getInstance()->log($str, LOGLEV_DEBUG);
		if(self::$queriesList->offsetExists($queryName))
		{
			return self::$queriesList->offsetGet($queryName);
		} else {
			return false;
		}
	}	
}

?>
