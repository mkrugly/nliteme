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

	require 'libs/include.php';
   
	/*
	* 
	*/ 
	//$link = new Links();
	
	//DbConnectionFactory::getFactory()->getConnection()->debug = true;
	//Tracer::getInstance()->setTraceLevel(LOGLEV_ERROR);
	if(! empty($_POST) && ! empty($_POST['uploadtype']) )
	{
		$callback = new UpdateCallback();
		if(! empty($_FILES['file'])) {
			switch($_FILES['file']['error'])
			{
				case UPLOAD_ERR_OK:
					if (is_uploaded_file($_FILES['file']['tmp_name']) && $_FILES['file']['type'] === 'application/json') {
						//$content = file_get_contents($_FILES['file']['tmp_name']);
						
						// call suitable MassUploader
						if( $_POST['uploadtype']  === 'testresults' ) {
							$uploader = new TestResultMassUploader($_FILES['file']['tmp_name'], $_FILES['file']['name'] );
							$callback = $uploader->perform();
						} else if ( $_POST['uploadtype']  === 'builds' ) {
							$uploader = new BuildMassUploader($_FILES['file']['tmp_name'], $_FILES['file']['name'] );
							$callback = $uploader->perform();
						} else if ( $_POST['uploadtype']  === 'testcases' ) {
							$uploader = new TestCaseMassUploader($_FILES['file']['tmp_name'], $_FILES['file']['name'] );
							$callback = $uploader->perform();
						} else if ( $_POST['uploadtype']  === 'buildincrements' ) {
							$uploader = new BuildIncrementMassUploader($_FILES['file']['tmp_name'], $_FILES['file']['name'] );
							$callback = $uploader->perform();
						} else if ( $_POST['uploadtype']  === 'testsuites' ) {
							$uploader = new TestSuiteMassUploader($_FILES['file']['tmp_name'], $_FILES['file']['name'] );
							$callback = $uploader->perform();
						} else if ( $_POST['uploadtype']  === 'testlines' ) {
							$uploader = new TestLineMassUploader($_FILES['file']['tmp_name'], $_FILES['file']['name'] );
							$callback = $uploader->perform();
						} else if ( $_POST['uploadtype']  === 'features' ) {
							$uploader = new FeatureMassUploader($_FILES['file']['tmp_name'], $_FILES['file']['name'] );
							$callback = $uploader->perform();
						} else {
							$callback->addCallbackMsg(Text::_('UNSUPPORTED_FORM_FIELD_VALUE').": uploadtype");
						}
					}
					break;
				case UPLOAD_ERR_INI_SIZE:
					$str = basename(__FILE__).": ".Text::_('UPLOAD_ERR_INI_SIZE');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					$callback->addCallbackMsg(Text::_('UPLOAD_ERR_INI_SIZE'));
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$str = basename(__FILE__).": ".Text::_('UPLOAD_ERR_FORM_SIZE');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					$callback->addCallbackMsg(Text::_('UPLOAD_ERR_FORM_SIZE'));
					break;
				case UPLOAD_ERR_PARTIAL:
					$str = basename(__FILE__).": ".Text::_('UPLOAD_ERR_PARTIAL');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					$callback->addCallbackMsg(Text::_('UPLOAD_ERR_PARTIAL'));
					break;
				case UPLOAD_ERR_NO_FILE:
					$str = basename(__FILE__).": ".Text::_('UPLOAD_ERR_NO_FILE');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					$callback->addCallbackMsg(Text::_('UPLOAD_ERR_NO_FILE'));
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$str = basename(__FILE__).": ".Text::_('UPLOAD_ERR_NO_TMP_DIR');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					$callback->addCallbackMsg(Text::_('UPLOAD_ERR_NO_TMP_DIR'));
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$str = basename(__FILE__).": ".Text::_('UPLOAD_ERR_CANT_WRITE');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					$callback->addCallbackMsg(Text::_('UPLOAD_ERR_CANT_WRITE'));
					break;
				case UPLOAD_ERR_EXTENSION:
					$str = basename(__FILE__).": ".Text::_('UPLOAD_ERR_EXTENSION');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					$callback->addCallbackMsg(Text::_('UPLOAD_ERR_EXTENSION'));
					break;
				default:
					$str = basename(__FILE__).": UPLOAD ".Text::_('UNKNOWN_ERROR');
					Tracer::getInstance()->log($str, LOGLEV_ERROR);
					$callback->addCallbackMsg(": UPLOAD ".Text::_('UNKNOWN_ERROR'));
					break;
			}
			//$callback->addCallbackMsg($_FILES['file']['tmp_name']);
			//$callback->addCallbackMsg($_FILES['file']['type']);
			//$callback->addCallbackMsg($_FILES['file']['name']);
		} else if (! empty($_POST['jsonstring']) ) {
			// handle a form with jsonstring
			$content = $_POST['jsonstring'];
		} else {
			$callback->addCallbackMsg(Text::_('NO_FILE_OR_NO_JSONSTRING'));
		}
		
		// upload if there is a content
		if(! empty($content) )
		{
			// temporarily override the php memory limit to make sure there is enough memory
			ini_set('memory_limit', '512M'); 
			// handle test results upload
			if( $_POST['uploadtype'] === 'testresults' ) {
				$uploader = new TestResultRecordUploader($content);
				$callback = $uploader->perform();
			} else if ( $_POST['uploadtype'] === 'builds' ) {
			// handle builds upload
				$uploader = new BuildRecordUploader($content);
				$callback = $uploader->perform();
			} else if ( $_POST['uploadtype'] === 'testcases' ) {
			// handle test cases upload
				$uploader = new TestCaseRecordUploader($content);
				$callback = $uploader->perform();
			} else if ( $_POST['uploadtype'] === 'buildincrements' ) {
			// handle TestSuites upload
				$uploader = new BuildIncrementRecordUploader($content);
				$callback = $uploader->perform();
			} else if ( $_POST['uploadtype'] === 'testsuites' ) {
			// handle TestSuites upload
				$uploader = new TestSuiteRecordUploader($content);
				$callback = $uploader->perform();
			} else if ( $_POST['uploadtype'] === 'testlines' ) {
			// handle TestLines upload
				$uploader = new TestLineRecordUploader($content);
				$callback = $uploader->perform();
			} else if ( $_POST['uploadtype'] === 'features' ) {
			// handle Features upload
				$uploader = new FeatureRecordUploader($content);
				$callback = $uploader->perform();
			} else {
				$callback->addCallbackMsg(Text::_('UNSUPPORTED_FORM_FIELD_VALUE').": uploadtype");
			}
		}
		//print_r(Tracer::getInstance()->getTraces());
		header('Content-type: application/json');
		print($callback->encode()."\n");
	} else {
	// for testing purposes
		$callback = new UpdateCallback();
		if($argc > 1 && file_exists($argv[1]) )
		{
			$str = basename(__FILE__).": TEST RUN \n";
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			$uploader = new TestResultMassUploader($argv[1]);
			$callback = $uploader->perform();
		}
		print_r(Tracer::getInstance()->getTraces());
		print($callback->encode()."\n");
	}
	
?>
