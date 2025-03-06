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

abstract class DetailsView extends ConfigBasedView
{
	protected $label = null; 	// holds a label e.g. TESTRESULT
	protected $action = null; 	// holds action state

	/*
	 * constructor
	 */ 
	public function __construct(DetailsModel $model, $columnConfigName, $label)
	{
		parent::__construct($model,$columnConfigName);
		$this->label = $label;
	}
	
	/*
	 * function to perform show details action
	 */ 
	public function showDetails($index)
	{
		$this->action = 'showDetails';
		$res = $this->model->showDetails($index);
		$this->setMessages($this->model->getMessages(), $res);
		$this->display();	
	}
	
	/*
	 * function to perform edit (insert/update) action
	 */ 
	public function editDetails($index)
	{
		$this->action = 'editDetails';
		$res = $this->model->editDetails($index);
		$this->setMessages($this->model->getMessages(), $res);
		$this->display();	
	}
	
	/*
	 * function to perform save action
	 */ 
	public function saveDetails(array $postParams)
	{
		$this->action = 'saveDetails';
		$res = $this->model->saveDetails($postParams);
		//$this->setMessages($this->model->getMessages(), $res);
		$this->editDetails($this->model->getDbRecord()->getId());
	}
	
	/*
	 * function to perform delete action
	 */ 
	public function deleteDetails($index)
	{
		$this->action = 'deleteDetails';
		$res = $this->model->deleteDetails($index);
		//$this->setMessages($this->model->getMessages(), $res);
		$this->editDetails($this->model->getDbRecord()->getId());
	}
	
	/*
	 * function prepares common content
	 */ 
	protected function prepareCommonContent()
	{
		// merge configuration and model data first
		$this->prepareEnabledColumnListContent();
		
		$this->content['label'] = $this->label;
		$this->content['action'] = $this->action;
		$this->content['id'] = $this->model->getDbRecord()->getIdColumn();
		$this->content['name'] = $this->model->getDbRecord()->getNameColumn();
		$this->content['itercolumns'] = $this->getIteratableColumnList();
		if($this->getEnabledColumnList()->getDbColumnByRealName('description') != false)
		{
			$desc = $this->getEnabledColumnList()->getDbColumnByRealName('description')->getColumnValue();
			//decompression moved to the detailsmodel
			//$this->content['description'] = Utils::emptystr($desc) ? '' : gzuncompress($desc);
			$this->content['description'] = $desc;
		}
		if($this->getEnabledColumnList()->getDbColumnByRealName('shortdescription') != false)
		{
			$this->content['shortdescription'] = $this->getEnabledColumnList()->getDbColumnByRealName('shortdescription')->getColumnValue();
		}
	}

	/*
	 * function prepares enabled column content acording to configuration and model
	 */ 	
	protected function prepareEnabledColumnListContent()
	{
		$iterator = $this->getEnabledColumnList()->getIterator();
		while( $iterator->valid() )
		{
			$current = $iterator->current();
			$this->prepareColumnContent($current);
			$iterator->next();
		}
	}
	
	/*
	 * function merges the model and configuration data to form a content
	 */ 	
	protected function prepareColumnContent(DbColumnConfig& $column)
	{
		// if we have index=>value relation
		if ( $column->getColumnIndex() != false ) {
			$index = $this->model->getDbRecord()->getDbColumnValue($column->getColumnIndex());
			$value = $this->model->getDbRecord()->getDbColumnValue($column->getColumnRealName());
			// update the 'values' field
			if( $column->hasPredefinedValues() === false && ! empty($index) && ! empty($value) )
			{
				$tmpArr = array($index=>$value);
				$column->setColumnPredefinedValues($tmpArr);
			}
			$column->setColumnValue($value);
		} else {
			$column->setColumnValue($this->model->getDbRecord()->getDbColumnValue($column->getColumnRealName()));
			//print_r($column);
			//print('<br>');
		}
	}
    
	/*
	 * prepares view specific connent - to be overridden in the child class
	 */ 
	protected function prepareSpecificContent()
	{
	}
	
	/*
	 * default implementation of parrent's abstract prepareContent() function
	 */ 
	protected function prepareContent()
	{
		$this->prepareCommonContent();
		$this->prepareSpecificContent();
	}
}

/*
 * Here are the classes using DetailsView
 */

class BuildIncrementView extends DetailsView
{
	/*
	 * constructor
	 */ 
	public function __construct(BuildIncrementModel $model)
	{
		parent::__construct($model, 'build_increments', 'BUILD_INCREMENT');
		$this->setTemplate('test-result-details.phtml');
	}
}

class BuildView extends DetailsView
{
	/*
	 * constructor
	 */ 
	public function __construct(BuildModel $model)
	{
		parent::__construct($model, 'builds', 'BUILD');
		$this->setTemplate('test-result-details.phtml');
	}
	
	protected function prepareSpecificContent()
	{
		$this->prepareRelatedDashboard();
	}
	
	private function prepareRelatedDashboard()
	{
		if(!empty($this->model->getDbRecord()->getId()))
		{
			$idColumn = $this->model->getDbRecord()->getIdColumn();
			$tclink = new Links(array('action'=>$this->prefix.'.MainHighLevelReport', $idColumn->getColumnRealName() => $idColumn->getColumnValue(), 'groupby' => 'fname', 'nonav' => 1));
			$url = $tclink->GetServerUrl().$tclink->GetScriptName().'?'.$tclink->GetQueryUrl();
			$dashboardConfigString = '{"dashboardcolumnlist":{"0":{"widgetconfiglist":{"0":{"name":"Covered Features","url":"'.$url.'","useiframe":"yes","title":"Associated Features"}}}},"name":"Build Other Details"}';
			$this->content['config'] = new DashboardTab($dashboardConfigString);
			$this->content['related'] = array();
		}
	}
}

class TestLineView extends DetailsView
{
	/*
	 * constructor
	 */ 
	public function __construct(TestLineModel $model)
	{
		parent::__construct($model, 'testlines', 'TESTLINE');
		$this->setTemplate('test-result-details.phtml');
	}
}

class TestSuiteView extends DetailsView
{
	/*
	 * constructor
	 */ 
	public function __construct(TestSuiteModel $model)
	{
		parent::__construct($model, 'testsuites', 'TESTSUITE');
		$this->setTemplate('test-result-details.phtml');
	}
}

class TestCaseView extends DetailsView
{
	/*
	 * constructor
	 */ 
	public function __construct(TestCaseModel $model)
	{
		parent::__construct($model, 'testcases', 'TESTCASE');
		$this->setTemplate('test-result-details.phtml');
	}
}

class FeatureView extends DetailsView
{
	/*
	 * constructor
	 */ 
	public function __construct(FeatureModel $model)
	{
		parent::__construct($model, 'features', 'FEATURE');
		$this->setTemplate('test-result-details.phtml');
	}
    
	/*
	 * prepares related connent which is derived from other tables
	 * the related connect shall be defined under the $this->content['related_tabs'] 
	 */ 
	protected function prepareSpecificContent()
	{
		$this->prepareRelatedColumnConfig();
		$this->prepareRelatedDashboard();
	}
	
	private function prepareRelatedColumnConfig()
	{
		$dbColumnConfig = new DbColumnConfigList();
		foreach (array('coverage') as $columnRealName)
		{
			$value = $this->model->getDbRecord()->getDbColumnValue($columnRealName);
			if($this->getColumnConfig()->getDbColumnByRealName($columnRealName) !== null)
			{
				$column = clone $this->getColumnConfig()->getDbColumnByRealName($columnRealName);
				$column->setColumnValue($value);
				$dbColumnConfig->addColumn($column);
			} else {
				$dbColumnConfig->addColumn(new DbColumnConfig(array('realname' => $columnRealName, 'value' => $value)));
			}
		}
		$this->content['related']['columns'] = $dbColumnConfig;
	}
	
	private function prepareRelatedDashboard()
	{
		if(!empty($this->model->getDbRecord()->getId()) && !empty($this->model->getDbRecord()->getDbColumnValue('num_testcases')))
		{
			$idColumn = $this->model->getDbRecord()->getIdColumn();
			$num_tcs = $this->model->getDbRecord()->getDbColumnValue('num_testcases');
			$tclink = new Links(array('action'=>$this->prefix.'.MainTestCases', $idColumn->getColumnRealName() => $idColumn->getColumnValue(), 'nonav' => 1));
			$url = $tclink->GetServerUrl().$tclink->GetScriptName().'?'.$tclink->GetQueryUrl();
			$dashboardConfigString = '{"dashboardcolumnlist":{"0":{"widgetconfiglist":{"0":{"name":"Associated Test Cases","url":"'.$url.'","useiframe":"yes","title":"Associated Test Cases: '.$num_tcs.'"}}}},"name":"Feature Other Details"}';
			$this->content['config'] = new DashboardTab($dashboardConfigString);
		}
	}
}

class TestResultView extends DetailsView
{
	/*
	 * constructor
	 */ 
	public function __construct(TestResultModel $model)
	{
		parent::__construct($model, 'testresults', 'TESTRESULT');
		$this->setTemplate('test-result-details.phtml');
	}
	
	/*
	 * protected function prepareContent()
	 */ 
	protected function prepareSpecificContent()
	{
		$this->content['status'] = $this->getEnabledColumnList()->getDbColumnByRealName('tcverdict');
	}
}
?>
