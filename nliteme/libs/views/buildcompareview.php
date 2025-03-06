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
 * class BuildCompareView prepares a view for the build compare main content table based on the model data
 *****/
class BuildCompareView extends TemplateView //extends TemplateView
{
	private $buildCompareHeader = null;	// structure that uses
	private $message = array(); // messages to show e.g error etc.
    public function __construct(BuildCompareModel $model)
	{
		parent::__construct($model);
		$this->setTemplate('main-content-buildcompare.phtml');	
		$this->buildCompareHeader = new BuildCompareHeader();
	}

	/*
	 * function to perform show list action
	 */ 
	public function showList()
	{
		$this->model->showList();
		$resultSet = $this->model->getResultSet();
		$res = $this->prepareBuilds($resultSet);
		// if the list of builds to compare is not empty set the compare table and so on
		if($res === true)
		{
			$this->buildCompareHeader->setCompareByColumn($this->model->getCompareByCfgKey());
			$sortingColumnList = $this->model->getSortingColumnList();
			$this->prepareSortingColumns($sortingColumnList);			
		}
		$this->setMessages($this->message,true);
		//print_r($this->buildCompareHeader->encode());
		$this->display();
	}	
	
	protected function prepareContent()
	{
		$this->content['compareby'] = $this->model->getCompareByColumnName();
		$this->setContentBody($this->buildCompareHeader->encode());
	}
	
	/*
	 * function set the table header field in the content array
	 */ 	
	protected function setSortingHeader(DbColumnList $headers)
	{
		$this->content['header'] = $headers;
	}

	/*
	 * function set the table header field in the content array
	 */ 	
	protected function setBuildHeader(DbColumnList $headers)
	{
		$this->content['build'] = $headers;
	}
	
	/*
	 * function set the table header field in the content array
	 */ 	
	protected function setIncrementHeader(DbColumnList $headers)
	{
		$this->content['increment'] = $headers;
	}	
	
	/*
	 * function sets the body of the build compare template
	 */ 	
	protected function setContentBody($bodyContent)
	{
		$this->content['body'] = $bodyContent;
	}

	/*
	 * function sets the number of element for which the ajax requests will be done
	 * it shall be equal to the number of elements in the 1st sorting column
	 */ 	
	protected function setSortingCount($sortCount)
	{
		$this->content['sortcount'] = $sortCount;
	}
	
	/*
	 * function sets the number of element for which the ajax requests will be done
	 * it shall be equal to the number of elements in the 1st sorting column
	 */ 	
	protected function setSortingMainIndex($sortmain)
	{
		$this->content['sortmain'] = $sortmain;
	}	
	
	/*
	 * function reduces DbColumnConfig content to only needed elements
	 * and adds the stripped Sorting Columns list to the BuildCompareHeader object
	 * additionally the content['header'] for table header settings
	 */ 	
	protected function prepareSortingColumns(DbColumnList& $sortingColumnList)
	{
		// create en empty DbColumnList
		$strippedSortingList = new DbColumnList();
		$strippedSortingInputs = array();
		$iterator = $sortingColumnList->getIterator();
		$sortCount = 0;
		$sortMainInx = '';
		while( $iterator->valid() )
		{
			// create an empty DbColumnConfig
			$strippedColumn = new DbColumnConfig(null);
			// and fill only the relevant fields based on the SortingColumn info from model
			$strippedColumn->setColumnRealName($iterator->current()->getColumnRealName());
			$strippedColumn->setColumnIndex($iterator->current()->getColumnIndex());
			// Important check !!!
			// if predefined values for any of the sorting columns are not present
			// - break the loop
			// - set sortCount = 0
			$values = $iterator->current()->getColumnPredefinedValues();
			if(empty($values)){
				$sortCount = 0;
				$strippedSortingList = new DbColumnList();
				$strippedSortingInputs = array();
				array_push($this->message, Text::_('NO_COMPARE_RESULTS'));
				break;
			} else {
				$strippedColumn->setColumnPredefinedValues($values);
				$strippedSortingList->addColumn($strippedColumn);		
				// now do the same for inputList in form of key:values
				$key = (!empty($iterator->current()->getColumnIndex())) ? $iterator->current()->getColumnIndex() : $iterator->current()->getColumnRealName();
				$inds = array_combine(range(0,count($values)-1), array_keys($values));
				$strippedSortingInputs[$key] = array('index' => $inds, 'map' => $values);
				if(empty($sortCount))
				{
						$sortCount = count($values);
						$sortMainInx = $key;
				}
				$iterator->next();				
			}
		}
		// set sorting header and input
		$this->buildCompareHeader->setSortingColumns($strippedSortingInputs);	
		$this->setSortingHeader($strippedSortingList);
		$this->setSortingCount($sortCount);
		$this->setSortingMainIndex($sortMainInx);
	}
	
	/*
	 * function parses the model's resultSet and sets the builds structure of the BuildCompareHeader object
	 * additionally the content['builds'] and content['increment'] for table header settings
	 */ 	
	protected function prepareBuilds($resultSet)
	{
		if(!empty($resultSet) && ! $resultSet->EOF ) {
			// array containing builds structure of the BuildCompareHeader
			$builds = array();
			// list for setting content['builds']
			$buildList = new DbColumnList();
			// list for setting content['increment']
			$incrementList = new DbColumnList();
			foreach($resultSet->GetRows() AS $row)
			{
				// set the value in the build compare array for a given buildId
				if(!Utils::emptystr($row['buildid']))
				{
					//
					$builds[$row['buildid']] = $this->getBuildDetails($row);
					// add build info content['builds']
				    $buildColumn = new DbColumnConfig(null);
					$buildColumn->setColumnRealName($row['build']);
					$buildColumn->setColumnIndex($row['buildid']);
					$action = $this->link->GetServerUrl() . $this->link->GetScriptName();
					$action .= '?action='.$this->prefix.'.MainBuilds.editDetails&buildid='.$row['buildid'];
					$buildColumn->setParam('action', $action);
					$buildList->addColumn($buildColumn);
					// add increment info content['increment']
					if(isset($row['increment']) && !empty($row['increment']))
					{
						$incrementColumn = $incrementList->getDbColumnByRealName($row['increment']);
						if(empty($incrementColumn))
						{
							$incrementColumn = new DbColumnConfig(null);
							$incrementColumn->setColumnRealName($row['increment']);
						}
						$incrementColumn->setColumnIndex($row['incid']);
						if($incrementColumn->hasPredefinedValue($row['build']) === false)
						{
							$incBuilds = $incrementColumn->getColumnPredefinedValues();
							if(! is_array($incBuilds))
							{
								$incBuilds = array();
							}
							$incBuilds[$row['buildid']] = $row['build'];
							$incrementColumn->setColumnPredefinedValues($incBuilds);
						}
						$incrementList->addColumn($incrementColumn);
					}
					
				}
			}
			$inds = array_combine(range(0,count($builds)-1), array_keys($builds));
			$this->buildCompareHeader->setCompareArray(array('buildid' => array('index' => $inds, 'map' => $builds)));
			$this->setBuildHeader($buildList);
			$this->setIncrementHeader($incrementList);
			return true;
		} else {
			array_push($this->message, Text::_('NO_BUILDS_TO_COMPARE'));
			$str = get_class($this)."::".__FUNCTION__.": SQL Query error or Model's Result Set is empty\n";
			Tracer::getInstance()->log($str, LOGLEV_ERROR);
			return false;
		}
	}
	
	/*
	 * function processes $row from ResultSet and fills builds entries in the buildCompareHeader object
	 */
	protected function getBuildDetails($row)
	{
		//$detailList = array();
		//$detailList['buildid'] = $row['buildid'];
		//$detailList['build'] = $row['build'];
		//$detailList['tooltip'] = $row['createdate'];
		//return $detailList;
		return $row['build'];
	}	
}

/*****
 * class BuildCompareSearcherView prepares a view for the build compare search form based on the model data
 *****/
class BuildCompareSearcherView extends SearcherView
{
	public function __construct(BuildCompareSearcherModel $model)
	{
		parent::__construct($model);
		$this->preselectFromGet = true;
		$this->setTemplate('searcher-buildcompare.phtml');
	}

	protected function prepareContent()
	{
		if($this->preselectFromGet === true)
		{
			$compareByDbColumn = $this->modelInputsToProcess->getDbColumnByRealName('compareby');
			if(isset($compareByDbColumn))
			{
				// first set default value to be first form predefined
				$defaultValue = $compareByDbColumn->getColumnPredefinedValuebyIndex(0);
				if(!empty($defaultValue))
				{
					$compareByDbColumn->setColumnValue($defaultValue);
				}
				// override default value with the one from $GET
				$this->setColumnValueFromGet($compareByDbColumn);
				$this->modelInputsToProcess->addColumn($compareByDbColumn);
			}
		}
		parent::prepareContent();
		$this->content['compareby'] = $this->content['inputs']->getDbColumnByRealName('compareby');
		$this->content['inputs']->delColumn('compareby');
	}
}

/*****
 * class BuildCompareTBodyView prepares a view for the build compare main content table body based on the model data
 * (json format)
 *****/
class BuildCompareTBodyView extends JsonView
{
	protected $buildCompareRows;
	protected $link = null;
	/*
	 * constructor
	 */ 
	public function __construct(BuildCompareTBodyModel $model)
	{
		parent::__construct($model);
		$this->buildCompareRows = new BuildCompareRows();
		$this->link = new Links(array('action'=>$this->prefix.'.MainTestResults'));
	}
	
	/*
	 * function to perform show details action
	 */ 
	public function showList()
	{
		$this->model->showList();
		$rs = $this->model->getResultSet();
		if(!empty($rs) && ! $rs->EOF ) {
			foreach($rs->GetRows() AS $row)
			{
				$this->processRSRow($row);
			}
			$this->content = $this->buildCompareRows->encode();
		} else {
			$this->content = '{}';  // so that valid json string is returned
			//$str = get_class($this)."::".__FUNCTION__.": SQL Query error or Model's Result Set is empty\n";
			//Tracer::getInstance()->log($str, LOGLEV_ERROR);	
		}
		
		$this->display();
	}
	
	/*
	 * function processes $row from ResultSet and fills entries for a suitable CompareRow
	 */
	protected function processRSRow($row)
	{
		$compCName = $this->model->getCompareByColumnName();
		// first generate a key to BuildCompareRows List
		// and the row specific key=>value pairs
		$rowKey = '';
		$rowFields = array();
		foreach($this->model->getSortingColumnInxs() as $col)
		{
			if(!Utils::emptystr($row[$col]))
			{
				$rowKey .= $row[$col].'_';
				$rowFields[$col] = $this->getColumnDetails($row, $col);
			}
		}

		if(!Utils::emptystr($rowKey))
		{
			// get BuildCompareRow if exists, otherwise create a new one
			$bcRow = $this->buildCompareRows->getRow($rowKey);
			if(empty($bcRow))
			{
				$bcRow = new BuildCompareRow($rowFields);
				$bcRow->setCompareArray(array());
			}
			
			// set the value in the build compare array for a given buildId
			if(!Utils::emptystr($row['buildid']) && isset($row[$compCName]) && !Utils::emptystr($row[$compCName]))
			{
				$bcArray = $bcRow->getCompareArray();
				$bcArrayKey = $row['buildid'];
				if(empty($bcArray[$bcArrayKey]))
				{
					$bcArray[$bcArrayKey] = $this->getBuildDetails($row);
					$bcRow->setCompareArray($bcArray);
				}
				// update the BuildCompareRows with a given BuildCompareRow object
				$this->buildCompareRows->addRow($bcRow,$rowKey);
			}
		}
	}
	
	/*
	 * function processes $row from ResultSet and fills entries for a suitable CompareRow
	 */
	protected function getBuildDetails($row)
	{
		$detailList = array();
		$detailList['value'] = $row[$this->model->getCompareByColumnName()];
		$detailList['link']  = $this->link->GetServerUrl();
		$detailList['link'] .= $this->link->GetScriptName();
		$action = $this->link->GetArgFromQueryUrl('action');
		$action .='.editDetails';
		$detailList['link'] .= '?'.$this->link->GetModifiedQueryUrl(array('action'=>$action,'id'=>$row['id']));
		$detailList['id']    = $row['id'];
		$detailList['tooltip'] = $row['createdate'];
		return $detailList;
	}

	/*
	 * function processes $row from ResultSet and prepares detailed info for a given column
	 */	
	protected function getColumnDetails($row, $col)
	{
		$actions =  array('buildid'  => 'action='.$this->prefix.'.MainBuilds.editDetails',
					   'incid' => 'action='.$this->prefix.'.MainBuildIncrements.editDetails',
					   'id'    => 'action='.$this->prefix.'.MainTestResults.editDetails',
					   'tcid'  => 'action='.$this->prefix.'.MainTestCases.editDetails',				   					   		
					   'tsid'  => 'action='.$this->prefix.'.MainTestSuites.editDetails',
					   'tlid'  => 'action='.$this->prefix.'.MainTestLines.editDetails',
					   'fid'   => 'action='.$this->prefix.'.MainFeatures.editDetails'
					);
		$field_action_map = array('fid' => 'hlink');
		$details = array('index' => $row[$col]);
		if(!empty($row[$col]))
		{
			if(in_array($col, array_keys($field_action_map)) && !empty($row[$field_action_map[$col]]))
			{
				$details['link'] = $row[$field_action_map[$col]];
			} elseif (in_array($col, array_keys($actions))) {
				$details['link'] = $this->link->GetServerUrl();
				$details['link'] .= $this->link->GetScriptName();
				$details['link'] .= '?'.$actions[$col].'&'.$col.'='.$row[$col];
			}
		}
		return $details;
	}
}

class BuildCompareTBodyPassrateView extends JsonView
{
	protected $buildCompareRows;
	protected $link = null;
	/*
	 * constructor
	 */ 
	public function __construct(BuildCompareTBodyPassrateModel $model)
	{
		parent::__construct($model);
		$this->buildCompareRows = new BuildCompareRows();
		$this->link = new Links(array('action'=>$this->prefix.'.MainTestResults'));
	}
	
	/*
	 * function to perform show details action
	 */ 
	public function show()
	{
		$this->content = '{}';
		$res = $this->model->show();
		if($res === true)
		{
			$rs = $this->model->getOutputArray();
			if(!empty($rs)) {
				foreach($rs AS $row)
				{
					$this->processRSRow($row);
				}
				$this->content = $this->buildCompareRows->encode();
			} 			
		}
		$this->display();
	}
	
	/*
	 * function processes $row from ResultSet and fills entries for a suitable CompareRow
	 */
	protected function processRSRow($row)
	{
		$compCName = $this->model->getCompareByColumnName();
		// first generate a key to BuildCompareRows List
		// and the row specific key=>value pairs
		$rowKey = '';
		$rowFields = array();
		foreach($this->model->getSortingColumnInxs() as $col)
		{
			if(!empty($row[$col]))
			{
				$rowKey .= $row[$col].'_';
				$rowFields[$col] = $this->getColumnDetails($row, $col);
			}
		}

		if(!Utils::emptystr($rowKey))
		{
			// get BuildCompareRow if exists, otherwise create a new one
			$bcRow = $this->buildCompareRows->getRow($rowKey);
			if(empty($bcRow))
			{
				$bcRow = new BuildCompareRow($rowFields);
				$bcRow->setCompareArray(array());
			}
			
			// set the value in the build compare array for a given buildId
			if(!Utils::emptystr($row['buildid']) && isset($row[$compCName]) && !Utils::emptystr($row[$compCName]))
			{
				$bcArray = $bcRow->getCompareArray();
				$bcArrayKey = $row['buildid'];
				if(empty($bcArray[$bcArrayKey]))
				{
					$bcArray[$bcArrayKey] = $this->getBuildDetails($row);
					$bcRow->setCompareArray($bcArray);
				}
				// update the BuildCompareRows with a given BuildCompareRow object
				$this->buildCompareRows->addRow($bcRow,$rowKey);
			}
		}
	}
	
	/*
	 * function processes $row from ResultSet and fills entries for a suitable CompareRow
	 */
	protected function getBuildDetails($row)
	{
		$detailList = array();
		$detailList['value'] = $row[$this->model->getCompareByColumnName()];
		$detailList['link']  = $this->link->GetServerUrl();
		$detailList['link'] .= $this->link->GetScriptName();
		$detailList['link'] .= '?'.$this->link->GetModifiedQueryUrl($row['link-args']);
		$detailList['tooltip'] = $row['createdate'];
		return $detailList;
	}
	
	/*
	 * function processes $row from ResultSet and prepares detailed info for a given column
	 */	
	protected function getColumnDetails($row, $col)
	{
		$actions =  array('buildid'  => 'action='.$this->prefix.'.MainBuilds.editDetails',
					   'incid' => 'action='.$this->prefix.'.MainBuildIncrements.editDetails',
					   'id'    => 'action='.$this->prefix.'.MainTestResults.editDetails',
					   'tcid'  => 'action='.$this->prefix.'.MainTestCases.editDetails',				   					   		
					   'tsid'  => 'action='.$this->prefix.'.MainTestSuites.editDetails',
					   'tlid'  => 'action='.$this->prefix.'.MainTestLines.editDetails',
					   'fid'   => 'action='.$this->prefix.'.MainFeatures.editDetails'
					);
		$field_action_map = array('fid' => 'hlink');
		$details = array('index' => $row[$col]);
		if(!empty($row[$col]))
		{
			if(in_array($col, array_keys($field_action_map)) && !empty($row[$field_action_map[$col]]))
			{
				$details['link'] = $row[$field_action_map[$col]];
			} elseif (in_array($col, array_keys($actions))) {
				$details['link'] = $this->link->GetServerUrl();
				$details['link'] .= $this->link->GetScriptName();
				$details['link'] .= '?'.$actions[$col].'&'.$col.'='.$row[$col];
			}
		}
		return $details;
	}
}

/****************************************************************************************************************/
/*
 * Structure of a build compare table body for json encoding
 */

/*****
 * Class is a container for the BuildCompareRow objects,
 * that can be encoded to json string
 *****/
class BuildCompareRows extends JsonObject
{
	public function __construct($compareRowList = null)
	{
		parent::__construct($compareRowList);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($compareRowList)
	{
		foreach ($compareRowList AS $key => $value) 
		{
			if( $value instanceof BuildCompareRow ) {
				$this->addRow($value, $key );
			} else {
				$this->addRow(new BuildCompareRow($value), $key );
			}  
		}
	}
	/*
	 * function add CompareRow object to the list
	 */ 
	public function addRow(BuildCompareRow $compareRow, $key = null)
	{
		if(is_null($key)) {
			$this->append( $compareRow );
		} else {
			$this->offsetSet($key, $compareRow);
		}
	}
	
	/*
	 * function returns a CompareRow object for a given $key
	 */ 
	public function getRow($key)
	{
		return $this->getParam($key);
	}
	
}

/*****
 * class BuildCompareRow defines a row of data used in build compare feature
 *****/
class BuildCompareRow extends JsonObject
{	
	public function __construct($compareRowFields = null)
	{
		parent::__construct($compareRowFields);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{
		foreach ($datastructure AS $key => $value) 
		{
			$this->setParam($key, $value);
		}
	}
	
	public function getCompareArray()
	{
		return $this->getParam('builds');	
	}
	
	public function setCompareArray(array $compareArray)
	{
		$this->setParam('builds', $compareArray);	
	}	
}

/*****
 * class BuildCompareRow defines a row of data used in build compare feature
 *****/
class BuildCompareHeader extends JsonObject
{	
	public function __construct($compareHeaderFields = null)
	{
		parent::__construct($compareHeaderFields);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{
		foreach ($datastructure AS $key => $value) 
		{
			$this->setParam($key, $value);
		}
	}

	public function getCompareByColumn()
	{
		return $this->getParam('compareby');	
	}
	
	public function getSortingColumns()
	{
		return $this->getParam('sortings');	
	}	
	
	public function getCompareArray()
	{
		return $this->getParam('builds');	
	}

	public function setCompareByColumn($compareByColumn)
	{
		$this->setParam('compareby', $compareByColumn);	
	}
	
	public function setSortingColumns(array $sortingColumnList)
	{
		$this->setParam('sortings', $sortingColumnList);	
	}	
	
	public function setCompareArray(array $compareArray)
	{
		$this->setParam('builds', $compareArray);	
	}	
}
 
?>
