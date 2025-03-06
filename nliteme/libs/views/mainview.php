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

abstract class MainView extends TemplateView
{
	private $queryParams = array();	// contains an array with query params e.g. $_GET copy
	/*
	 * constructor
	 */ 
	public function __construct(MainModel $model)
	{
		parent::__construct($model);
		$this->setTemplate('main-page.phtml');
		$this->alternativeTemplate = 'main-page-nonav.phtml';
	}
	
	/*
	 * function to perform save action
	 */ 
	public function show()
	{
		$this->display();
	}
	
	/*
	 * function to set query Params array
	 */ 
	public function setQueryParams(array $queryParams)
	{
		$this->queryParams = $queryParams;
	}

	/*
	 * function returns query Params array
	 */ 
	public function getQueryParams()
	{
		return $this->queryParams;
	}

	/*
	 * protected function prepareContent()
	 */ 
	protected function prepareContent()
	{
		if(! isset($this->getQueryParams()['nonav']) )
		{
			// set top navigation menu
			$this->content['top-nav'] = $this->prepareTopNav();
			// set searcher link to load
			$this->content['searcher'] = $this->setSearcherLink();		
			// set left column link to load
			//$this->content['left-column'] = $this->setLeftColumnLink();	
		} else {
			$this->setTemplate($this->alternativeTemplate);
		}

		// set main content link to load
		$this->content['main-content'] = $this->setMainContentLink();
	}

	/*
	 * returns a list of actions for top-nav menu
	 */ 	
	protected function prepareTopNav()
	{
		return array(  'TPL_GENERAL' => 'action='.$this->prefix.'.MainDashboard',
					   'TPL_BUILDS'  => 'action='.$this->prefix.'.MainBuilds',
					   'TPL_BUILD_INC'  => 'action='.$this->prefix.'.MainBuildIncrements',
					   'TPL_TESTRESULTS'  => 'action='.$this->prefix.'.MainTestResults',
					   'TPL_TESTCASES'  => 'action='.$this->prefix.'.MainTestCases',					   					   		
					   'TPL_TESTSUITES'  => 'action='.$this->prefix.'.MainTestSuites',
					   'TPL_TESTLINES'  => 'action='.$this->prefix.'.MainTestLines',
					   'TPL_FEATURES'  => 'action='.$this->prefix.'.MainFeatures',
					   'TPL_BUILDCOMPARE'  => 'action='.$this->prefix.'.MainBuildCompare',
					   'TPL_HIGHLEVREPORT'  => array(
						   'TPL_HLRFNAME' =>  'action='.$this->prefix.'.MainHighLevelReport&groupby[]=fname&dcfg=1',
						   'TPL_HLRTSNAME' =>  'action='.$this->prefix.'.MainHighLevelReport&groupby[]=tsname',
						   'TPL_HLR' =>  'action='.$this->prefix.'.MainHighLevelReport&dcfg=2',
						   'TPL_HLRDEFECTSTC' =>  'action='.$this->prefix.'.MainHighLevelReport&groupby[]=extracolumn_2&dcfg=3&tcverdict=0;<>',
						   'TPL_HLRDEFECTSTS' =>  'action='.$this->prefix.'.MainHighLevelReport&groupby[]=extracolumn_2&groupby[]=tsname&dcfg=3&tcverdict=0;<>'
						   )
					);
	}

	/*
	 * returns a link to be loaded into searcher div
	 */ 
	protected function setSearcherLink()
	{
		return $this->setQueryWithAction('Searcher', true);
	}

	/*
	 * returns a link to be loaded into main-content div
	 */ 	
	protected function setMainContentLink()
	{
		return $this->setQueryWithAction('');
	}

	/*
	 * returns a link to be loaded into left-column div
	 */ 	
	protected function setLeftColumnLink()
	{
		return $this->setQueryWithAction('LeftColumn', true);
	}
	
	/*
	 * function sets action string
	 * return queryParam array wuth modified action param
	 */ 	
	private function setQueryWithAction($actionSufix, $delActionDetails = false)
	{
		$queryArr = $this->getQueryParams();		
		// if action is not present in queryParams array set it based on calling class name removing 'View'
		if(! isset($queryArr['action'])) 
		{
			$queryArr['action'] = preg_replace('/View/', '', get_class($this));
		}
		// add prefix if not present
		if(! preg_match('/^'.$this->prefix.'/', $queryArr['action']) )
		{
			$queryArr['action'] = $this->prefix.'.'.$queryArr['action'];
		}	
		// take care of the target
		$fractions = explode('.', $queryArr['action']);
		if(isset($fractions[2]))
		{
			// remove Main string after $prefix
			$pattern = '/^Main/';
			$fractions[2] = preg_replace($pattern, '', $fractions[2]);			
			// add sufix
			$fractions[2] = $fractions[2].$actionSufix;
		}
		$delActionDetails === true ? array_splice($fractions, 3) : null;

		$queryArr['action'] = implode('.', $fractions);
		return http_build_query($queryArr);
	}	
}

class MainTestResultsView extends MainView
{
	public function __construct(MainTestResultsModel $model)
	{
		parent::__construct($model);
	}
}

class MainTestCasesView extends MainView
{
	public function __construct(MainTestCasesModel $model)
	{
		parent::__construct($model);
	}
}

class MainTestSuitesView extends MainView
{
	public function __construct(MainTestSuitesModel $model)
	{
		parent::__construct($model);
	}
}

class MainTestLinesView extends MainView
{
	public function __construct(MainTestLinesModel $model)
	{
		parent::__construct($model);
	}
}

class MainFeaturesView extends MainView
{
	public function __construct(MainFeaturesModel $model)
	{
		parent::__construct($model);
	}
}

class MainBuildsView extends MainView
{
	public function __construct(MainBuildsModel $model)
	{
		parent::__construct($model);
	}
}

class MainBuildIncrementsView extends MainView
{
	public function __construct(MainBuildIncrementsModel $model)
	{
		parent::__construct($model);
	}
}

class MainDashboardView extends MainView
{
	public function __construct(MainDashboardModel $model)
	{
		parent::__construct($model);
		$this->setTemplate('main-page-no-topnavright.phtml');
	}
	/*
	 * protected function prepareContent()
	 */ 
	protected function prepareContent()
	{
		// set top navigation menu
		$this->content['top-nav'] = $this->prepareTopNav();
		// set main content link to load
		$this->content['main-content'] = $this->setMainContentLink();
		// set left column link to load
		//$this->content['left-column'] = $this->setLeftColumnLink();
	}
}

class MainBuildCompareView extends MainView
{
	public function __construct(MainBuildCompareModel $model)
	{
		parent::__construct($model);
		$this->setTemplate('main-page-buildcompare.phtml');
	}
}

class MainHighLevelReportView extends MainView
{
	public function __construct(MainHighLevelReportModel $model)
	{
		parent::__construct($model);
		//$this->setTemplate('main-page-buildcompare.phtml');
	}
}

class MainHighLevelReportDetailView extends MainView
{
	public function __construct(MainHighLevelReportDetailModel $model)
	{
		parent::__construct($model);
		$this->setTemplate('main-page-no-topnavright.phtml');
	}
}



?>
