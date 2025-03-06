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

abstract class SearcherController extends Controller
{
	/*
	 * constructor
	 */ 
	public function __construct()
	{
	}

	/*
	 * function to perform list action on TestResults
	 */ 
	public function showList()
	{
		$model = $this->getModelName();
		$view = $this->getViewName();
		$this->setModel(new $model());
		$this->setView(new $view($this->getModel()));
		$action = $this->parseGetForAction();
		$this->view->setSubmitAction($action);
		$this->view->showList($_GET);
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
	 * function retrieves the action e.g. ?action=com.nliteme.TestResults.showList for which the search list is generated
	 * Note. Naming convention of controllers is used
	 */ 
	protected function parseGetForAction()
	{
		$action = '';
		if( isset($_GET['action']) )
		{
			$action = preg_replace('/Searcher/', '', $_GET['action']);
		}
		return $action;
	}
}

class TestResultsSearcherController extends SearcherController
{
	public function __construct()
	{
	}
}

class TestCasesSearcherController extends SearcherController
{
	public function __construct()
	{
	}
}

class TestSuitesSearcherController extends SearcherController
{
	public function __construct()
	{
	}
}

class TestLinesSearcherController extends SearcherController
{
	public function __construct()
	{
	}
}

class FeaturesSearcherController extends SearcherController
{
	public function __construct()
	{
	}
}

class BuildsSearcherController extends SearcherController
{
	public function __construct()
	{
	}
}

class BuildIncrementsSearcherController extends SearcherController
{
	public function __construct()
	{
	}
}

?>
