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

//Includes
    require_once(dirname(__FILE__).'/utils.php');
    require_once(dirname(__FILE__).'/dbconnect.php');
    require_once(dirname(__FILE__).'/classes/tracer.php');
    require_once(dirname(__FILE__).'/classes/text.php');
    require_once(dirname(__FILE__).'/classes/dbconnectionfactory.php');
    require_once(dirname(__FILE__).'/classes/precompiledqeueries.php');
    require_once(dirname(__FILE__).'/classes/templateenv.php');
    require_once(dirname(__FILE__).'/classes/jsonobject.php');
    require_once(dirname(__FILE__).'/classes/messenger.php');
    require_once(dirname(__FILE__).'/classes/dbcolumn.php');    
    require_once(dirname(__FILE__).'/classes/dbcolumnlist.php');
    require_once(dirname(__FILE__).'/classes/dbcolumnconfig.php');    
    require_once(dirname(__FILE__).'/classes/dbcolumnconfiglist.php');
    require_once(dirname(__FILE__).'/classes/dbrecord.php');
    require_once(dirname(__FILE__).'/classes/build.php');
    require_once(dirname(__FILE__).'/classes/buildincrement.php');
    require_once(dirname(__FILE__).'/classes/testcase.php');
    require_once(dirname(__FILE__).'/classes/testsuite.php');
    require_once(dirname(__FILE__).'/classes/testline.php');
    require_once(dirname(__FILE__).'/classes/testresult.php'); 
    require_once(dirname(__FILE__).'/classes/feature.php');
    require_once(dirname(__FILE__).'/classes/config.php');    
    require_once(dirname(__FILE__).'/classes/dbquerybuilder.php');
    require_once(dirname(__FILE__).'/classes/links.php');
    require_once(dirname(__FILE__).'/classes/pagination.php');
    require_once(dirname(__FILE__).'/classes/viewmodel.php');
    //require_once(dirname(__FILE__).'/classes/manager.php');
    require_once(dirname(__FILE__).'/classes/recordmanager.php');    
    require_once(dirname(__FILE__).'/classes/uploader.php');
    require_once(dirname(__FILE__).'/classes/recorduploader.php');     
    require_once(dirname(__FILE__).'/classes/massuploader.php');
    require_once(dirname(__FILE__).'/classes/router.php');
    require_once(dirname(__FILE__).'/classes/testresultstats.php');
    require_once(dirname(__FILE__).'/classes/widgetconfig.php');
    require_once(dirname(__FILE__).'/classes/dashboardconfig.php');
    require_once(dirname(__FILE__).'/classes/description.php');

// TBD. load mvc on demand    
    // models
    require_once(dirname(__FILE__).'/models/model.php');
    require_once(dirname(__FILE__).'/models/emptymodel.php');
    require_once(dirname(__FILE__).'/models/detailsmodel.php');
    require_once(dirname(__FILE__).'/models/testresultmodel.php');
    require_once(dirname(__FILE__).'/models/listmodel.php');
    require_once(dirname(__FILE__).'/models/mainmodel.php');
    require_once(dirname(__FILE__).'/models/dashboardmodel.php');
    require_once(dirname(__FILE__).'/models/dashboardtabmodel.php');
    require_once(dirname(__FILE__).'/models/searchermodel.php');
    require_once(dirname(__FILE__).'/models/testresultsmodel.php');
    require_once(dirname(__FILE__).'/models/testresultsratemodel.php');
    require_once(dirname(__FILE__).'/models/testactivitiesmodel.php');
    require_once(dirname(__FILE__).'/models/buildcomparemodel.php');
    require_once(dirname(__FILE__).'/models/highlevelreportmodel.php');    
    // views
    require_once(dirname(__FILE__).'/views/view.php');
    require_once(dirname(__FILE__).'/views/configbasedview.php');
    require_once(dirname(__FILE__).'/views/emptyview.php');
    require_once(dirname(__FILE__).'/views/detailsview.php');
    require_once(dirname(__FILE__).'/views/listview.php');
    require_once(dirname(__FILE__).'/views/mainview.php');
    require_once(dirname(__FILE__).'/views/dashboardview.php');
    require_once(dirname(__FILE__).'/views/dashboardtabview.php');
    require_once(dirname(__FILE__).'/views/searcherview.php');    
    require_once(dirname(__FILE__).'/views/testactivitiesview.php');
    require_once(dirname(__FILE__).'/views/buildcompareview.php');
    require_once(dirname(__FILE__).'/views/highlevelreportview.php');     
    // controllers
    require_once(dirname(__FILE__).'/controllers/controller.php');
    require_once(dirname(__FILE__).'/controllers/emptycontroller.php');
    require_once(dirname(__FILE__).'/controllers/maincontroller.php');   
    require_once(dirname(__FILE__).'/controllers/dashboardcontroller.php'); 
    require_once(dirname(__FILE__).'/controllers/dashboardtabcontroller.php'); 
    require_once(dirname(__FILE__).'/controllers/searchercontroller.php');
    require_once(dirname(__FILE__).'/controllers/detailslistscontroller.php');
    require_once(dirname(__FILE__).'/controllers/widgetcontroller.php');
    require_once(dirname(__FILE__).'/controllers/testactivitiescontroller.php');
    require_once(dirname(__FILE__).'/controllers/buildcomparecontroller.php');
    require_once(dirname(__FILE__).'/controllers/highlevelreportcontroller.php');     
    
    // temporarily added to print out in html mode
    //require_once(dirname(__FILE__).'/adodb5/tohtml.inc.php');
    
    //Tracer::getInstance()->setTraceLevel(LOGLEV_ERROR);
	//Tracer::getInstance()->setPrintToErrorLog(true);
?>
