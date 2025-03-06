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

abstract class SearcherView extends TemplateView
{
	protected $modelInputsToProcess = null;
	protected $getParamsToProcess = array();
	protected $preselectFromGet = false;  // flag determines if search form field values shall be preselected based on $_GET params (default false)
	
	/*
	 * constructor
	 */ 
	public function __construct(SearcherModel $model)
	{
		parent::__construct($model);
		$this->setTemplate('searcher.phtml');
		//$this->setTemplate('searchwrapper.phtml');
	}
	
	/*
	 * function to perform show listaction
	 */ 
	public function showList(array $getParams = array())
	{
		$this->getParamsToProcess = $getParams;
		$this->modelInputsToProcess = $this->model->showList();
		$this->display();
	}
	
	/*
	 * protected function prepareContent()
	 */ 
	protected function prepareContent()
	{
		$preparedContent = new DbColumnConfigList();
		$iterator = $this->modelInputsToProcess->getIterator();
		while( $iterator->valid() )
		{
			// set preselected form field values based on $_GET
			$current = $iterator->current();
			if($this->preselectFromGet === true) { $this->setColumnValueFromGet($current); }
			// this splits the date column to date_FROM and date_TO fields
			if($current->getSearcherFieldType() == FIELDTYPES::_DP) {
				$name = $current->getColumnRealName();
				$newDbColumnFrom = clone $current;
				$newDbColumnFrom->setColumnRealName($name."_FROM");
				if($this->preselectFromGet === true) { $this->setColumnValueFromGet($newDbColumnFrom); }
				$newDbColumnTo = clone $current;
				$newDbColumnTo->setColumnRealName($name."_TO");
				if($this->preselectFromGet === true) { $this->setColumnValueFromGet($newDbColumnTo); }
				$preparedContent->addColumn($newDbColumnFrom);
				$preparedContent->addColumn($newDbColumnTo);
			} else {
				$preparedContent->addColumn($current);
			}
			$iterator->next();
		}
		$this->content['inputs'] = $preparedContent;

		// set new queryUrl with submit action
		//$this->link->SetQueryUrl(array('action'=>$this->content['submitAction']));
		$this->updateSubmitQueryUrl();
	}
	
	/*
	 * protected function subtracts the form for keys from the link url and sets the proper submit action
	 */ 
	protected function updateSubmitQueryUrl(bool $resetArgsFromGET=False)
	{
		if($resetArgsFromGET === True)
		{
			$this->link->SetQueryUrl($this->getParamsToProcess);
		}
		$subtractArr = array_flip(array_merge($this->content['inputs']->getDbColumnsRealNames(),
							$this->content['inputs']->getDbColumnsIndices()));
		$argsArr = $this->link->GetQueryUrlKeyDiffArray($subtractArr);
		$argsArr['action'] = $this->content['submitAction'];
		$this->link->SetQueryUrl($argsArr);
	}	
	
	/*
	 * protected function prepareContent()
	 */ 
	protected function setColumnValueFromGet(DbColumnConfig& $dbColumn)
	{
		if(isset($this->getParamsToProcess[$dbColumn->getColumnRealName()])
		   && ! Utils::emptystr($this->getParamsToProcess[$dbColumn->getColumnRealName()])) {
			$getValue = $this->getParamsToProcess[$dbColumn->getColumnRealName()];
			$dbColumn->setColumnValue($getValue);
		} elseif (! Utils::emptystr($dbColumn->getColumnIndex())
		   && isset($this->getParamsToProcess[$dbColumn->getColumnIndex()])
		   && ! Utils::emptystr($this->getParamsToProcess[$dbColumn->getColumnIndex()])
		   && ($dbColumn->getSearcherFieldType() === FIELDTYPES::_SM || $dbColumn->getSearcherFieldType() === FIELDTYPES::_SS)
		   ) {
			$getValue = $this->getParamsToProcess[$dbColumn->getColumnIndex()];
			$dbColumn->setColumnValue($getValue);	
		}
	}
	
	/*
	 * function sets link static arguments based on a given assoc array in form key=>value
	 */ 
	public function setLinkStaticArgs(array $assocArr)
	{
		$this->link->MergeToQueryUrl($assocArr);
	}
	
}

class TestResultsSearcherView extends SearcherView
{
	public function __construct(TestResultsSearcherModel $model)
	{
		parent::__construct($model);
	}
}

class TestCasesSearcherView extends SearcherView
{
	public function __construct(TestCasesSearcherModel $model)
	{
		parent::__construct($model);
	}
}

class TestSuitesSearcherView extends SearcherView
{
	public function __construct(TestSuitesSearcherModel $model)
	{
		parent::__construct($model);
	}
}

class TestLinesSearcherView extends SearcherView
{
	public function __construct(TestLinesSearcherModel $model)
	{
		parent::__construct($model);
	}
}

class FeaturesSearcherView extends SearcherView
{
	public function __construct(FeaturesSearcherModel $model)
	{
		parent::__construct($model);
	}
}

class BuildsSearcherView extends SearcherView
{
	public function __construct(BuildsSearcherModel $model)
	{
		parent::__construct($model);
	}
}

class BuildIncrementsSearcherView extends SearcherView
{
	public function __construct(BuildIncrementsSearcherModel $model)
	{
		parent::__construct($model);
	}
}

?>
