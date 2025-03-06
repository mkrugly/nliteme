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

abstract class DetailsListsController extends Controller
{
	protected $columnConfig = null;
	protected $columnConfigName = null;
	protected $queryCond = array();
	protected $pageIndex = null;
	protected $sortBy = null;
	protected $sortOrder = null;
	/*
	 * constructor
	 */ 
	public function __construct($columnConfigName)
	{
		$this->columnConfigName = $columnConfigName;
		$this->columnConfig = Config::getInstance()->getColumnConfig($columnConfigName);
	}

	/*
	 * function to create setDetailsMV() - shall be implemented by every child class
	 */ 
	protected function setDetailsMV()
	{
		$model = $this->getDetailsModelName();
		$view = $this->getDetailsViewName();
		$this->setModel(new $model());
		$this->setView(new $view($this->getModel()));
	}
	
	/*
	 * function to create setDetailsMV()
	 */ 
	protected function setListMV()
	{
		$model = $this->getModelName();
		$view = $this->getViewName();
		$this->setModel(new $model());
		$this->setView(new $view($this->getModel()));		
	}

// Actions
	/*
	 * function to perform show action on Details
	 */ 
	public function showDetails()
	{
		$this->setDetailsMV();
		$this->view->setSubmitAction($this->getMyRoute());
		$keyColumn = $this->model->getDbRecord()->getKeyColumn();
		$this->view->showDetails(isset($_GET[$keyColumn]) ? $_GET[$keyColumn] : null);
	}
	
	/*
	 * function to perform edit action on Details
	 */ 
	public function editDetails()
	{
		$this->setDetailsMV();
		$this->view->setSubmitAction($this->getMyRoute());
		$keyColumn = $this->model->getDbRecord()->getKeyColumn();
		$this->view->editDetails(isset($_GET[$keyColumn]) ? $_GET[$keyColumn] : null);
	}
	
	/*
	 * function to perform save action on Details
	 */ 
	public function saveDetails()
	{
		$this->setDetailsMV();
		$this->view->setSubmitAction($this->getMyRoute());
		$this->view->saveDetails(!empty($_POST) ? $_POST : array());
	}
		
	/*
	 * function to perform delete action
	 */ 
	public function deleteDetails()
	{
		$this->setDetailsMV();
		$this->view->setSubmitAction($this->getMyRoute());
		$keyColumn = $this->model->getDbRecord()->getKeyColumn();
		$this->view->deleteDetails(isset($_GET[$keyColumn]) ? $_GET[$keyColumn] : null);
	}
	
	/*
	 * function to perform list action on TestResults
	 */ 
	public function showList()
	{
		$this->setListMV();
		$this->parseGetForList();
		$this->model->setOrderBy($this->sortBy, $this->sortOrder);
		$this->model->setPage($this->pageIndex);
		if(! empty($this->queryCond))
		{
			$this->model->setConditions($this->queryCond);
		}
		$this->view->setSubmitAction($this->getMyRoute());
		$this->view->showList();
	}

	/*
	 * function to perform delete action on the List
	 */ 
	public function deleteList()
	{
		$this->setListMV();
		$this->parseGetForList();
		$this->model->setOrderBy($this->sortBy, $this->sortOrder);
		$this->model->setPage($this->pageIndex);
		if(! empty($this->queryCond))
		{
			$this->model->setConditions($this->queryCond);
		}
		//check $_POST for ids to delete
		$this->view->setSubmitAction($this->getMyRoute());
		$keyColumn = $this->model->getDbRecord()->getKeyColumn();
		$this->view->deleteList(isset($_POST[$keyColumn]) ? $_POST[$keyColumn] : array());
	}
	
	/*
	 * function define a default action
	 * has to be added to each controller
	 */ 
	public function defaultAction()
	{
		$this->showList();
	}
	
	/*
	 * function parses $_GET for the input parameters for ListModel
	 */ 
	protected function parseGetForList()
	{
		// parse the $_GET
		if( ! empty($_GET))
		{
			foreach($_GET as $query => $value)
			{
			  if($query !== 'action')
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
	 * function retrieves a name of Details Model from Controller name
	 */
	private function getDetailsModelName()
	{
		$pattern = '/sController/';
		$replacement = 'Model';
		$subject = get_class($this);
		return preg_replace($pattern, $replacement, $subject);
	} 
	/*
	 * function retrieves a name of Details View from Controller name
	 */
	private function getDetailsViewName()
	{
		$pattern = '/sController/';
		$replacement = 'View';
		$subject = get_class($this);
		return preg_replace($pattern, $replacement, $subject);
	} 	 
}

/*
 * Here are the classes using DetailsView
 */
class BuildIncrementsController extends DetailsListsController
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct('build_increments');
	}
}

class BuildsController extends DetailsListsController
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct('builds');
	}
}

class TestLinesController extends DetailsListsController
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct('testlines');
	}
}

class TestSuitesController extends DetailsListsController
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct('testsuites');
	}
}

class TsTcMapController extends DetailsListsController
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct('testsuites');
	}
}

class TestCasesController extends DetailsListsController
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct('testcases');
	}
}

class TestResultsController extends DetailsListsController
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct('testresults');
	}
} 

class FeaturesController extends DetailsListsController
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct('features');
	}
} 


?>
