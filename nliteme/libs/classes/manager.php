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
 * Manager class for managing the DB content i.e. inserting, updating and removing builds, test cases and test results
 * The idea is to have one common class which can be used by admin page and other uploaders etc.
 */

class Manager
{
   
	/*
	 * function handles (prepares/check if exists/inserts/updates/delete) a dbRecord
	 * arguments: DbRecord object reference, 
	 *
	 * returns true on success, otherwsie a relevant error notification
	 * (use === on boolean opertor for check)
	 */ 
    public function handleRecord(DbRecord& $dbRecord)
    {
		$classType = get_class($dbRecord);
		// define check functions variables to map the precompiled queries functions
		$isRecordByInx = '';
		$isRecord = '';
		$getName = 'getName';
		if( $classType === 'Build' ) { 
			$isRecordByInx = 'isBuildByInx';
			$isRecord = 'isBuild';
		} else if( $classType === 'BuildIncrement') {
			$isRecordByInx = 'isBuildIncrementByInx';
			$isRecord = 'isBuildIncrement';
		} else if( $classType === 'TestCase' ) {
			$isRecordByInx = 'isTestCaseByInx';
			$isRecord = 'isTestCase';
		} else if( $classType === 'TestResult') {
			$isRecordByInx = 'isTestResultByInx';
			$isRecord = 'isTestResult';
			$getName = 'getHash';
		} else if( $classType === 'TestSuite') {
			$isRecordByInx = 'isTestSuiteByInx';
			$isRecord = 'isTestSuite';
		} else if( $classType === 'TestLine') {
			$isRecordByInx = 'isTestLineByInx';
			$isRecord = 'isTestLine';			
		} else {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('UNSUPPORTED_UPLOAD_CONTENT');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return Text::_('UNSUPPORTED_UPLOAD_CONTENT');
		}
		
		// check if the test result has a defined Id
		if(! Utils::emptystr($dbRecord->getId())) {
			// if ID is defined, check if the record with a given ID exists in DB
			if( PrecompiledQueries::getInstance()->$isRecordByInx($dbRecord->getId()) === false ) {
				$str = get_class()."::".__FUNCTION__.": ".$classType." id".Text::_('HAS_WRONG_VALUE').": ".$dbRecord->getId();
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return $classType." id".Text::_('HAS_WRONG_VALUE').": ".$dbRecord->getId();
			} else {
				// if the name is set make sure it does not collide with existing names in DB
				// Remember: $dbRecord->$getName() has to be unique
				$checkName = PrecompiledQueries::getInstance()->$isRecord($dbRecord->$getName());
				if($checkName !== false && intval($checkName) !== intval($dbRecord->getId()))
				{
					$name = $dbRecord->getNameColumn()->getColumnRealName();
					$str = get_class()."::".__FUNCTION__.": ".$classType." ".Text::_(strtoupper($name))." ".Text::_('IS_NOT_UNIQUE');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return $classType." ".Text::_(strtoupper($name))." ".Text::_('IS_NOT_UNIQUE');			
				}
				// update existing record using id - only allowed if id is present
				// be carefull there aren't any checks done here, so $dbRecord has to contain valid values
				// or you may spoil the DB 
				// update dbRecord in DB if allowed (use precompiled query if exists)
				$preparedQuery = $dbRecord->getCurrentQuery();
				$ret = $dbRecord->update($preparedQuery);
				return $ret;
			}
		} else {
			$ret = $this->prepareRecord($dbRecord);
			if( $ret !== true )
			{
				return $ret;
			}
			// add build to DB if allowed (use precompiled query if exists)
			$preparedQuery = $dbRecord->getCurrentQuery();
			// check using name/hash if the dbRecord exists in DB
			$checkIndex = PrecompiledQueries::getInstance()->$isRecord($dbRecord->$getName() );
			if( $checkIndex === false )  {
				// if id is not found insert a new record
				$ret = $dbRecord->insert($preparedQuery);
				return $ret;
			} else {
				// since record exists, try to update it
				// first id corresponding to the given name has to be set (as it is a key for the update)
				$dbRecord->setId($checkIndex);
				$ret = $dbRecord->update($preparedQuery);
				return $ret;
			}
		}
		return true;
	} 
	
    /*
     * wrapper function that call proper prepare function depending on the dbRecord object type
     * return a handler return code (true on success, erros string on failure)
     */
    public function prepareRecord(DbRecord& $dbRecord)
    {	
		$result = true;
		$classType = get_class($dbRecord);
		
		/* 
		 * common preparation
		 */ 
		// name must be defined
		if( Utils::emptystr($dbRecord->getName()) )
		{
			$str = get_class()."::".__FUNCTION__.": ".$classType.' '.Text::_('NAME').Text::_('NOT_DEFINED');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return $classType.' '.Text::_('NAME').Text::_('NOT_DEFINED');
		}
		// if creation date does not exists, set current date
		if( Utils::emptystr($dbRecord->getCreateDate()))
		{
			$dbRecord->setCreateDate(Utils::getCurTime());
		}
		
		// columns that shall have predefined value are mandatory, therefore
		// check for predefined values, make sure the predefined index is returned back
		// e.g. this will be a case for extracolumns, test verdict etc.
		$ret = $this->updatePredefinedColumns($dbRecord);
		if( $ret !== true )
		{
			return $ret;
		}
		
		/* 
		 * record type specific preparation
		 */ 
		if( $classType === 'Build' ) { 
			//print(get_class()."::".__FUNCTION__.": BUILD\n");
			$result = $this->prepareBuild($dbRecord);
		
		} else if( $classType === 'BuildIncrement') {
			$result = $this->prepareBuildIncrement($dbRecord);
			
		} else if( $classType === 'TestCase' ) {
			$result = $this->prepareTestCase($dbRecord);
			
		} else if( $classType === 'TestResult') {
			$result = $this->prepareTestResult($dbRecord);

		} else if( $classType === 'TestSuite') {
			$result = $this->prepareTestSuite($dbRecord);
		
		} else if( $classType === 'TestLine') {
			$result = $this->prepareTestLine($dbRecord);
							
		} else {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('UNSUPPORTED_UPLOAD_CONTENT');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			$result = Text::_('UNSUPPORTED_UPLOAD_CONTENT');
		}
		return $result;
	}
 
    /*
     * function prepares a Build object 
     * return true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     */ 
    public function prepareBuild(Build& $buildObj)
    {
		/*
		 * Handle increment properly
		 */ 
		// if incid && increment does not exists return error
		if( $buildObj->getIncId() === null && $buildObj->getIncrement() === null ) {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_BUILD_INCREMENT');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return Text::_('NO_BUILD_INCREMENT');
		} elseif( $buildObj->getIncId() === null && $buildObj->getIncrement() !== null  ) {
		// build increment is specified by name	
			// try to check for the build in DB and get the corresponding buildid		
			$incid = PrecompiledQueries::getInstance()->isBuildIncrement($buildObj->getIncrement());
			// if does not exist
			if( $incid === false)  
			{
				$increment = new BuildIncrement();
				$increment->setName($buildObj->getIncrement());
				// if inteligent Manager disabled print error
				if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
				{
					$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD_INCREMENT');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD_INCREMENT');
				} else {
					// set build testdate to the createdate of build
					$increment->setCreateDate($buildObj->getCreateDate());
					// handle increment object
					$ret = $this->handleRecord($increment);
					if( $ret !== true )
					{
						return $ret;
					}
					// update incid in Build Obj
					$incid = PrecompiledQueries::getInstance()->isBuildIncrement($increment->getName());
				}
			}
			// update incid in Build Obj
			$buildObj->setIncId($incid);
		} else {
		// build increment is specified by incid	
			// check if increment exists in DB
			if(PrecompiledQueries::getInstance()->isBuildIncrementByInx($buildObj->getIncId()) === false) {
				$str = get_class()."::".__FUNCTION__.": incid ".Text::_('HAS_WRONG_VALUE').": ".$buildObj->getIncId();
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return "incid ".Text::_('HAS_WRONG_VALUE').": ".$buildObj->getIncId();
			} 
		}
		return true;
	}
	
    /*
     * function prepares record specific part of BuildIncrement object 
     * return true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     */ 
    public function prepareBuildIncrement(BuildIncrement& $buildIncObj)
    {
		return true;
	}

    /*
     * function prepares record specific part of Testsuite object 
     * return true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     */ 	
	public function prepareTestSuite(TestSuite& $testCaseObj)
    {
		return true;
	}
	
    /*
     * function prepares record specific part of Testcase object 
     * return true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     */ 	
	public function prepareTestCase(TestCase& $testCaseObj)
    {
		return true;
	}
	
    /*
     * function prepares record specific part of Testline object 
     * return true on success, otherwise a relevant error notification
     * (use === on boolean opertor for check)
     */ 	
	public function prepareTestLine(TestLine& $testCaseObj)
    {
		return true;
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
	public function prepareTestResult(TestResult& $testResultObj)
    {
		/* columns in the testresults table
		[ID] => id
		[CREATEDATE] => createdate
		[INCID] => incid
		[BUILDID] => buildid
		[TSID] => tsid
		[TCID] => tcid
		[TLID] => tlid
		[TCVERDICT] => tcverdict
		[EXTRACOLUMN_0] => extracolumn_0
		[EXTRACOLUMN_1] => extracolumn_1
		[EXTRACOLUMN_2] => extracolumn_2
		[EXTRACOLUMN_3] => extracolumn_3
		[DURATION] => duration
		[DESCRIPTION] => description
		[FILEPATH] => filepath
		[HASH] => hash
		 */

		/*
		 * Handle increment properly
		 */ 
		// if incid && increment does not exists return error
		if( $testResultObj->getIncId() === null && $testResultObj->getIncrement() === null ) {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_BUILD_INCREMENT');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return Text::_('NO_BUILD_INCREMENT');
		} elseif( $testResultObj->getIncId() === null && $testResultObj->getIncrement() !== null  ) {
		// build increment is specified by name	
			// try to check for the build in DB and get the corresponding buildid		
			$incid = PrecompiledQueries::getInstance()->isBuildIncrement($testResultObj->getIncrement());
			// if does not exist
			if( $incid === false)  
			{
				$increment = new BuildIncrement();
				$increment->setName($testResultObj->getIncrement());
				// if inteligent Manager disabled print error
				if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
				{
					$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD_INCREMENT');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD_INCREMENT');
				} else {
					// set build testdate to the createdate of build
					$increment->setCreateDate($testResultObj->getCreateDate());
					// handle increment object
					$ret = $this->handleRecord($increment);
					if( $ret !== true )
					{
						return $ret;
					}
					// update incid
					$incid = PrecompiledQueries::getInstance()->isBuildIncrement($increment->getName() );
				}
			}
			// update incid in testResultObj
			$testResultObj->setIncId($incid);
		} else {
		// build increment is specified by incid	
			// check if increment exists in DB
			if(PrecompiledQueries::getInstance()->isBuildIncrementByInx($testResultObj->getIncId()) === false) {
				$str = get_class()."::".__FUNCTION__.": incid =".$testResultObj->getIncId().' '.Text::_('NOT_IN_DB');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return "incid =".$testResultObj->getIncId().' '.Text::_('NOT_IN_DB');
			} 
		}

		/*
		 * Handle build properly
		 */ 
		// check and update build id 
		if( $testResultObj->getBuildId() === null && $testResultObj->getBuild() === null ) {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_BUILD');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return Text::_('NO_BUILD');
		} elseif( $testResultObj->getBuildId() === null && $testResultObj->getBuild() !== null  ) {
		// build is specified by name	
			// try to check for the build in DB and get the corresponding buildid		
			$buildid = PrecompiledQueries::getInstance()->isBuild($testResultObj->getBuild());
			// if does not exist
			if( $buildid === false)  {
				$build = new Build();
				$build->setName($testResultObj->getBuild());
				$build->setIncId($testResultObj->getIncId());
				$build->setIncrement($testResultObj->getIncrement());
				// if inteligent Manager disabled print error
				if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
				{
					$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('BUILD');
				} else {
					// set build testdate to the createdate of test result
					$build->setCreateDate($testResultObj->getCreateDate());
					// handle increment object
					$ret = $this->handleRecord($build);
					if( $ret !== true )
					{
						return $ret;
					}
					// update buildid
					$buildid = PrecompiledQueries::getInstance()->isBuild($build->getName() );
				}
			} 
			// update buildid in testResultObj
			$testResultObj->setBuildId($buildid);
		} else {
		// build is specified by buildid	
			// check if build exists in DB
			if(PrecompiledQueries::getInstance()->isBuildByInx($testResultObj->getBuildId()) === false) {
				$str = get_class()."::".__FUNCTION__.": buildid=".$testResultObj->getBuildId().' '.Text::_('NOT_IN_DB');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return "buildid=".$testResultObj->getBuildId().' '.Text::_('NOT_IN_DB');
			}
		}
		// Now update testdate of the build according to tr create date
		if(PrecompiledQueries::getInstance()->updateBuildTestDateByBuildId($testResultObj->getBuildId(),$testResultObj->getCreateDate()) === false) {
			$str = get_class()."::".__FUNCTION__.Text::_('BUILD_TESTDATE_UPDATE').' '.Text::_('FAILED').' '.Text::_('SQL_ERROR');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return Text::_('BUILD_TESTDATE_UPDATE').' '.Text::_('FAILED').' '.Text::_('SQL_ERROR');
		}

		/*
		 * Handle test suite properly
		 */ 
		 // check and update ts id
		if( $testResultObj->getTsId() === null && $testResultObj->getTestSuite() === null ) {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_TESTSUITE');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return Text::_('NO_TESTSUITE');
		} elseif( $testResultObj->getTsId() === null && $testResultObj->getTestSuite() !== null  ) {
		// test suite is specified by name	
			// try to check for the test suite in DB and get the corresponding tsid		
			$tsid = PrecompiledQueries::getInstance()->isTestSuite($testResultObj->getTestSuite());
			// if does not exist
			if( $tsid === false)  {
				// if inteligent Manager disabled print error
				if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
				{
					$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTSUITE');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTSUITE');
				} else {
					$ts= new TestSuite();
					$ts->setName($testResultObj->getTestSuite());
					// set build testdate to the createdate of test result
					$ts->setCreateDate($testResultObj->getCreateDate());
					// handle increment object
					$ret = $this->handleRecord($ts);
					if( $ret !== true )
					{
						return $ret;
					}
					// update tsid
					$tsid = PrecompiledQueries::getInstance()->isTestSuite($ts->getName() );
				}
			} 
			// update tsid in testResultObj
			$testResultObj->setTsId($tsid);
		} else {
		// testcase is specified by tsid	
			// check if tsid exists in DB
			if(PrecompiledQueries::getInstance()->isTestSuiteByInx($testResultObj->getTsId()) === false) {
				$str = get_class()."::".__FUNCTION__.": tsid=".$testResultObj->getTsId().' '.Text::_('NOT_IN_DB');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return "tsid=".$testResultObj->getTsId().' '.Text::_('NOT_IN_DB');
			} 
		}

		/*
		 * Handle test case properly
		 */ 
		 // check and update tc id
		if( $testResultObj->getTcId() === null && $testResultObj->getTestCase() === null ) {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_TESTCASE');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return Text::_('NO_TESTCASE');
		} elseif( $testResultObj->getTcId() === null && $testResultObj->getTestCase() !== null  ) {
		// test case is specified by name	
			// try to check for the test case in DB and get the corresponding tcid		
			$tcid = PrecompiledQueries::getInstance()->isTestCase($testResultObj->getTestCase());
			// if does not exist
			if( $tcid === false)  {
				// if inteligent Manager disabled print error
				if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
				{
					$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTCASE');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTCASE');
				} else {
					$tc= new TestCase();
					$tc->setName($testResultObj->getTestCase());
					// set build testdate to the createdate of test result
					$tc->setCreateDate($testResultObj->getCreateDate());
					// handle increment object
					$ret = $this->handleRecord($tc);
					if( $ret !== true )
					{
						return $ret;
					}
					// update tcid
					$tcid = PrecompiledQueries::getInstance()->isTestCase($tc->getName() );
				}
			} 
			// update tcid in testResultObj
			$testResultObj->setTcId($tcid);
		} else {
		// testcase is specified by tcid	
			// check if tcid exists in DB
			if(PrecompiledQueries::getInstance()->isTestCaseByInx($testResultObj->getTcId()) === false) {
				$str = get_class()."::".__FUNCTION__.": tcid=".$testResultObj->getTcId().' '.Text::_('NOT_IN_DB');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return "tcid=".$testResultObj->getTcId().' '.Text::_('NOT_IN_DB');
			} 
		}
		
		/*
		 * Handle tsid <=> tcid mapping
		 * i.e. if (mapping does not exists) {
		 * 			if intelligentManager flag  != 1 {issue error}
		 * 			else {add mapping}
		 */ 
		if(	PrecompiledQueries::getInstance()->isTsTcMap($testResultObj->getTsId(), $testResultObj->getTcId()) === false )
		{
			// if inteligent Manager disabled print error
			if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
			{
				$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTLINE');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTLINE');
			} else {
				$ret = PrecompiledQueries::getInstance()->addTsTcMap($testResultObj->getTsId(), $testResultObj->getTcId());
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
		if( $testResultObj->getTlId() === null && $testResultObj->getTestLine() === null ) {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('NO_TESTLINE');
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return Text::_('NO_TESTLINE');
		} elseif( $testResultObj->getTlId() === null && $testResultObj->getTestLine() !== null  ) {
		// test line is specified by name	
			// try to check for the test case in DB and get the corresponding tlid		
			$tlid = PrecompiledQueries::getInstance()->isTestLine($testResultObj->getTestLine());
			// if does not exist
			if( $tlid === false)  {
				// if inteligent Manager disabled print error
				if(Config::getInstance()->getSettings()->getParam("intelligentManager") != 1)
				{
					$str = get_class()."::".__FUNCTION__.": ".Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTLINE');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return Text::_('INTELLIGENT_MANAGER_DISABLED').' '.Text::_('CANNOT_ADD').' '.Text::_('TESTLINE');
				} else {
					$tl= new TestLine();
					$tl->setName($testResultObj->getTestLine());
					// set build testdate to the createdate of test result
					$tl->setCreateDate($testResultObj->getCreateDate());
					// handle increment object
					$ret = $this->handleRecord($tl);
					if( $ret !== true )
					{
						return $ret;
					}
					// update tlid
					$tlid = PrecompiledQueries::getInstance()->isTestLine($tl->getName() );
				}
			} 
			// update tlid in testResultObj
			$testResultObj->setTlId($tlid);
		} else {
		// testcase is specified by tlid	
			// check if tlid exists in DB
			if(PrecompiledQueries::getInstance()->isTestLineByInx($testResultObj->getTlId()) === false) {
				$str = get_class()."::".__FUNCTION__.": tlid=".$testResultObj->getTlId().' '.Text::_('NOT_IN_DB');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				return "tlid=".$testResultObj->getTcId().' '.Text::_('NOT_IN_DB');
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
					$str = get_class()."::".__FUNCTION__.": ".$columnrealname.Text::_('NOT_DEFINED');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					return ": ".Text::_(strtoupper($columnrealname)).Text::_('NOT_DEFINED');
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
		$dbObjs = array(new Build(), new BuildIncrement(), new TestCase(), new TestSuite(), new TestLine(), new TestResult());
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

?>
