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

abstract class ListView extends ConfigBasedView
{
	protected $pagination = null;		// Pagination object

	/*
	 * constructor
	 */ 
	public function __construct(ListModel $model, $columnConfigName)
	{
		parent::__construct($model,$columnConfigName);
		$this->setDetailActions();
		$this->setFieldActions();
	}
	
	/*
	 * function to perform show details action
	 */ 
	public function showList()
	{
		$res = $this->model->showList();
		$this->setMessages($this->model->getMessages(), $res);
		$this->setContentBody($this->model->getResultSet());
		$this->setNumberOfRecords($this->model->getNumberOfRecords());
		$this->prepareQuickLinks(); // adds quick links - if defined - to content body resultset 
		$this->prepareFieldActionLinks(); // adds field specific link actions to each record in the resultset
		$this->setContentFooter(new Pagination($this->link->GetCurrentUrl(), $this->model->getCurrentPage(), $this->model->getNumberOfPages()));
		$this->display();	
	}
	
	/*
	 * function to perform delete list action
	 */ 
	public function deleteList(array $indexList)
	{
		$res = $this->model->deleteList($indexList);
		$messList = $this->model->getMessages();
		if(empty($messList) && $res) {
			$messList = array(Text::_('TPL_DELETE').' '.Text::_('SUCCEEDED'));
		} 
		$this->setMessages($messList, $res);
		// removing delete action from action argument
		// TBD. find a better/cleaner way to handle this !!!!
		isset($_GET['action']) ? $_GET['action'] = $this->content['submitAction'] : null;
		// call showList action
		$this->showList();
		
	}
	
	/*
	 * function prepares a sorted list of headers in for of DbColumnConfigList
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
				if( $enabled->getDbColumnByRealName($value) !== null)
				{
					$headers->addColumn($enabled->getDbColumnByRealName($value));
				}
			}
		}
		return $headers;
	}
	
	/*
	 * function used to prepare Quick links if any defined
	 * Note. optional - child class specific
	 */
	protected function prepareQuickLinks()
	{
	}
    
	/*
	 * function used to prepare fields specific links if any defined
	 * Note. optional - child class specific
	 */
	protected function prepareFieldActionLinks()
	{
	}
	
	/*
	 * function sets the table header field in the content array
	 */ 	
	protected function setCheckBoxId($id_name)
	{
		$this->content['checkboxid'] = $id_name;
	}

	/*
	 * function sets the table header field in the content array
	 */ 	
	protected function setContentLabel($label)
	{
		$this->content['label'] = $label;
	}
	
	/*
	 * function sets the table header field in the content array
	 */ 	
	protected function setContentHeader(DbColumnConfigList $headers)
	{
		$this->content['header'] = $headers;
	}
	
	/*
	 * function sets the table body field in the content array
	 */ 	
	protected function setContentBody($resultset)
	{
		if($this->usejson === True && !is_array($resultset))
		{
			$resultset = $resultset->GetRows();
		}
		$this->content['body'] = $resultset;
	}
	
	/*
	 * function sets the table footer field in the content array
	 */ 	
	protected function setContentFooter($footer)
	{
		$this->content['footer'] = $footer;
	}

	/*
	 * function sets the list of quick links to be set in the quick links column
	 */ 	
	protected function setContentQuickLinks(array $quicklinks)
	{
		$this->content['quicklinks'] = $quicklinks;
	}
	   
	/*
	 * function sets the list of quick links to be set in the quick links column
	 */ 	
	protected function setNumberOfRecords($numOfRecords)
	{
		$this->content['numberofrecords'] = $numOfRecords;
	}
	
	/*
	 * returns a list of actions for details
	 */ 	
	protected function setDetailActions()
	{
		$this->content['detailActions'] =  array(  'buildid'  => 'action='.$this->prefix.'.MainBuilds.editDetails',
					   'incid' => 'action='.$this->prefix.'.MainBuildIncrements.editDetails',
					   'id'    => 'action='.$this->prefix.'.MainTestResults.editDetails',
					   'tcid'  => 'action='.$this->prefix.'.MainTestCases.editDetails',				   					   		
					   'tsid'  => 'action='.$this->prefix.'.MainTestSuites.editDetails',
					   'tlid'  => 'action='.$this->prefix.'.MainTestLines.editDetails',
					   'fid'   => 'action='.$this->prefix.'.MainFeatures.editDetails'
					);
	}
	
	protected function setFieldActions()
	{
		$this->content['fieldActions'] =  array('fname' => 'hlink');
		if (!empty(Config::getInstance()->getPreference('defect_otherserver_hlink')))
		{
			$this->content['fieldActions']['extracolumn_2'] = Config::getInstance()->getPreference('defect_otherserver_hlink');
		}
	}
    
	/*
	 * default implementation of parrent's abstract prepareContent() function
	 */ 
	protected function prepareContent()
	{
	}
}

/*
 * Here are the classes using DetailsView
 */
class BuildIncrementsView extends ListView
{
	/*
	 * constructor
	 */ 
	public function __construct(BuildIncrementsModel $model)
	{
		parent::__construct($model, 'build_increments');
		$this->setTemplate('main-content-table.phtml');
		$this->setCheckBoxId('incid');
		$this->setContentLabel('TPL_BUILD_INC');
		$headers = $this->prepareHeaderList(array('increment', 'createdate'));
		if( !empty($headers) )
		{
			$this->setContentHeader($headers);
		}
	}
}

class BuildsView extends ListView
{
	/*
	 * constructor
	 */ 
	public function __construct(BuildsModel $model)
	{
		parent::__construct($model, 'builds');
		$this->setTemplate('main-content-table.phtml');
		$this->setCheckBoxId('buildid');
		$this->setContentLabel('TPL_BUILDS');
		$headers = $this->prepareHeaderList(array('increment', 'build', 'createdate', 'testdate'));
		if( !empty($headers) )
		{
			$this->setContentHeader($headers);
		}
	}
}

class TestLinesView extends ListView
{
	/*
	 * constructor
	 */ 
	public function __construct(TestLinesModel $model)
	{
		parent::__construct($model, 'testlines');
		$this->setTemplate('main-content-table.phtml');
		$this->setCheckBoxId('tlid');
		$this->setContentLabel('TPL_TESTLINES');
		$headers = $this->prepareHeaderList(array('tlname', 'createdate'));
		if( !empty($headers) )
		{
			$this->setContentHeader($headers);
		}
	}
}

class TestSuitesView extends ListView
{
	/*
	 * constructor
	 */ 
	public function __construct(TestSuitesModel $model)
	{
		parent::__construct($model, 'testsuites');
		$this->setTemplate('main-content-table.phtml');
		$this->setCheckBoxId('tsid');
		$this->setContentLabel('TPL_TESTSUITES');
		$headers = $this->prepareHeaderList(array('tsname', 'createdate'));
		if( !empty($headers) )
		{
			$this->setContentHeader($headers);
		}
	}
}

class TestCasesView extends ListView
{
	/*
	 * constructor
	 */ 
	public function __construct(TestCasesModel $model)
	{
		parent::__construct($model, 'testcases');
		$this->setTemplate('main-content-table.phtml');
		$this->setCheckBoxId('tcid');
		$this->setContentLabel('TPL_TESTCASES');
		$headers = $this->prepareHeaderList(array('tcname', 'tsname', 'fname', 'coverage', 'createdate'));
		if( !empty($headers) )
		{
			$this->setContentHeader($headers);
		}
	}
}

class FeaturesView extends ListView
{
	/*
	 * constructor
	 */ 
	public function __construct(FeaturesModel $model)
	{
		parent::__construct($model, 'features');
		$this->setTemplate('main-content-table.phtml');
		$this->setCheckBoxId('fid');
		$this->setContentLabel('TPL_FEATURES');
		$headers = $this->prepareHeaderList(array('fid', 'fname', 'createdate', 'num_testcases', 'coverage'));
		if( !empty($headers) )
		{
			$this->setContentHeader($headers);
		}
        // define a list of quick links fields
		$this->setContentQuickLinks(array('hlink'));
	}
	
	protected function setFieldActions()
	{
		$this->content['fieldActions'] =  array('num_testcases' => 'action_num_testcases', 
												'coverage' => 'action_coverage');
	}
    
	/*
	 * function used to prepare field specific links for each record in case needed
	 * Note. optional - child class specific
	 */
	protected function prepareFieldActionLinks()
	{
		$outputArray = array();
		foreach($this->model->getResultSet() as $row)
		{
			foreach($this->content['fieldActions'] as $field => $action)
			{
				// if field for which to set the link isset add a corresponding link field
                if(isset($row[$field]) && !isset($row[$action]) )
				{
					$fractions = explode('.', $this->content['submitAction']);
                    $fractions[2] = 'MainTestCases';
                    $urlArgs = array('action'=>implode('.', $fractions), 'fid'=>$row['fid']);
					$link = $this->link->GetServerUrl();
					$link .= $this->link->GetScriptName();
					$link .= '?'.http_build_query($urlArgs);
					$row[$action] = $link;
				}
			}
			array_push($outputArray,$row);
		}
		$this->setContentBody($outputArray);
	}
}

class TestResultsView extends ListView
{
	private $historyArgs = array();
	/*
	 * constructor
	 */ 
	public function __construct(TestResultsModel $model)
	{
		parent::__construct($model, 'testresults');
		$this->setTemplate('main-content-table.phtml');
		//$this->setTemplate('testresultlist.phtml');
		$this->setCheckBoxId('id');
		$this->setContentLabel('TPL_TESTRESULTS');
		$headers = $this->prepareHeaderList(array('filepath', 'tcname', 'increment', 'build', 'tsname', 'tcverdict', 'tlname', 'fname', 'coverage', 'extracolumn_0', 'extracolumn_2', 'createdate'));
		if( !empty($headers) )
		{
			$this->setContentHeader($headers);
		}
		// define a list of quick links fields
		$this->setContentQuickLinks(array('filepath','history')); 
		// define a list of table columns used for generating test results history
		$this->historyArgs = array('tcid', 'incid', 'tsid', 'tlid', 'extracolumn_0');
	}
	
	/*
	 * function used to prepare Quick links if any defined
	 * Note. optional - child class specific
	 */
	protected function prepareQuickLinks()
	{
		if(! empty($this->content['quicklinks']) )
		{
			$outputArray = array();
			foreach($this->model->getResultSet() as $row)
			{
				foreach($this->content['quicklinks'] as $quicklink)
				{
					if(! isset($row[$quicklink]))
					{
						// handle history links
						if($quicklink == 'history')
						{
							$row[$quicklink] = $this->getHistoryLink($row);
						}
					}
				}
				array_push($outputArray,$row);
			}
			$this->setContentBody($outputArray);
		}
	}
	
	/*
	 * function prepares a link to a list of historical test results based on a given row
	 */ 
	private function getHistoryLink(array $row)
	{
		$quicklink = '';
		$urlArgs = array();
		// prepare arguments for query URL 
		foreach($this->historyArgs as $arg)
		{
			if(isset($row[$arg]))
			{
				$urlArgs[$arg] = $row[$arg];
			}
		}
		// if conditions are found
		if(!empty($urlArgs))
		{
			// set proper action
			$fractions = explode('.', $this->content['submitAction']);
			$fractions[2] = isset($fractions[2]) ?  'Main'.$fractions[2] : $fractions[2];
			$urlArgs['action'] = implode('.', $fractions);
			// sort by createdate
			$urlArgs['sort'] = 'createdate';
			$urlArgs['order'] = 'DESC';
			// preapre link
			$quicklink = $this->link->GetServerUrl();
			$quicklink .= $this->link->GetScriptName();
			$quicklink .= '?'.http_build_query($urlArgs);
		}
		return $quicklink;
	}
}

class TsTcMapView extends ListView
{
	/*
	 * constructor
	 */ 
	public function __construct(TsTcMapModel $model)
	{
		parent::__construct($model, 'testsuites');
		$this->setTemplate('main-content-table.phtml');
		//$this->setCheckBoxId('tsid');
		$this->setContentLabel('TPL_TESTSUITES_TESTCASES');
		$headers = $this->prepareHeaderList(array('tsname', 'tcname'));
		if( !empty($headers) )
		{
			$this->setContentHeader($headers);
		}
	}
}
 
?>
