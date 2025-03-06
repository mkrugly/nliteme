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

*/

/*
 * class used to store links used in pagination
 */ 

class Pagination
{
/* 
 * private
 */
	private $next;
	private $previous;
	private $links = array();
	
	private $url;
	private $numOfPages;
	private $currentPage;
	
	private $maxNumOfPaginations;
	
	/*
	 * function generation the object
	 */ 
	private function initPagination()
	{
		// disable previous/next if necessary
		if($this->currentPage == 0) {
			$this->previous = null;
		} else {
			$this->previous = $this->url.$this->getConcatChar($this->url).'page='.($this->currentPage - 1);
		}

		if ( $this->currentPage == ($this->numOfPages - 1) ) {
			$this->next = null;
		} else {
			$this->next = $this->url.$this->getConcatChar($this->url).'page='.($this->currentPage + 1);

		}
		
		$limit = $this->maxNumOfPaginations;
		if($limit > $this->numOfPages)
		{
			$limit = $this->numOfPages;
		}
		
		if( ($this->currentPage - floor($limit/2)) < 0 ) {
			$start = 0;
		} else if ( ($this->currentPage + floor($limit/2)) >= $this->numOfPages) {
			$start = $this->numOfPages - $limit;
		} else {
			$start = $this->currentPage - floor($limit/2) ;
		}
		
		for($i = $start; $i < $start+$limit; $i++)
		{
			$this->links[$i+1] = $this->url.$this->getConcatChar($this->url).'page='.$i;			
		}	
	}
	
	private function stripPageFromUrl($url)
	{
		$strToReturn = preg_replace('/[\?\&]page=\d*/', '',$url,-1,$count);
		return $strToReturn;
	}
	
	private function getConcatChar($url)
	{
		$concatChar = '&';
		if( ! preg_match('/\?/', $url) )
		{
			$concatChar = '?';
		}
		return $concatChar;
	}

/* 
 * public
 */
	public function __construct($url, $currentPage, $numOfPages, $maxNumOfPaginations=9)
	{
		$this->url = $this->stripPageFromUrl($url);
		$this->currentPage = $currentPage;
		$this->numOfPages = $numOfPages;
		
		$this->maxNumOfPaginations = $maxNumOfPaginations;
		
		$this->initPagination();
	}
	
	/*
	 * function returns start link
	 */ 
	public function getPrevious()
	{
		return $this->previous;
	}
	
	/*
	 * function returns end link
	 */ 
	public function getNext()
	{
		return $this->next;
	}
	
	/*
	 * function 
	 */ 
	public function getPaginationList()
	{
		return $this->links;
	}
	
	/*
	 * function returns current page index
	 */ 
	public function getCurrentPageIndex()
	{
		return $this->currentPage;
	}

	/*
	 * function returns total number of pages
	 */ 
	public function getNumOfPages()
	{
		return $this->numOfPages;
	}
	
}

class PaginationAjax
{
/* 
 * private
 */
	private $link;
	private $numOfPages;
	private $currentPage;
/* 
 * public
 */
	public function __construct($link, $currentPage, $numOfPages)
	{
		$this->link = $link;
		$this->currentPage = $currentPage;
		$this->numOfPages = $numOfPages;
	}
	
	/*
	 * function 
	 */ 
	public function getLink()
	{
		return $this->link;
	}
	
	/*
	 * function returns current page index
	 */ 
	public function getCurrentPageIndex()
	{
		return $this->currentPage;
	}

	/*
	 * function returns total number of pages
	 */ 
	public function getNumOfPages()
	{
		return $this->numOfPages;
	}
}

?>
