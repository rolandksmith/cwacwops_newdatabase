function get_the_user_ip_data() {
$parser = new WhichBrowser\Parser(getallheaders());

/* For Debugging Purposes
echo "Browser: " . $parser->browser->name . "\n";
echo "Version: " . $parser->browser->version->value . "\n";
echo "OS: " . $parser->os->name . "\n";
echo "Manufacturer: " . $parser->device->manufacturer . "\n";	
echo "Device: " . $parser->device->type . "\n";
*/

$thisBrowser = $parser->browser->name;
$thisVersion = $parser->browser->version->value;
$thisOS = $parser->os->name;
$thisMfgr = $parser->device->manufacturer;
$thisDevice = $parser->device->type;


$returnArray['browser'] = $thisBrowser;
$returnArray['version'] = $thisVersion;
$returnArray['OS'] = $thisOS;
$returnArray['Mfgr'] = '';
$returnArray['device'] = $thisDevice;

return $returnArray;
}
add_action('get_the_user_ip_data', 'get_the_user_ip_data');