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
 * class BuildCompareController handles build compare main content table requests
 *****/
class BuildCompareController extends Controller
{
	public function __construct()
	{
		$this->parseGetForCompareBy();
		$model = $this->getModelName();
		$view = $this->getViewName();
		$this->setModel(new $model($this->compareByColumn));
		$this->setView(new $view($this->getModel()));
		$this->view->setSubmitAction($this->parseGetForAction());
	}

	/*
	 * function define a default action
	 * has to be added to each controller
	 */ 
	public function defaultAction()
	{
		$this->model->setConditions($_GET);
		$this->view->showList();
	}
	
	/*
	 * function retrieves the action e.g. ?action=com.nliteme.TestResults.showList for which the search list is generated
	 * Note. Naming convention of controllers is used
	 */ 
	protected function parseGetForAction()
	{
		$action = '';
		if( isset($_GET['action']) )
		{
			$action = $_GET['action'].'TBody';
		}
		return $action;
	}	
	
	/*
	 * function parses $_GET for the input parameters for compareby criteria
	 */
	private function parseGetForCompareBy()
	{

		if(!empty($_GET) && isset($_GET['compareby']) && !Utils::emptystr($_GET['compareby']))
		{
			$this->compareByColumn = $_GET['compareby'];
		} else {
			$this->compareByColumn = 'tcverdict';
		}
	}	
}

/*****
 * class BuildCompareSearcherController handles build compare search form requests
 *****/
class BuildCompareSearcherController extends SearcherController
{
	public function __construct()
	{
	}
}

/*****
 * class BuildCompareTBodyController handles build comapre main table body requests
 *****/
class BuildCompareTBodyController extends Controller
{
	protected $columnConfig = null;
	protected $queryCond = array();
	protected $compareByColumn = null;
	private $subController = null;
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		$this->parseGetForCompareBy();
		// if no subcontroller is present configure this controller
		if(empty($this->getSubcontroller()))
		{
			$this->columnConfig = Config::getInstance()->getColumnConfig('testresults');
			$model = $this->getModelName();
			$view = $this->getViewName();
			$this->setModel(new $model($this->compareByColumn));
			$this->setView(new $view($this->getModel()));				
		} 
	}
	
	/*
	 * function define a default action
	 * has to be added to each controller
	 */ 
	public function defaultAction()
	{
		// default is to run a function on this controller
		// but if a subcontroller is present it's function shall be called instead
		if(empty($this->getSubcontroller()))
		{
			$this->parseGetForList();
			if(! empty($this->queryCond))
			{
				$this->model->setConditions($this->queryCond);
			}
			$this->view->showList();				
		} else {
			$this->getSubcontroller()->defaultAction();
		}
	}
	
	/*
	 * function parses $_GET for the input parameters for ListModel
	 */ 
	protected function parseGetForList()
	{
		// parse the $_GET
		if( ! empty($_POST))
		{
			foreach($_POST as $query => $value)
			{
			  if($query !== 'action' && $query !=='compareby')
			  {
				// check out the page index
				if($query === "page") {
					$this->pageIndex = $value;
				} else if($query === "sort") {	// check out sortby
					// check if join tab is present in config
					$column = $this->columnConfig->getDbColumnByRealName($value);
					if( isset($column) ) 
					{
						// if join tab is present add it to the query name
						$joinTab = $column->getColumnJoinTab();
						isset($joinTab) ? $value = $joinTab.'.'.$value : null;
					}
					$this->sortBy = $value;
				} else if($query === "order") {	// check out sort
					$this->sortOrder = $value;
				} else {
					// prepare check out searchable fields
					preg_match('/(\w+)_.*/', $query, $matches) ?  $name = $matches[1] : $name = $query;
					$column = $this->columnConfig->getDbColumnByRealName($name);
					if( isset($column) ) 
					{
						// if join tab is present add it to the query name
						$joinTab = $column->getColumnJoinTab();
						isset($joinTab) ? $query = $joinTab.'.'.$query : null;
					}
					$this->queryCond[$query] = $value;
				}
			  }
			}
		}
	}
	
	/*
	 * function parses $_GET for the input parameters for compareby criteria
	 */
	private function parseGetForCompareBy()
	{

		if(!empty($_POST) && isset($_POST['compareby']) && !Utils::emptystr($_POST['compareby']))
		{
			$this->compareByColumn = $_POST['compareby'];
		} else {
			$this->compareByColumn = 'tcverdict';
		}
	}

	/*
	 * function checks if compareByColumn specific controller class exists and if so returns it object
	 */	
	private function getSubcontroller()
	{
		if(!empty($this->compareByColumn) && empty($this->subController))
		{
		    $pattern = '/Controller/';
		    $replacement = ucfirst($this->compareByColumn).'Controller';
		    $subject = get_class($this);
			$subControllerName = preg_replace($pattern, $replacement, $subject);
			$this->subController = class_exists($subControllerName) ? new $subControllerName() : null;
		}
		return $this->subController;
	}
}

class BuildCompareTBodyPassrateController extends Controller
{
	private $staticConditionFields = array();
	private $staticCondictionArray = array();
	private $buildIdArray = array();
	
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		$model = $this->getModelName();
		$view = $this->getViewName();
		$this->setModel(new $model());
		$this->setView(new $view($this->getModel()));
		$this->getConditionFields();		
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
	
	/*
	 * function parses $_GET for the input parameters for a corresponding Model
	 */ 
	protected function parseGet()
	{
		// parse the $_GET
		if( ! empty($_POST))
		{
			foreach($_POST as $query => $value)
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

	/*
	 * function returns a list of columns that are allowed to be condition fields
	 */ 	
	protected function getConditionFields()
	{
		if(empty($this->staticConditionFields))
		{
			$this->staticConditionFields = $this->model->getSortingColumnInxs();
		}
		return $this->staticConditionFields;
	}	
}

?>
