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

/*
* Router class parses the input params to get the controller object and action
* returns Route object
*/
class Router
{
	private static $instance;
	private function __construct() {}
	private function __clone() {}
	
	/*
	 * format for controller actions argument is as follows
	 * action=com.nliteme.<configuration_reference>.<mvc_name>.<action_name>
	 * - configuration_reference points to suitable dbcolumnconfig from preferences
	 * - mvc_name - prefix for ModelControllerView classes beloning together
	 * - action_name - controller class member function name to be called
	 */ 
	
	/*
	 * function parses $_GET
	 */ 
	public static function getRoute()
	{
		$controller = 'MainDashboardController'; // default is MainDashboardController
		$action = 'defaultAction';			   // each controller implements the default function (see Controller abstract class)
		if(isset($_GET['action'])) {
			$arr = explode('.', $_GET['action']);
			if( $arr[0] === 'com' && $arr[1] === 'nliteme' ) {
				// get specific controller name
				if(class_exists($arr[2].'Controller')) {
					$controller = $arr[2].'Controller';
				} else {
					$controller = 'EmptyController'; // use empty controller if action specific controller does not exist
				}
				// get function name
				if(!empty($arr[3]) && method_exists($controller,$arr[3]))
				{
					$action = $arr[3];
				}
			}
		}
		return new Route(new $controller, $action);
	}
}

/*
 * Class Route encapsulates Controller object and requested action
 */
class Route 
{
	private $controller;
	private $action;
	
	public function __construct(Controller $controller, $action)
	{
		$this->controller = $controller;
		$this->action = $action;
	}
	
	public function getController()
	{
		return $this->controller;
	}
	
	public function getAction()
	{
		return $this->action;
	}
}

?>
