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

class HighLevelReportController extends Controller
{
	private $staticConditionFields = array('tsid', 'fid', 'extracolumn_2', 'tcverdict');
	private $allowedGroupByFields = array('tsname', 'fname', 'extracolumn_2');
	private $staticCondictionArray = array();
	private $buildSelectionArray = array();
	private $buildLimit = 10;
	private $groupByColumns = array('increment','build');
	private $pageIndex = null;
	private $staticLinkArgs = array();
	private $columnConfig = null;
	private $hlrDetailsDashboardCfg = array(); // {key, value} pair for the detail dashboard config indetification
	
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		$this->pageIndex = 0;
		$this->buildLimit = 10;
		$this->columnConfig = Config::getInstance()->getColumnConfig('builds');
		$model = $this->getModelName();
		$view = $this->getViewName();
		$this->parseGetForGroupBy();
		$this->setModel(new $model($this->groupByColumns));
		$this->setView(new $view($this->getModel()));
	}

	/*
	 * function to perform list action on TestResults
	 */ 
	public function show()
	{			
		$this->parseGet();
		$this->model->setPage($this->pageIndex);
		$this->model->setBuildLimit($this->buildLimit);
		$this->model->setBuildSelectionConditions($this->buildSelectionArray);
		$this->model->setStaticConditions($this->staticCondictionArray);
		$this->view->setLinkStaticArgs(array_merge($this->staticLinkArgs, $this->hlrDetailsDashboardCfg));
		$this->view->show();
	}

	/*
	 * function define a default action
	 * has to be added to each controller
	 */ 
	public function defaultAction()
	{
		$this->show();
	}
	
	private function parseGetForGroupBy()
	{
		$groupByList = array();
		if(isset($_GET['groupby']))
		{
			$groupby_from_arr = is_array($_GET['groupby']) ? $_GET['groupby'] : array($_GET['groupby']);
			$groupByList = array_intersect($this->allowedGroupByFields, $groupby_from_arr);
		}	
		if (empty($groupByList)) {array_push($groupByList, 'tsname', 'fname');}

		$this->groupByColumns = array_unique(array_merge($this->groupByColumns, $groupByList));
		if (in_array('fname', $this->groupByColumns)) {
			$this->buildLimit = 3;
		}
		$this->staticLinkArgs['groupby'] = $groupByList;
	}	
	
	/*
	 * function parses $_GET for the input parameters for a corresponding Model
	 */ 
	protected function parseGet()
	{
		// parse the $_GET
		if( ! empty($_GET))
		{
			foreach($_GET as $query => $value)
			{
				if(!in_array($query,array('action', 'groupby')))
				{
					// check out the page index
					if($query === "page")
					{
						$this->pageIndex = $value;
					}
					else if ($query === "dcfg") 
					{
						$this->hlrDetailsDashboardCfg = array($query => $value);
					}
					else if ($query === "nonav") 
					{
						$this->staticLinkArgs['nonav'] = 1;
					}
					else if ($query === "bmax" && is_numeric($value))
					{
						$this->buildLimit = $value;
					}
					// static condition for rate results
					else if (in_array($query, $this->staticConditionFields))
					{
						$this->staticCondictionArray[$query] = is_array($value) ? $value : array($value);
					}
					// buildIds selection query
					else
					{
						preg_match('/(\w+)_.*/', $query, $matches) ?  $name = $matches[1] : $name = $query;
						$column = $this->columnConfig->getDbColumnByRealName($name);
						if( isset($column) ) 
						{
							// if join tab is present add it to the query name
							$joinTab = $column->getColumnJoinTab();
							isset($joinTab) ? $query = $joinTab.'.'.$query : null;
						}
						$this->buildSelectionArray[$query] = $value;						
					}
				}
			}
		}
	}	
}

class HighLevelReportDetailPassRateController extends Controller
{
	private $staticConditionFields = array('tsid', 'fid', 'extracolumn_2', 'tcverdict');
	private $allowedGroupByFields = array('tsname', 'fname', 'extracolumn_2');
	private $staticCondictionArray = array();
	private $buildIdArray = array();
	private $groupByColumns = array('tlname','extracolumn_0','build');
	
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		$this->parseGetForGroupBy();
		$model = $this->getModelName();
		$view = $this->getViewName();
		$this->setModel(new $model(array(), $this->groupByColumns));
		$this->setView(new $view($this->getModel()));
	}

	/*
	 * function to perform list action on TestResults
	 */ 
	public function show()
	{			
		$this->parseGet();
		$this->model->setStaticConditions($this->staticCondictionArray);
		$this->model->setBuildIdCondition($this->buildIdArray);
		$this->view->show();
	}

	/*
	 * function define a default action
	 * has to be added to each controller
	 */ 
	public function defaultAction()
	{
		$this->show();
	}
	
	private function parseGetForGroupBy()
	{
		$groupByList = array();
		if(isset($_GET['groupby']))
		{
			$groupby_from_arr = is_array($_GET['groupby']) ? $_GET['groupby'] : array($_GET['groupby']);
			$groupByList = array_intersect($this->allowedGroupByFields, $groupby_from_arr);
		}	
		if (empty($groupByList)) {array_push($groupByList, 'tsname', 'fname');}
		$this->groupByColumns = array_unique(array_merge($this->groupByColumns, $groupByList));
	}
	
	/*
	 * function parses $_GET for the input parameters for a corresponding Model
	 */ 
	protected function parseGet()
	{
		// parse the $_GET
		if( ! empty($_GET))
		{
			foreach($_GET as $query => $value)
			{
				if($query !== 'action')
				{	
					// static condition for rate results
					if(in_array($query, $this->staticConditionFields))
					{
						$this->staticCondictionArray[$query] = is_array($value) ? $value : array($value);
					}

					// check for buildId
					if($query === 'buildid')
					{
						$this->buildIdArray = is_array($value) ? $value : array($value);
					}					
				}
			}
		}
	}	
}

class HighLevelReportDetailPassRateTooltipController extends HighLevelReportDetailPassRateController
{	
}

class HighLevelReportDetailDefectTLController extends HighLevelReportDetailPassRateController
{	
}

/*****
 * class HighLevelReportListSearcherController handles HLR search form requests
 *****/
class HighLevelReportSearcherController extends SearcherController
{
}

/*****
 * class HighLevelReportDetailVerdictController handles HLR Detail Verdict form requests
 *****/
class HighLevelReportDetailVerdictController extends TestResultsController
{
}

/*****
 * class HighLevelReportDetailVerdictController handles HLR Detail Verdict form requests
 *****/
class HighLevelReportDetailDefectTcController extends HighLevelReportDetailVerdictController
{
}

/*****
 * class HighLevelReportDetailTabController handles HLR Detail Report requests
 *****/
class HighLevelReportDetailTabController extends DashboardTabController
{
}

/*****
 * class HighLevelReportDetailController handles HLR Detail Report requests
 *****/
class HighLevelReportDetailController extends DashboardController
{
}
?>
