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
* 
*
* Uploader class handles upload process
* 
*
*/

abstract class RecordUploader
{
	private $dbRecords;
	private $usePrecompiledQuery;

	/* 
	 * constructor
	 */
	public function __construct(DbRecords& $dbRecords)
    {
		$this->dbRecords = $dbRecords;
		$this->usePrecompiledQuery = isset($_GET['useprecompiled']) ? true : false;
	}
	
	/* 
	 * destructor
	 */
	public function __destruct()
    {
	}
	
	/*
	 * function performs an upload procedure
	 * returns an UploadCallback object
	 */ 
	public function perform()
	{
		$callback = new UpdateCallback();
		if( $this->dbRecords->getJsonError() !== null ) {
			$callback->addCallbackMsg( $this->dbRecords->getJsonError() );
		} else {
			$managerCName = $this->getRecordManagerName();
			if($managerCName !== false) {
				$precompiledQueryToSet = null;
				// iterate through DbRecords and handle them
				$iterator = $this->dbRecords->getIterator();
				$recordInx = 0;
				while( $iterator->valid() )
				{
					// if precompiled Query from the previously handled dbRecord is valid set it for the current to speed up
					if( $precompiledQueryToSet !== null && $this->usePrecompiledQuery === true)
					{
						$iterator->current()->setCurrentQuery($precompiledQueryToSet);
					}
					// instantiate suitable RecordManager
					$current = $iterator->current();
					$manager = new $managerCName($current);
					// handle record
					$result = $manager->handleRecord();
					//print("handled: ".$recordInx."\n");
					// update counter on success and failure
					if( $result === true ) {
						// increment 
						$callback->addSuccess();
						// set precompiledQuery
						$precompiledQueryToSet = $iterator->current()->getCurrentQuery();
					} else {
						// try to reset the precompiled Query
						$precompiledQueryToSet = null;
						// add return string to callback list (index is an ) and increment failures count
						$callback->addFail();
						$callback->addCallbackMsg($result, $recordInx);
					}
					
					// continue with next dbRecord
					$recordInx++;
					$iterator->next();
				}
			} else {
				$str = get_class()."::".__FUNCTION__.": ".Text::_('UNSUPPORTED_UPLOAD_CONTENT');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				$callback->addCallbackMsg( Text::_('UNSUPPORTED_UPLOAD_CONTENT') );
			}
		}
		return $callback;
	}
	
	/*
	 * function returns RecordManager class name
	 * if class exists, otherwise false
	 */ 
	private function getRecordManagerName()
	{
		$pattern = '/RecordUploader/';
		$replacement = 'RecordManager';
		$subject = get_class($this);
		$recManCN = preg_replace($pattern, $replacement, $subject);
		if( !empty($recManCN) && class_exists($recManCN)  )
		{
			return $recManCN;
		} else {
			return false;
		}
	}
}

/*
 * Here are the classes using RecordUploader
 */
 
class BuildIncrementRecordUploader extends RecordUploader
{
	public function __construct($content)
	{
		if( $content instanceof BuildIncrements ) {
			parent::__construct($content);
		} else {
			$buildInc = new BuildIncrements($content);
			parent::__construct($buildInc);
        }
	}
}

class BuildRecordUploader extends RecordUploader
{
	public function __construct($content)
	{
		if( $content instanceof Builds ) {
			parent::__construct($content);
		} else {
			$build = new Builds($content);
			parent::__construct($build);
        }
	}
}

class TestSuiteRecordUploader extends RecordUploader
{
	public function __construct($content)
	{
		if( $content instanceof TestSuites ) {
			parent::__construct($content);
		} else {
			$testsuite = new TestSuites($content);
			parent::__construct($testsuite);
        }
	}
}

class TestCaseRecordUploader extends RecordUploader
{
	public function __construct($content)
	{
		if( $content instanceof TestCases ) {
			parent::__construct($content);
		} else {
			$testcase = new TestCases($content);
			parent::__construct($testcase);
        }
	}
}

class TestLineRecordUploader extends RecordUploader
{
	public function __construct($content)
	{
		if( $content instanceof TestLines ) {
			parent::__construct($content);
		} else {
			$testline = new TestLines($content);
			parent::__construct($testline);
        }
	}
}

class FeatureRecordUploader extends RecordUploader
{
	public function __construct($content)
	{
		if( $content instanceof Features ) {
			parent::__construct($content);
		} else {
			$testsuite = new Features($content);
			parent::__construct($testsuite);
		}
	}
}

class TestResultRecordUploader extends RecordUploader
{
	public function __construct($content)
	{
		if( $content instanceof TestResults ) {
			parent::__construct($content);
		} else {
			$testresult = new TestResults($content);
			parent::__construct($testresult);
        }
	}
}

///*
// * class used to handle upload callbacks 
// */ 
//class UpdateCallback extends JsonObject
//{
//	public function __construct($datastructure = null)
//	{
//		parent::__construct($datastructure);
//		// if $datastructure = null then set function is not called hence initialize params yourself
//		if( empty($datastructure) ) {
//		$this->setParam('numOfSuccess', 0);
//		$this->setParam('numOfFails', 0);
//		$this->setParam('numOfTotal', 0);
//		$this->setParam('callbackMsg', array() );
//		}
//	}
//	
//	/*
//	 * function defines how the object will look like
//	 */ 
//	protected function set($datastructure)
//	{
//		foreach ($datastructure AS $key => $value) 
//		{
//			$this->setParam($key, $value);
//		}	
//	}
//	
//	private function setTotal()
//	{
//		$this->setParam('numOfTotal', $this->getParam('numOfSuccess') + $this->getParam('numOfFails'));
//	}
//	
//	public function getTotal()
//	{
//		return $this->getParam('numOfTotal');
//	}
//	
//	public function setSuccess($numOfSuccess)
//	{
//		if( is_int($numOfSuccess) )
//		{
//			$this->setParam('numOfSuccess', $numOfSuccess);
//			$this->setTotal();
//		}
//	}
//	
//	public function getSuccess()
//	{
//		return $this->getParam('numOfSuccess');
//	}
//	
//	public function setFails($numOfFails)
//	{
//		if( is_int($numOfFails) )
//		{
//			$this->setParam('numOfFails', $numOfFails);
//			$this->setTotal();
//		}
//	}
//	
//	public function getFails()
//	{
//		return $this->getParam('numOfFails');
//	}
//	
//	public function addSuccess()
//	{
//		$this->setParam('numOfSuccess', $this->getParam('numOfSuccess') + 1);
//		$this->setTotal();
//	}
//	
//	public function addFail()
//	{
//		$this->setParam('numOfFails', $this->getParam('numOfFails') + 1);
//		$this->setTotal();
//	}
//	
//	public function setCallback(array $arrayOfMsgs)
//	{
//		$this->setParam('callbackMsg', $arrayOfMsgs);
//	}
//	
//	public function getCallback()
//	{
//		$this->getParam('callbackMsg');
//	}
//	
//	public function addCallbackMsg($msg, $entryInx = null)
//	{
//		if(! empty($msg) )
//		{
//			if(is_array($msg) )
//			{
//				$msg = implode("\n", $msg);
//			}
//			
//			if(is_string($msg))
//			{
//				$callback = $this->getParam('callbackMsg');
//				if( $entryInx === null ) {
//					array_push($callback, $msg);
//				} else {
//					$callback[$entryInx] = $msg;
//				}
//				$this->setParam('callbackMsg', $callback);
//			}
//		}
//	}
//}


?>
