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

/*
 * Abstract class that aggregates the common functions to handle ColumnConfigs from preferences
 * 
 */ 

abstract class ConfigBasedView extends TemplateView
{
	protected $columnConfig = null; // DbColumnList containing column config information retrieved from preferences
	protected $enabledColumnConfig = null; // DbColumnList containing column config information for enabled columns retrieved from preferences

	/*
	 * constructor
	 */ 
	public function __construct(Model $model, $columnConfigName)
	{
		parent::__construct($model);
		$this->getColumnConfig($columnConfigName);
		$this->getEnabledColumnList();
	}

	/*
	 * function return a dbColumnList with columns configuration from preferences
	 */ 
	protected function getColumnConfig($columnConfigName=null)
	{
		if( empty($this->columnConfig) )
		{
			$this->columnConfig = Config::getInstance()->getColumnConfig($columnConfigName);
		}
		return $this->columnConfig;
	}

	/*
	 * function return a dbColumnList with showable columns
	 */ 
	protected function getEnabledColumnList()
	{
		if( empty($this->enabledColumnConfig) )
		{
			$showableColumnList = new DbColumnList();
			$iterator = $this->getColumnConfig()->getIterator();
			while( $iterator->valid() )
			{
				if($iterator->current()->is_enabled())
				{
					$showableColumnList->addColumn(clone $iterator->current());
				}	
				$iterator->next();
			}
			$this->enabledColumnConfig = $showableColumnList;
		}
		return $this->enabledColumnConfig;
	}
	
	/*
	 * function return a dbColumnList with showable columns
	 */ 
	protected function getShowableColumnList()
	{
		$showableColumnList = new DbColumnList();
		$iterator = $this->getEnabledColumnList()->getIterator();
		while( $iterator->valid() )
		{
			if($iterator->current()->is_showable())
			{
				$current = $iterator->current();
				$showableColumnList->addColumn($current);
			}	
			$iterator->next();
		}
		return $showableColumnList;
	}
	
	/*
	 * function return a dbColumnList with showable columns
	 */ 
	protected function getIteratableColumnList()
	{
		$showableColumnList = new DbColumnList();
		$iterator = $this->getEnabledColumnList()->getIterator();
		while( $iterator->valid() )
		{
			if($iterator->current()->is_iteratable())
			{
				$current = $iterator->current();
				$showableColumnList->addColumn($current);
			}	
			$iterator->next();
		}
		return $showableColumnList;
	}
}
?>
