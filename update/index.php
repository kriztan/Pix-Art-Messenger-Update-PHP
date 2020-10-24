<?php
///////////////////////////////
//// INPUT ////////////////////
$versionCode = "184"; //184 = first versionCode with automatic updates via github, so no further changes needed.
//////////////////////////////
//// Code ////////////////////
//// Get the string from the URL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://codeberg.org/api/v1/repos/kriztan/Pix-Art-Messenger/releases');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$json = curl_exec($ch);
curl_close($ch);
//// Decode the JSON string
$data = json_decode($json);
/////////////////////////////
$counter = 0;
$prerelease = true;
$draft = true;
for ($i = 0; $i <= 25; $i++) {
	$prerelease = isBoolean($data[$i]->prerelease);
	$draft = isBoolean($data[$i]->draft);
	if (!$prerelease AND !$draft) {
		$counter = $i;
		break;
	}
}
$downloadlink = $data[$counter]->assets[0]->browser_download_url;  // set to 1 if there is more than one apk
$latestVersion = $data[$counter]->tag_name;
$changes = utf8_encode($data[$counter]->body);
$size = formatBytes($data[$counter]->assets[0]->size);  // set to 1 if there is more than one apk
//////
$downloadlink = strtolower($downloadlink);
if (strlen($json) >= 1500) {
	$success = true;
} else {
	$success = false;
}
$version = array(
'success' => $success,
'latestVersion' => "$latestVersion",
'latestVersionCode' => "$versionCode",
'changelog' => "$changes",
'filesize' => "$size",
'appURI' => "$downloadlink",
);
//////////////////////////////
//// OUTPUT //////////////////
echo json_encode($version,JSON_UNESCAPED_SLASHES);
//////////////////////////////
//// Functions ///////////////
function formatBytes($bytes, $precision = 2) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB');
	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= pow(1024, $pow);
	return number_format($bytes, $precision, ",", ".") . ' ' . $units[$pow];
}
//////////////////////////////
function getSize($url) {
	// cache files are created like cache/abcdef123456...
	$cacheFile = 'cache' . DIRECTORY_SEPARATOR . md5($url);

	if (file_exists($cacheFile)) {
		$fh = fopen($cacheFile, 'r');
		$size = fread($fh, filesize($cacheFile));
		if ($size > 0) {
			return $size;
		}
	}
	$size_raw[] = get_headers($url,1);
	$cache = $size_raw[0]['Content-Length'];
	$fh = fopen($cacheFile, 'w');
	fwrite($fh, $cache);
	fclose($fh);
	return $cache;
}
//////////////////////////////
function isBoolean($value) {
	if ($value) {
		return true;
	} else {
		return false;
	}
}
//////////////////////////////
//////////////////////////////
?>