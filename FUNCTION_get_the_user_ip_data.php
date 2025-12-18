function get_the_user_ip_data() {
$parser = new WhichBrowser\Parser(getallheaders());
$thisBrowser = $parser->browser->name;
$thisVersion = $parser->browser->version->toString();
$thisOS = '';
if (isset($parser->os->name)) {
	$thisOS = $parser->os->name;
}
$thisMfgr = '';
if (isset($parser->device->manufacturer)) {
	$thisMfgr = $parser->device->manufacturer;
}
$thisDevice = $parser->device->type;
$returnArray['browser'] = $thisBrowser;
$returnArray['version'] = $thisVersion;
$returnArray['OS'] = $thisOS;
$returnArray['Mfgr'] = $thisMfgr;
$returnArray['device'] = $thisDevice;

return $returnArray;
}
add_action('get_the_user_ip_data', 'get_the_user_ip_data');