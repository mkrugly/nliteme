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

abstract class Controller
{
	protected $model = null;
	protected $view = null;
	
	/*
	 * constructor
	 */ 
	public function __construct()
	{
	}

	/*
	 * function define a default action
	 * has to be added to each controller
	 */ 
	abstract public function defaultAction();
	
	protected function setModel(Model $model)
	{
		$this->model = $model;
	}
	
	protected function getModel()
	{
		return $this->model;
	}
	
	protected function setView(View $view)
	{
		$this->view = $view;
	}
	
	protected function getView()
	{
		return $this->view;
	}
	
	protected function getMyRoute()
	{
		$prefix = 'com.nliteme';
		$myroute = preg_replace('/Controller/', '', get_class($this));
		return $prefix.'.'.$myroute;
	}
	
	/*
	 * function retrieves a name of Model from Controller name
	 */ 	
	protected function getModelName()
	{
		$pattern = '/Controller/';
		$replacement = 'Model';
		$subject = get_class($this);
		return preg_replace($pattern, $replacement, $subject);
	}

	/*
	 * function retrieves a name of View from Controller name
	 */ 		
	protected function getViewName()
	{
		$pattern = '/Controller/';
		$replacement = 'View';
		$subject = get_class($this);
		return preg_replace($pattern, $replacement, $subject);
	}
}

?>
