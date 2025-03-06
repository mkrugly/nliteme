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

* Tracer class is a singleton that takes care of trace collection
* 
 */

define('LOGLEV_NONE',   0);
define('LOGLEV_FATAL',  1); 
define('LOGLEV_ERROR',  2); 
define('LOGLEV_WARNING',3);
define('LOGLEV_INFO', 	4); 
define('LOGLEV_DEBUG',  5);
define('LOGLEV_ALL',    0x3f);


class Tracer
{
	private static $instance;
	private static $traceList;
	private static $logLevelMask = LOGLEV_NONE;
	private static $printToErrorLog = false;
	private function __construct() {}
	private function __clone() {}

    /*
     * function returns a reference to Manager singleton instance 
     */ 
	public static function getInstance()
	{
        if (! isset(self::$instance) ) {
            self::$instance = new Tracer();
			self::$traceList = array();
			self::$logLevelMask = LOGLEV_NONE;
        }
        return self::$instance;
    }
	
	public function setTraceLevel($level = LOGLEV_NONE)
	{
		self::$logLevelMask = LOGLEV_ALL & ((1 << ($level + 1)) - 1);
	}
	
	public function setPrintToErrorLog($printToErrorLog = true)
	{
		self::$printToErrorLog = $printToErrorLog;
	}	
	
	/*
	 * function add a $traceString to traces
	 * optional $btFlag specifies if backtrace shall be collected (default: no)
	 */
	public function log($traceString, $loglevel, $btFlag = False)
	{
		if( self::getLevelMask($loglevel) & self::$logLevelMask)
		{
			$str = date(DATE_ATOM)."\t".self::levelToStr($loglevel)."\t".$traceString;
			array_push(self::$traceList, $str);
			if(self::$printToErrorLog === true)
			{
				error_log($str);
			}
			if($btFlag === true || $loglevel === LOGLEV_FATAL)
			{
				$str = self::debug_backtrace_string();
				array_push(self::$traceList, $str);
			}
		}
	}
	
	/*
	 * function return a list of collected traces as array
	 */
	public function getTraces()
	{
		return self::$traceList;
	}
	
	/*
	 * function return a list of collected traces as string
	 */	
	public function getTracesAsString()
	{
		return implode("\n", self::getTraces());
	}

	private function levelToStr($level)
    {
        $loglevels = array(
            LOGLEV_FATAL => 'FATAL',
            LOGLEV_ERROR => 'ERROR',
            LOGLEV_WARNING => 'WARNING',
            LOGLEV_INFO => 'INFO',
            LOGLEV_DEBUG => 'DEBUG'
        );

        return $loglevels[$level];
    }
    
    private function getLevelMask($level)
    {
        return (1 << $level);
    }
    
	private function debug_backtrace_string() 
	{ 
        ob_start(); 
        debug_print_backtrace(); 
        $btstring = ob_get_contents(); 
        ob_end_clean(); 

        return $btstring; 
    }
}

?>
