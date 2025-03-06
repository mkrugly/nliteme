<?php
/*
 * Use this script to enable automatic external server prefix addition to feature names if the link is not explictly defined for the feature
 * NOTE. Can be used if the feature name is stored as a feature ID in the external server
 * NOTE1. If the uploaded feature data contains already the hlink, the automatic prefix addition is not done.
 *
 */
require '../libs/include.php';

$preference = Config::getInstance()->getPreference('defect_otherserver_hlink');
if(empty($preference))
{
   print("The preference for the external Defects server is not set\n");
} else {
   print("The preference for the external Defects server is set. Removing existing.\n");
   Config::getInstance()->unsetPreference('defect_otherserver_hlink');
}

Config::getInstance()->setPreference('defect_otherserver_hlink','https://<defect server url with all the URL argumnets up to defect id>');
$preference = Config::getInstance()->getPreference('defect_otherserver_hlink');

if(empty($preference))
{
   print("The preference for the external Defects server is not set\n");
} else {
   print_r($preference);
}
print("\n");
?>
