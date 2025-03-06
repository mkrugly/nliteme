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
* MassUploader class handles mass upload of huge amount of data of the same type (e.g. huge amout of test results) based on a given input file
* The data shall be provided in a separate json encoded strings in a separate lines, so that is it possible to parse the file line by line.
* e.g. each TestResult has to be json encoded and save in a separate line in a file
* 
* Note. The reasoning for using this iterative approach (instead of using clasic Uploader class) is that in this case there will be relatively 
* small object (or serveral objects) created for each line in a file (one DbRecord), whereas in the other case there would have to be a huge object 
* created for one huge decoded json string (DbRecords object containing large number of DbRecord objects), which (dependent of file size) might use 
* all memory available for php (see php.ini)
* 
* Constructor arguments:
* $filename - fully qualified to a file to be processed
* $realfilename - if $filename is different to realfilename (usefull to get the callback with realfilename if something wrong happens) 
*
*/

class MassUploader
{
	private $filename;
	private $realfilename;
	private $usePrecompiledQuery;

	/* 
	 * constructor
	 */
	public function __construct($filename, $realfilename=null)
    {
		$this->filename = $filename;
		$this->realfilename = $realfilename;
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
		if ($fh = fopen($this->filename, "r")) {
			$managerCName = $this->getRecordManagerName();
			$dbRecordCName = $this->getDbRecordName();
			if($managerCName !== false && $dbRecordCName !== false) {
				$precompiledQueryToSet = null;
				$linenumber = 0;
				while (!feof($fh)) 
				{
					$record = null;
					$linenumber++;
					$line = fgets($fh);				
					$record = new $dbRecordCName($line);
					
					// was decode successfull?
					if($record->getJsonError() !== null) {
						$callback->addCallbackMsg(Text::_('FILE_LINE').$linenumber.': '.$record->getJsonError());
					} else {		
						// if precompiled Query from the previously handled dbRecord is valid set it for the current to speed up
						if( $precompiledQueryToSet !== null  && $this->usePrecompiledQuery === true)
						{
							$record->setCurrentQuery($precompiledQueryToSet);
						}
						// instantiate suitable RecordManager
						$manager = new $managerCName($record);
						// handle record
						$result = $manager->handleRecord();
						//print("handled: ".$recordInx."\n");
						// update counter on success and failure
						if( $result === true ) {
							// increment 
							$callback->addSuccess();
							// set precompiledQuery
							$precompiledQueryToSet = $record->getCurrentQuery();
						} else {
							// try to reset the precompiled Query
							$precompiledQueryToSet = null;
							// add return string to callback list (index is an ) and increment failures count
							$callback->addFail();
							$callback->addCallbackMsg(Text::_('FILE_LINE').$linenumber.': '.$result);
						}
					}
				}
			} else {
				$str = get_class()."::".__FUNCTION__.": ".Text::_('UNSUPPORTED_UPLOAD_CONTENT');
				Tracer::getInstance()->log($str, LOGLEV_ERROR);
				$callback->addCallbackMsg( Text::_('UNSUPPORTED_UPLOAD_CONTENT') );
			}							
			
			fclose($fh);
		} else {
			$str = get_class()."::".__FUNCTION__.": ".Text::_('CANNOT_OPEN_FILE').': '.(( empty($this->realfilename)) ? $this->filename : $this->realfilename);
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			$callback->addCallbackMsg( Text::_('CANNOT_OPEN_FILE').': '.(( empty($this->realfilename)) ? $this->filename : $this->realfilename) );
		}
		
		return $callback;
	}
	
	/*
	 * function returns RecordManager class name
	 * if class exists, otherwise false
	 */ 
	private function getRecordManagerName()
	{
		$pattern = '/MassUploader/';
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

	/*
	 * function returns RecordManager class name
	 * if class exists, otherwise false
	 */	
	private function getDbRecordName()
	{
		$pattern = '/MassUploader/';
		$replacement = '';
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
 * Here are the classes using MassUploader
 */
class TestResultMassUploader extends MassUploader
{
	public function __construct($filename, $realfilename=null)
	{
		parent::__construct($filename, $realfilename);
	}
}

class BuildMassUploader extends MassUploader
{
	public function __construct($filename, $realfilename=null)
	{
		parent::__construct($filename, $realfilename);
	}
}

class TestSuiteMassUploader extends MassUploader
{
	public function __construct($filename, $realfilename=null)
	{
		parent::__construct($filename, $realfilename);
	}
}

class TestCaseMassUploader extends MassUploader
{
	public function __construct($filename, $realfilename=null)
	{
		parent::__construct($filename, $realfilename);
	}
}

class TestLineMassUploader extends MassUploader
{
	public function __construct($filename, $realfilename=null)
	{
		parent::__construct($filename, $realfilename);
	}
}

class BuildIncrementMassUploader extends MassUploader
{
	public function __construct($filename, $realfilename=null)
	{
		parent::__construct($filename, $realfilename);
	}
}

class FeatureMassUploader extends MassUploader
{
	public function __construct($filename, $realfilename=null)
	{
		parent::__construct($filename, $realfilename);
	}
}

?>
