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

/*****
 * class HighLevelReportListView prepares a view for pass rate per build, test suite
 *****/
class HighLevelReportView extends ConfigBasedView
{
	/*
	 * constructor
	 */ 
	public function __construct(HighLevelReportModel $model)
	{
		parent::__construct($model, 'testresults');
		// using wrapper template temporarily
		$this->setTemplate('hlr-tabnoscroll-pbar-ttipdynamic.phtml');
		$this->link = new Links(array('action'=>$this->prefix.'.MainHighLevelReportDetail'));
		$this->setContentLabel('TPL_HIGHLEVREPORTFORBUILDS');
		$this->linkStaticArgs = array();
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
		//$this->setNumberOfRecords(count($this->model->getOutputArray()));	
		$this->setContentFooter(new PaginationAjax( new Links(), $this->model->getCurrentPage(), $this->model->getNumberOfPages()));
		$this->setContentTooltipAction();
		$this->setContentQuickLinks();
		$this->setDetailActions();
		$this->setFieldActions();
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
	
	protected function setContentQuickLinks()
	{
		$this->content['quicklinks'] = array('list', 'details');
		$this->content['list-quicklink_common'] = new Links(array('action'=>$this->prefix.'.MainTestResults'));
	}
	
	/*
	 * function set the tooltip column config
	 */ 	
	protected function setContentTooltipAction()
	{
		$this->content['tooltip-action'] = new Links($this->link->GetQueryUrlArray());
		$this->content['tooltip-action']->MergeToQueryUrl(array('action'=>$this->prefix.'.HighLevelReportDetailPassRateTooltip'));
	}

	/*
	 * function sets the table header field in the content array
	 */ 	
	protected function setContentLabel($label)
	{
		$this->content['label'] = $label;
	}

	/*
	 * function sets the list of quick links to be set in the quick links column
	 */ 	
	protected function setNumberOfRecords($numOfRecords)
	{
		$this->content['numberofrecords'] = $numOfRecords;
	}
	
	/*
	 * function sets the table footer field in the content array
	 */ 	
	protected function setContentFooter($footer)
	{
		$this->content['footer'] = $footer;
	}	
	
	/*
	 * protected function prepareContent()
	 */ 
	protected function prepareContent()
	{
	}
	
	protected function setDetailActions()
	{
		$this->content['detailActions'] =  array(  'buildid'  => 'action='.$this->prefix.'.MainBuilds.editDetails',
					   'incid' => 'action='.$this->prefix.'.MainBuildIncrements.editDetails',
					   'id'    => 'action='.$this->prefix.'.MainTestResults.editDetails',
					   'tcid'  => 'action='.$this->prefix.'.MainTestCases.editDetails',				   					   		
					   'tsid'  => 'action='.$this->prefix.'.MainTestSuites.editDetails',
					   'tlid'  => 'action='.$this->prefix.'.MainTestLines.editDetails',
					   'fid'  => 'action='.$this->prefix.'.MainFeatures.editDetails'
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
}

/*****
 * class HighLevelReportDetailPassRateView prepares a view for pass rate per test line
 *****/
class HighLevelReportDetailPassRateView extends ConfigBasedView
{
	/*
	 * constructor
	 */ 
	public function __construct(HighLevelReportDetailPassRateModel $model)
	{
		parent::__construct($model, 'testresults');
		// using wrapper template temporarily
		$this->setTemplate('hlr-tabscroll-pbar-ttipstatic.phtml');
		$this->link = new Links(array('action'=>$this->prefix.'.MainTestResults'));
		//$this->setContentLabel('TPL_HLR_PASSRATEPERTESTLINE');
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
		$this->setContentQuickLinks();
		$this->setDetailActions();
		$this->setFieldActions();
		$this->display();	
	}
	
	/*
	 * function sets link static arguments based on a given assoc array in form key=>value
	 */ 
	public function setLinkStaticArgs(array $assocArr)
	{
		$this->link->MergeToQueryUrl($assocArr);
	}
	
	protected function setContentQuickLinks()
	{
		$this->content['quicklinks'] = array('list');
		$this->content['list-quicklink_common'] = $this->link;
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
		//$this->content['tooltip-header'] = '';
		$this->content['tooltip-header'] = $this->getEnabledColumnList()->getDbColumnByRealName('tcverdict');
	}

	/*
	 * function sets the table header field in the content array
	 */ 	
	protected function setContentLabel($label)
	{
		$this->content['label'] = $label;
	}	
	
	/*
	 * protected function prepareContent()
	 */ 
	protected function prepareContent()
	{
	}
	
	protected function setDetailActions()
	{
		$this->content['detailActions'] =  array(  'buildid'  => 'action='.$this->prefix.'.MainBuilds.editDetails',
					   'incid' => 'action='.$this->prefix.'.MainBuildIncrements.editDetails',
					   'id'    => 'action='.$this->prefix.'.MainTestResults.editDetails',
					   'tcid'  => 'action='.$this->prefix.'.MainTestCases.editDetails',				   					   		
					   'tsid'  => 'action='.$this->prefix.'.MainTestSuites.editDetails',
					   'tlid'  => 'action='.$this->prefix.'.MainTestLines.editDetails',
					   'fid'  => 'action='.$this->prefix.'.MainFeatures.editDetails'
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
}

/*****
 * class HighLevelReportDetailPassRateView prepares a view for pass rate per test line
 *****/
class HighLevelReportDetailPassRateTooltipView extends HighLevelReportDetailPassRateView
{
	private $tooltipHeaders = array('tlname','extracolumn_0','totalcount', 'passrate');
	/*
	 * constructor
	 */ 
	public function __construct(HighLevelReportDetailPassRateTooltipModel $model)
	{
		parent::__construct($model);
		$this->setTemplate('hlr-tabnoscroll-ttipstatic.phtml');
	}	
	
	/*
	 * function prepares a sorted list of headers in form of DbColumnConfigList
	 * they will be used by the template for generating html table headers
	 */ 
	protected function prepareHeaderList(array $orderedColumnList = array())
	{
		// strip column to
		$tooltipColumnList = array();
		foreach($orderedColumnList as $value)
		{
			if (in_array($value, $this->tooltipHeaders))
			{
				array_push($tooltipColumnList, $value);
			}
		}
		return parent::prepareHeaderList($tooltipColumnList);
	}
}

/*****
 * class HighLevelReportDetailPassRateView prepares a view for pass rate per test line
 *****/
class HighLevelReportDetailDefectTLView extends HighLevelReportDetailPassRateView
{
	protected function prepareHeaderList(array $orderedColumnList = array())
	{
		$headers = parent::prepareHeaderList($orderedColumnList);
		if (isset($headers)) {
			$headers->delColumn('passrate');
		}
		return $headers;
	}
}

/*****
 * class HighLevelReportSearcherView prepares a view for the HLR search form
 *****/
class HighLevelReportSearcherView extends SearcherView
{
	private $hideGroupByField = True;
		
	public function __construct(HighLevelReportSearcherModel $model)
	{
		parent::__construct($model);
		$this->preselectFromGet = true;
		$this->setTemplate('searcher-hlr.phtml');
	}

	protected function prepareContent()
	{
		//if($this->preselectFromGet === true)
		//{
		//	$groupByDbColumn = $this->modelInputsToProcess->getDbColumnByRealName('groupby');
		//	if(isset($groupByDbColumn))
		//	{
		//		// first set default value to be first form predefined
		//		$defaultValue = $groupByDbColumn->getColumnPredefinedValues();
		//		if(!empty($defaultValue))
		//		{
		//			$groupByDbColumn->setColumnValue($defaultValue);
		//		}
		//		// override default value with the one from $GET
		//		$this->setColumnValueFromGet($groupByDbColumn);
		//		$this->modelInputsToProcess->addColumn($groupByDbColumn);
		//	}
		//}
		parent::prepareContent();
		//$this->content['groupby'] = $this->content['inputs']->getDbColumnByRealName('groupby');
		$this->removeFromInput();
		$this->updateSubmitQueryUrl(True);
	}
	
	private function removeFromInput()
	{
		$allowedGroupBy = array('tsname', 'fname', 'groupby', 'extracolumn_2');
		$groupby = array('tsname', 'fname');
		if(isset($this->getParamsToProcess['groupby']))
		{
			$groupby = is_array($this->getParamsToProcess['groupby']) ? $this->getParamsToProcess['groupby'] : array($this->getParamsToProcess['groupby']);
		}
		foreach(array_diff($allowedGroupBy, $groupby) as $columnToDelete)
		{
			$this->content['inputs']->delColumn($columnToDelete);
		}
	}
}

/*****
 * class HighLevelReportDetailVerdictView prepares a view for the HLR Detail Verdict form
 *****/
class HighLevelReportDetailVerdictView extends ListView
{
	public function __construct(HighLevelReportDetailVerdictModel $model)
	{
		parent::__construct($model, 'testresults');
		$this->setTemplate('hlr-tabnoscroll-pbar-ttipdynamic.phtml');
		
		$this->setContentLabel('TPL_HLR_TCVERDICTSUMMARY');
		$headers = $this->prepareHeaderList(array('tcname', 'increment', 'build', 'tsname', 'fname', 'coverage', 'tcverdict'));
		if( !empty($headers) )
		{
			$this->setContentHeader($headers);
		}
		$this->link = new Links(array('action'=>$this->prefix.'.MainTestResults'));
	}
	
	public function showList()
	{
		$res = $this->model->showList();
		$this->setMessages($this->model->getMessages(), $res);
		$this->setContentBody($this->model->getResultSet());
		$this->setNumberOfRecords($this->model->getNumberOfRecords());
		$this->prepareLinkArgs();
		$this->setContentQuickLinks(array());
		$this->setFieldActions();
		//$this->prepareQuickLinks(); // adds quick links - if defined - to content body resultset 
		//$this->setContentFooter(new Pagination($this->link->GetCurrentUrl(), $this->model->getCurrentPage(), $this->model->getNumberOfPages()));
		$this->display();	
	}	
	
	protected function setContentQuickLinks(array $quicklinks)
	{
		$this->content['quicklinks'] = array('list');
		$this->content['list-quicklink_common'] = $this->link;
	}
	
	/*
	 * function used to prepare Quick links if any defined
	 * Note. optional - child class specific
	 */
	protected function prepareLinkArgs()
	{
		$outputArray = array();
		foreach($this->model->getResultSet() as $row)
		{
			if(!empty($this->content['header']))
			{
				$linkArgs = array();
				$iterator = $this->content['header']->getIterator();
				while( $iterator->valid() )
				{
					$colName = $iterator->current()->getColumnRealName();
					if( $colName != 'tcverdict')
					{
						$colInx = $iterator->current()->getColumnIndex();
						if(! empty($colInx) && isset($row[$colInx])){
							$linkArgs[$colInx] = $row[$colInx];
						} else if ($iterator->current()->is_predefined() && isset($row[$colName])) {
							// MK. not really sure now why this condition is used here
							$linkArgs[$colName] = $row[$colName];
						} else if ($iterator->current()->is_searchable() && isset($row[$colName])) {
							$linkArgs[$colName] = $row[$colName];
						}
					}
					$iterator->next();
				}
				$row['link-args'] = $linkArgs;
			}
			array_push($outputArray,$row);
		}
		$this->setContentBody($outputArray);
	}	
}

class HighLevelReportDetailDefectTcView extends HighLevelReportDetailVerdictView
{
	public function __construct(HighLevelReportDetailDefectTcModel $model)
	{
		parent::__construct($model, 'testresults');	
		$this->setContentLabel('TPL_HLR_TCDEFECTSUMMARY');
		$headers = $this->prepareHeaderList(array('tcname', 'increment', 'build', 'tsname', 'fname', 'coverage', 'extracolumn_2'));
		if( !empty($headers) )
		{
			$this->setContentHeader($headers);
		}
		$this->link = new Links(array('action'=>$this->prefix.'.MainTestResults',
									  'tcverdict'=>'0;<>'));
	}	
}

class HighLevelReportDetailTabView extends DashboardTabView
{
	/*
	 * constructor
	 */ 
	public function __construct(HighLevelReportDetailTabModel $model)
	{
		parent::__construct($model);
	}
	
	/*
	 * function to perform show details action
	 */ 
	public function show()
	{
		$this->dashboardTabConfiguration = $this->model->show();
		$this->tabIndex = $this->model->getTabIndex();		
		$this->fillWidgetUrlsWithArgs();
		$this->display();	
	}
	
	private function fillWidgetUrlsWithArgs()
	{
		$colIterator = $this->dashboardTabConfiguration->getDashboardColumnList()->getIterator();
		while( $colIterator->valid() )
		{
			$widgetIterator = $colIterator->current()->getWidgetConfigList()->getIterator();
			while( $widgetIterator->valid() )
			{
				// merge the widget URL with the $_GET params
				$widgetUrlQuery = array();
				parse_str($widgetIterator->current()->getUrl(),$widgetUrlQuery);
				$widgetUrlQuery = $this->link->GetModifiedQueryUrl($widgetUrlQuery);
				$widgetIterator->current()->setUrl($widgetUrlQuery);				
				$widgetIterator->next();	
			}
			$colIterator->next();
		}
	}
}

class HighLevelReportDetailView extends DashboardView
{
	/*
	 * constructor
	 */ 
	public function __construct(HighLevelReportDetailModel $model)
	{
		parent::__construct($model);
	}
}
?>
