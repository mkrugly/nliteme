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

class hold server url and pathes, gets requests URLs etc, creates links for pagination

 **/

class Links {
/* private */	
	private $serverUrl  = NULL;
	private $currentUrl = NULL;
	private $queryUrl	= array();
	private $scriptName = NULL;
	
/* Public */
	/* constructor
	 * 
	 */
	public function __construct(array $_getLikeArray = array())
    {
		$this->queryUrl = (empty($_getLikeArray) ? $_GET : $_getLikeArray);
	}
	
	/* destructor
	 * 
	 */
	public function __destruct()
    {
	}

	public function GetServerUrl()
	{	
		if (! isset($this->serverUrl) )
		{
			$serverName = NULL;
			if ( isset($_SERVER['SERVER_NAME']) ) {
				$serverName = $_SERVER['SERVER_NAME'];
			} elseif ( isset($_SERVER['HTTP_HOST']) ) {
				$serverName = $_SERVER['HTTP_HOST'];
			} elseif ( isset($_SERVER['SERVER_ADDR']) ) {
				$serverName = $_SERVER['SERVER_ADDR'];
			} else {
				$serverName = 'localhost';
			}
	
			$serverProtocol = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) ? 'https' : 'http';
			$serverPort     = NULL;
			if ( isset($_SERVER['SERVER_PORT']) && !strpos($serverName, ':') &&
				( ($serverProtocol == 'http' && $_SERVER['SERVER_PORT'] != 80) ||
				($serverProtocol == 'https' && $_SERVER['SERVER_PORT'] != 443) )) 
			{
				$serverPort = $_SERVER['SERVER_PORT'];
				$serverPort  = ":".$serverPort ;
			}
			$this->serverUrl = $serverProtocol. '://' .$serverName.$serverPort;
		}	
		return $this->serverUrl;
	}
	
	public function GetCurrentUrl()
	{
		if (! isset($this->currentUrl) )
		{
			$requestURL = $_SERVER['REQUEST_URI'];
			$this->currentUrl = $this->GetServerUrl() . $requestURL;
		}
		return $this->currentUrl;
	}
	
	public function GetScriptName()
	{
		if (! isset($this->scriptName) && isset($_SERVER['SCRIPT_NAME']) )
		{
			$this->scriptName = $_SERVER['SCRIPT_NAME'];
		}
		return $this->scriptName;
	}
	
	public function GetQueryUrl()
	{
		/*
		if (! isset($this->queryUrl) ) && isset($_SERVER['QUERY_STRING']) )
		{
			$this->queryUrl = $_SERVER['QUERY_STRING'];
		}
		*/ 
		return http_build_query($this->queryUrl);
	}
	
	public function GetQueryUrlArray()
	{
		return $this->queryUrl;
	}

	public function GetModifiedQueryUrl(array $keyValueArray)
	{
		return(http_build_query(array_merge($this->queryUrl, $keyValueArray)));
	}
	
	public function GetArgFromQueryUrl($argName)
	{
		return(isset($this->queryUrl[$argName]) ? $this->queryUrl[$argName] : '');
	}	
	
	public function SetQueryUrl(array $keyValueArray)
	{
		$this->queryUrl = $keyValueArray;
	}
	
	public function SetArgFromQueryUrl($argName, $argValue)
	{
		$this->queryUrl[$argName] = $argValue;
	}

	public function UnsetArgFromQueryUrl($argName)
	{
		if(isset($this->queryUrl[$argName]))
		{
			unset($this->queryUrl[$argName]);
		}
	}
	
	public function MergeToQueryUrl(array $keyValueArray)
	{
		$this->queryUrl = array_merge($this->queryUrl, $keyValueArray);
	}

	public function GetQueryUrlKeyDiffArray(array $keyValueArray)
	{
		//print_r($this->queryUrl);
		//print_r($keyValueArray);
		//print_r(array_diff_key($this->queryUrl, $keyValueArray));
		return array_diff_key($this->queryUrl, $keyValueArray);
	}
	
	public function Get()
	{
		return array('script' => $this->GetScriptName(), 'server' => $this->GetServerUrl(), 'query' => $this->GetQueryUrl(), 'query_array' => $this->queryUrl, 'url' => $this->GetCurrentUrl());
	}
}

/*
 * class used to extract user browser info
 */ 
class UserAgent
{
/* Public */
	/* constructor
	 * 
	 */
	public function __construct()
	{
	}
	
	/* destructor
	 * 
	 */
	public function __destruct()
    {
	}

	public function isIE()
	{
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$isWindows = FALSE;
		$isMSIE = FALSE;
		if (preg_match('/windows|win32/i', $u_agent)) {
			$isWindows = TRUE;
		}
		if(preg_match('/MSIE/i',$u_agent))
		{
			$isMSIE = TRUE;
		}
		return $isWindows && $isMSIE;
	}
	
	public function isFirefox()
	{
		$isFirefox = FALSE;
		if(preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT']))
		{
			$isFirefox = TRUE;
		}
		return $isFirefox;
	}
	
	public function Get()
	{
		return $_SERVER['HTTP_USER_AGENT'];
	}
}

?>
