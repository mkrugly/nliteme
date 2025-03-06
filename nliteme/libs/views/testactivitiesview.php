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

class TestActivitiesView extends ConfigBasedView
{
	/*
	 * constructor
	 */ 
	public function __construct(TestActivitiesModel $model)
	{
		parent::__construct($model, 'testresults');
		// using wrapper template temporarily
		$this->setTemplate('test-activities-list.phtml');
		//$this->setTemplate('test-activities-list-wrapper.phtml');
		$this->link = new Links(array('action'=>$this->prefix.'.MainTestResults'));
	}
	
	/*
	 * function to perform show details action
	 */ 
	public function show()
	{
		$res = $this->model->show();
		$this->setContentHeader($this->prepareHeaderList($this->model->getHeaderColumns()));
		$this->setContentBody($this->model->getOutputArray());
		$this->setMessages($this->model->getMessages(), $res);
		$this->setContentTooltipHeader();
		$this->display();	
	}
	
	/*
	 * function sets link static arguments based on a given assoc array in form key=>value
	 */ 
	public function setLinkStaticArgs(array $assocArr)
	{
		$this->link->MergeToQueryUrl($assocArr);
	}	
	
	/*
	 * function prepares a sorted list of headers in form of DbColumnConfigList
	 * they will be used by the template for generating html table headers
	 */ 
	protected function prepareHeaderList(array $orderedColumnList = array())
	{
		$headers = null;
		$enabled = $this->getEnabledColumnList();
		if( empty($orderedColumnList) ) {
			$headers = $this->getEnabledColumnList();
		} else {
			$headers = new DbColumnConfigList();
			foreach($orderedColumnList as $value)
			{
				if( $enabled->getDbColumnByRealName($value) !== null) {
					$headers->addColumn($enabled->getDbColumnByRealName($value));
				} else {
					$headers->addColumn(new DbColumnConfig(array('realname' => $value, 'showable' => 'yes')));
				}
			}
		}
		return $headers;
	}
	
	/*
	 * function set the table header field in the content array
	 */ 	
	protected function setContentHeader(DbColumnConfigList $headers)
	{
		$this->content['header'] = $headers;
	}
	
	/*
	 * function set the table body field in the content array
	 */ 	
	protected function setContentBody($resultset)
	{
		$this->content['body'] = $resultset;
	}
	
	/*
	 * function set the tooltip column config
	 */ 	
	protected function setContentTooltipHeader()
	{
		$this->content['tooltip-header'] = $this->getEnabledColumnList()->getDbColumnByRealName('tcverdict');
	}
	
	/*
	 * protected function prepareContent()
	 */ 
	protected function prepareContent()
	{
	}
}
?>
