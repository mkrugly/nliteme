<?php
/*
 * Example script to modify existing test results' file pathes e.g. ftp server address
 *
 */
   require '../libs/include.php';

   $trListQuery = new DbQueryBuilder(TABLE_PREFIX.'testresults');
   $trListQuery->addColumns(array('id','filepath'));
   //$trListQuery->addCondition('filepath like \'ftp://1%\'');
   //$trListQuery->addLimit(0,10);
   $resultSet = $trListQuery->executeQuery();
   
   
   $trDetailsModel = new TestResultModel();
   foreach ($resultSet AS $record) 
   {
       //print_r($record);
       $name = $record['filepath'];
       $preQuery = null;
       if( preg_match('/^ftp\:\/\/10\.0\.0\.1.*/',$name) )
       {
		   $name = preg_replace('/^ftp\:\/\/10\.0\.0\.1/','ftp://my_log_server_name',$name);
		   $record['filepath'] = $name;
		   
		   if ( $trDetailsModel->saveDetails($record) === false )
		   {
				print('update failed for id: '.$record['id'].'\n');
				print_r($trDetailsModel->getMessages());
				print('\n');
		   }
		   
	   }
	   else if (preg_match('/^ftp\:\/\/10\.0\.0\.2.*/',$name) )
       {
		   $name = preg_replace('/^ftp\:\/\/10\.0\.0\.2/','ftp://my_new_log_server_name',$name);
		   $record['filepath'] = $name;
		   
		   if ( $trDetailsModel->saveDetails($record) === false )
		   {
				print('update failed for id: '.$record['id'].'\n');
				print_r($trDetailsModel->getMessages());
				print('\n');
		   }
		   
	   }
   }
   

?>
