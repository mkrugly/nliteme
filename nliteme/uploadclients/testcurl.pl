#!perl

use strict;
use WWW::Curl::Easy 4.14;
use WWW::Curl::Form;

my $target_url = 'http://localhost/nliteme/upload.php';

############# Example on how to upload a results stored in json file
#
#my $file_mimetype = 'application/json';		# mime type of the file
#my $filename = './build.json';			# file to upload
#my $curlf = WWW::Curl::Form->new;			# create a form
#
#$curlf->formadd("uploadtype", "builds");	# uploadtype specifies that this is builds upload
## $curlf->formaddfile(FILENAME, DESCRIPTION, TYPE)
## IMPORTANT!! DESCRIPTION has to be 'file' !!!
#$curlf->formaddfile($filename, 'file', $file_mimetype);	 
#my $curl = WWW::Curl::Easy->new() or die $!; # create a curl object
#$curl->setopt(CURLOPT_URL, $target_url);	 # set target url
#$curl->setopt(CURLOPT_HTTPPOST, $curlf);	 # add the form to post
#
#my $retcode = $curl->perform();			 # post
#print($retcode."\n");

############ Example on how to upload a results as json string
my $jsonstring = '{"0":{
"filepath":{"realname":"filepath","value":"ftp:\/\/localhost\/logs\/DailyBuilds\/30.00_2013-11-27-01-03\/perlTest"}
,"createdate":{"realname":"createdate","value":"2017-03-11 02:02:00"}
,"build":{"realname":"build","value":"30.00_2013-11-27-01-03"}
,"increment":{"realname":"increment","value":"30.00"}
,"tsname":{"realname":"tsname","value":"MyTestSuite"}
,"extracolumn_0":{"realname":"extracolumn_0","value":"UE1"}
,"extracolumn_1":{"realname":"extracolumn_1","value":"1000"}
,"extracolumn_2":{"realname":"extracolumn_2","value":"1"}
,"extracolumn_3":{"realname":"extracolumn_3","value":"95.05"}
,"tcname":{"realname":"tcname","value":"perlTest"}
,"tcverdict":{"realname":"tcverdict","value":"ok"}
,"description":{"realname":"description","value":"Detailed description:"}
,"duration":{"realname":"duration","value":50}
,"tlname":{"realname":"tlname","value":"Line_1"}
}}';

$curlf = WWW::Curl::Form->new;			# create a form
$curlf->formadd("uploadtype", "testresults");	# uploadtype specifies that this is testresults upload
$curlf->formadd("jsonstring", $jsonstring);	# add json string to a form field with name "jsonstring"
$curl = WWW::Curl::Easy->new() or die $!; # create a curl object
$curl->setopt(CURLOPT_URL, $target_url);	 # set target url
$curl->setopt(CURLOPT_HTTPPOST, $curlf);	 # add the form to post

my $retcode = $curl->perform();			 # post
print($retcode."\n");
