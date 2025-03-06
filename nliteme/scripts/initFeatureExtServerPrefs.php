<?php
/*
 * Use this script to enable automatic external server prefix addition to feature names i tthe link is not explictly defined for the feature
 * NOTE. Can be used if the feature name is stored as a feature ID in the external server
 * NOTE1. If the uploaded feature data contains already the hlink, the automatic prefix addition is not done.
 *
 */
require '../libs/include.php';

$preference = Config::getInstance()->getPreference('feature_otherserver_hlink');
if(empty($preference))
{
   print("The preferences for the External server for Features are not set\n");
} else {
   print("The preferences for the External server for Features are set. Removing existing.\n");
   Config::getInstance()->unsetPreference('feature_otherserver_hlink');
}

Config::getInstance()->setPreference('feature_otherserver_hlink','https://<external_feature_server_url_prefix>');
$preference = Config::getInstance()->getPreference('feature_otherserver_hlink');

if(empty($preference))
{
   print("The preferences for the External server for Features are not set\n");
} else {
   print_r($preference);
}
print("\n");
?>
