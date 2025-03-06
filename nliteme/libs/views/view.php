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

abstract class View
{
	protected $model = null;	// holds a correspoding model object	
	protected $prefix = 'com.nliteme';
	protected $usejson = False;
	public $content = null;
	
	/*
	 * constructor
	 */ 
	public function __construct(Model $model)
	{
		$this->model = $model;
		$this->usejson = isset($_GET['usejson']) ? True : False;
	}
	
	/*
	 * function to display the template
	 */ 
	public function display()
	{
		$this->prepareContent();
		if ($this->usejson === True)
		{
			header('Content-Type: application/json');
		}
		echo $this->content;
	}
	
	/*
	 * function that prepares a content to be used for rendering the temlate (called in $this-display()
	 */ 
	abstract protected function prepareContent();	
}

abstract class JsonView extends View
{
	/*
	 * constructor
	 */ 
	public function __construct(Model $model)
	{
		parent::__construct($model);
		$this->usejson = True;
	}
	
	protected function prepareContent()
	{

	}
}

abstract class TemplateView extends View
{

	protected $template = null; // twig environment object with loaded template file
	protected $messages = null;	// Messenger object
	protected $link = null;
	protected $text = null;		// Text4Twig object (helper to make it possible to use Text singleton _ function in twig template)
	protected $useragent = null; // UserAgent object (holds info about client browser)
	
	/*
	 * constructor
	 */ 
	public function __construct(Model $model)
	{
		parent::__construct($model);
		$this->model = $model;
		$this->messages = new Messenger();
		$this->link = new Links();
		$this->text = new Text4Twig();
		$this->useragent = new UserAgent();
		$this->content = array(); 	// array containing infos for template
	}
	
	/*
	 * 
	 */ 
	protected function setMessages(array $messages, $okOrNok)
	{
		$okOrNok === true ? $severity = MSG::_INFO : $severity = MSG::_ERROR;
		$this->messages->setMessages($messages);
		$this->messages->setSeverity($severity);
	}
	
	/*
	 * 
	 */ 
	protected function getMessages()
	{
		return $this->messages;
	}
	
	/*
	 * 
	 */
	protected function setTemplate($template)
	{
		$this->template = TemplateEnv::getInstance()->load($template);
	}
	
	/*
	 * 
	 */
	protected function getTemplate()
	{
		return $this->template;
	}
	
	/*
	 * function to display the template
	 */ 
	public function display()
	{
		$this->prepareContent();
		if($this->usejson === True)
		{
			$this->content = json_encode( (array) array('content' => $this->content, 'messages' => $this->getMessages(), 'link' => $this->link->Get(), 'Text' => $this->text, 'userbrowser' => $this->useragent->Get()), JSON_FORCE_OBJECT);
			header('Content-Type: application/json');
			echo $this->content;
		} else {		
			$this->template->display(array('content' => $this->content, 'messages' => $this->getMessages(), 'link' => $this->link, 'Text' => $this->text, 'userbrowser' => $this->useragent));
		}
	}
	
	/*
	 * function return a rendered template ready to echo
	 */ 
	public function render()
	{
		$this->prepareContent();
		return $this->template->render(array('content' => $this->content, 'messages' => $this->getMessages(), 'link' => $this->link, 'Text' => $this->text, 'userbrowser' => $this->useragent));
	}
	
	/*
	 * function sets proper action e.g. action=com.nliteme.TestResults.showList 
	 */ 	
	public function setSubmitAction($submitAction)
	{
		$this->content['submitAction'] = $submitAction;
	}
	
	/*
	 * function sets link property 
	 */ 	
	public function setLink(Links $link)
	{
		$this->link = $link;
	}
}
?>
