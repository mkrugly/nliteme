<?php

   require '../libs/include.php';
//Builds
	Tracer::getInstance()->setTraceLevel(LOGLEV_ERROR);
	$colList = new DbColumnConfigList();

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'createdate';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'yes'; 
    $tabentry['fieldtype'] = FIELDTYPES::_DP;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_DP;
    $colList->addColumn(new DbColumnConfig($tabentry));
    
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'testdate';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'yes'; 
    $tabentry['fieldtype'] = FIELDTYPES::_DP;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_DP;
    $colList->addColumn(new DbColumnConfig($tabentry));
    
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = 'incid';
    $tabentry['jointab'] = 'build_increments';
    $tabentry['realname'] = 'increment';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'yes'; 
    $tabentry['fieldtype'] = FIELDTYPES::_SS;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_SM;
    $colList->addColumn(new DbColumnConfig($tabentry));
        
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = 'buildid';
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'build';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_SS;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_SM;
    $colList->addColumn(new DbColumnConfig($tabentry));
    
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'description';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'no';
    $tabentry['showable'] = 'no';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_IT;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_IT;
    $colList->addColumn(new DbColumnConfig($tabentry));
   
    Config::getInstance()->updateColumnConfig($colList, 'builds');
 
// Build_increments      
	$colList = new DbColumnConfigList();

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'createdate';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'yes'; 
    $tabentry['fieldtype'] = FIELDTYPES::_DP;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_DP;
    $colList->addColumn(new DbColumnConfig($tabentry));
    
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = 'incid';
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'increment';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_SS;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_SM;
    $colList->addColumn(new DbColumnConfig($tabentry));

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'description';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'no';
    $tabentry['showable'] = 'no';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_IT;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_IT;
    $colList->addColumn(new DbColumnConfig($tabentry));
        
    Config::getInstance()->updateColumnConfig($colList, 'build_increments');
    
// test suites    
	$colList = new DbColumnConfigList();

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'createdate';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'yes'; 
    $tabentry['fieldtype'] = FIELDTYPES::_DP;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_DP;
    $colList->addColumn(new DbColumnConfig($tabentry));
    
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = 'tsid';
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'tsname';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_SS;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_SM;
    $colList->addColumn(new DbColumnConfig($tabentry));

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'description';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'no';
    $tabentry['showable'] = 'no';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_IT;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_IT;
    $colList->addColumn(new DbColumnConfig($tabentry));
        
    Config::getInstance()->updateColumnConfig($colList, 'testsuites');
    
// test cases    
	$colList = new DbColumnConfigList();
        
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = 'tcid';
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'tcname';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_SS;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_SM;
    $colList->addColumn(new DbColumnConfig($tabentry));
    
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = 'fid';
    $tabentry['jointab'] = 'features';
    $tabentry['realname'] = 'fname';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'yes'; 
    $tabentry['fieldtype'] = FIELDTYPES::_SS;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_SM;
    $colList->addColumn(new DbColumnConfig($tabentry));
       
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'coverage';
    $tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'no';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'yes';    
    $tabentry['fieldtype'] = FIELDTYPES::_IT; 
    $tabentry['valuetype'] = VALUETYPES::_REL;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_IT;  
    $colList->addColumn(new DbColumnConfig($tabentry));

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'createdate';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'yes'; 
    $tabentry['fieldtype'] = FIELDTYPES::_DP;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_DP;
    $colList->addColumn(new DbColumnConfig($tabentry));

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'description';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'no';
    $tabentry['showable'] = 'no';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_IT;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_IT;
    $colList->addColumn(new DbColumnConfig($tabentry));
           
    Config::getInstance()->updateColumnConfig($colList, 'testcases');

// test lines    
	$colList = new DbColumnConfigList();

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'createdate';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'yes'; 
    $tabentry['fieldtype'] = FIELDTYPES::_DP;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_DP;
    $colList->addColumn(new DbColumnConfig($tabentry));
    
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = 'tlid';
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'tlname';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_SS;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_SM;
    $colList->addColumn(new DbColumnConfig($tabentry));

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'description';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'no';
    $tabentry['showable'] = 'no';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_IT;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_IT;
    $colList->addColumn(new DbColumnConfig($tabentry));
        
    Config::getInstance()->updateColumnConfig($colList, 'testlines');
	
	$colList = new DbColumnConfigList();
        
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = 'fid';
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'fname';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_SS;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_SM;
    $colList->addColumn(new DbColumnConfig($tabentry));

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'hlink';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'no';
    $tabentry['showable'] = 'no';
    $tabentry['iteratable'] = 'yes'; 
    $tabentry['fieldtype'] = FIELDTYPES::_HL;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_IT;
    $colList->addColumn(new DbColumnConfig($tabentry));
    
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'createdate';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'yes';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'yes'; 
    $tabentry['fieldtype'] = FIELDTYPES::_DP;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_DP;
    $colList->addColumn(new DbColumnConfig($tabentry));

    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'description';
	$tabentry['predefined'] = null;
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'no';
    $tabentry['showable'] = 'no';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_IT;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_IT;
    $colList->addColumn(new DbColumnConfig($tabentry));
    
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'num_testcases';
    $tabentry['predefined'] = 'no';
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'no';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_IT;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_IT;
    $colList->addColumn(new DbColumnConfig($tabentry));
    
    $tabentry = array();
    $tabentry['enabled'] = 'yes';
    $tabentry['index'] = null;
    $tabentry['jointab'] = null;
    $tabentry['realname'] = 'coverage';
    $tabentry['predefined'] = 'no';
    $tabentry['value'] = null;
	$tabentry['values'] = array();
	$tabentry['searchable'] = 'no';
    $tabentry['showable'] = 'yes';
    $tabentry['iteratable'] = 'no'; 
    $tabentry['fieldtype'] = FIELDTYPES::_IT;
    $tabentry['valuetype'] = VALUETYPES::_REL;
    $tabentry['searcherfieldtype'] = FIELDTYPES::_IT;
    $colList->addColumn(new DbColumnConfig($tabentry));

	Config::getInstance()->updateColumnConfig($colList, 'features');	

print_r(Tracer::getInstance()->getTraces());
print("\n");

?>
