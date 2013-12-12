<?php
if(!function_exists('in')){function in($needle, $haystack){switch(gettype($haystack)){case 'string': return strpos($haystack, $needle) !== false; case 'array': return in_array($needle, $haystack); case 'integer': return $haystack - $needle >= 0; case 'object':return isset($haystack->$needle);default:return null;}}}
if(!function_exists('replace')){function replace($haystack, $pattern, $rep) { if (is_array($haystack)) { $pos = array_search($pattern, $haystack, true); if ($pos === false) return $haystack; return array_replace($haystack, Array($pos => $rep)); } $pp = '/^[^a-zA-Z0-9\\\\s].*' . str_replace('/', '\/', $pattern[0]) . '[imsxADSUXJu]*$/m'; if (preg_match($pp, $pattern)) return preg_replace($pattern, $rep, $haystack); else return str_replace($pattern, $rep, $haystack);}}
if(!function_exists('template')){function template($file, $data, $rootCore = '$data', $eval = true, $depth = 0) { $code = str_replace('\'', '\\\'', (file_exists($file.'.tpl') ? file_get_contents($file . '.tpl') : $file)); $parseVar = function($var, $root = '$data') use ($rootCore) { if (trim($var) == '.') { return $root; } $parts = explode('.', trim($var)); if ($parts[0] == '') $start = $rootCore; else $start = $root . '[\'' . $parts[0] . '\']'; for ($i = 1; $i < count($parts); $i++) { $start .= '[\'' . $parts[$i]. '\']'; } return $start; }; $parseVars = function($code, $root = '$data') use ($parseVar) { preg_match_all('/\{\{(?!#\/\^)((?:[^}]|\}[^}])+)\}\}/m', $code, $matches); foreach ($matches[1] as $key => $match) { $var = $parseVar($match, $root); $var = '(is_object('.$var.') && is_callable('.$var.') ? '.$var.'() : '.$var . ')'; if (trim($match) != '.') { $var = 'function_exists(\''.$match.'\') ? '.$match.'() : ' . $var; } $code = str_replace($matches[0][$key], '\' . (' . $var . ') . \'', $code); } return $code; }; $parseTpl = function($code, $root = '$data') use ($depth, $file) { preg_match_all('/\{\{>((?:[^}]|\}[^\}])+)\}\}/m', $code, $matches); foreach ($matches[1] as $key => $match) { $code = str_replace($matches[0][$key], template(dirname((file_exists($file.'.tpl') ? $file : __FILE__))."/".trim($match), null, $root, false, $depth + 1), $code); } return $code; }; preg_match_all('/\{\{(#|\^)((?:[^}]|\}[^}])+)\}\}((?:[^{]|\{[^{]|\{\{[^#\^])*)\{\{\/\2\}\}/m', $code, $matches); foreach ($matches[1] as $key => $match) { $var = $parseVar($matches[2][$key]); if ($match == '^') { $code = str_replace($matches[0][$key], '\'; if (empty('.$var.')) { $__p .= \'' . $matches[3][$key] . '\'; } $__p .= \'', $code); } else if ($match == '#') { $replacement = '\'; if (!empty('.$var.')) { if (is_array('.$var.')) foreach (' . $var . ' as $counter => $entry'.$depth.') { $__p .= \''; $replacement .= $parseVars($parseTpl($matches[3][$key], '$entry'.$depth), '$entry'.$depth) . '\'; } else { $__p .= \'' . $matches[3][$key] . '\'; }} $__p .= \''; $code = str_replace($matches[0][$key], $replacement, $code); } } $code = $parseVars($parseTpl($code, $rootCore), $rootCore); if ($eval) { $code = '$__p = \'' . $code . '\';'; eval($code); return $__p; } return $code; }}
/** 
	Snow Crystals
	=============

	This script is a simple micro-framework based on [Snow](http://github.com/AndCake/snow) 
	with proper templating, caching and custom routes.

	In order to write a new route, simply add a new directory in routes and add a new
	snow file called after the action-part of the route in that directory. The URL
	will then look like that:

		http://<myserver>/<base-dir>/<snow-flake>/<snow-crystal>
	
	In the crystal file, you need to define at least a `get` function that will be called
	for all GET requests. In addition to that, you can also define a `post` function for
	POST requests or a `put` function for PUT requests and so on.

	The result of such a function should always be an array that contains the data to be
	used in the template. Example for a URL of the format 
	`http://<myserver>/<base-dir>/user/select/<id>`:

		 file is flakes/user/select.snow
		import "lib/storage"
		fn get(id)
			store = STORAGE
			<- store.get "user", id

	The template is always called like the crystal. Example (the user object returned by
	the above crystal code has a property `name`):

		<!-- flakes/user/select.tpl -->
		<h1>Welcome {{name}}!</h1>
		<p>Glad you made it here!</p>

	Caching can be activated by setting the global `CACHING` variable to anything but 
	`false`. If you set it to a number, it will interpret the number as the minutes the 
	current page should be cached. If it is anything else, it will generate an ETag cache 
	entry.

	By having a crystal return a "decorator" attribute with it's value pointing to a template
	that has a `{{body}}` tag, you can wrap the decorator around the actual crystal's 
	template.

	Example:

	Crystal "list":

		fn get <- ['decorator': 'basehtml']

	"list"'s template:

		<span class='bold'>Hello world!</span>

	Decorator `basehtml.tpl`:

		<div class='page'>
			{{body}}
		</div>

	Will result in:

		<div class='page'>
			<span class='bold'>Hello world!</span>
		</div>

	If a 404, file not found error occurs, it tries to load the flakes/error/404.snow and
	execute it's `get`/`post`/`put` function. Afterwards it will render the 404.tpl template 
	in the same directory. In case the action is not found, only the template will be rendered.

	If any other error occurs during the execution of a custom action, it will always issue
	a 500 error. Therefore it will try to load the flakes/error/500.snow and execute it's 
	`get`/`post`/`put` function. Afterwards it will render the 500.tpl template in the same 
	directory. In case the action is not found, only the template will be rendered.

	Custom URLs can also be defined by overriding the main/home crystal, in case the URL 
	does not need to be multi-parted (example: `http://<server>/<pathname>`). The 
	`pathname` segment of the URL will be transmitted to the main/home crystal as the first
	parameter. Alternatively, for multi-part URLs (example: `http://<server>/<pathname1>/<pathname2>...`)
	you can override the error/404 crystal and route to the respective actual crystal.

	All settings are stored on a file called `settings.json`, which is read into the global
	variable `CONFIG`. Any properties you write there can be obtained from the `CONFIG` object.
**/;
ini_set("xdebug.max_nesting_level", 900);
require_once("snowcompiler.php");
function import($file) {
	if (!file_exists("" . ($file) . ".php") || (gettype($_tmp1 = filemtime("" . ($file) . ".php")) === gettype($_tmp2 = filemtime("" . ($file) . ".snow")) && ($_tmp1  <  $_tmp2 && (($_tmp1 = $_tmp2 = null) || true)) || ($_tmp1 = $_tmp2 = null))) {
		if (file_exists("" . ($file) . ".snow")) {
			$snow  =  new SnowCompiler(file_get_contents($file . ".snow"));
			$code  =  $snow->compile();
			file_put_contents("" . ($file) . ".php", "<?php\n" . ($code) . "\n?>");
		} else {
			throw(new Exception("Unable to find file " . ($file) . ""));
		}
;
		// end block;
}
;
	include_once("" . ($file) . ".php");
};
$CONFIG  =  json_decode(file_get_contents("settings.json"));
$route  =  replace($_SERVER['REQUEST_URI'], dirname($_SERVER['SCRIPT_NAME']), '');
// caching implementation (uses Last-Modified and ETags and server-side cached files);
$CACHING  =  false;
$cacheName  =  "cache/" . md5("" . ($route) . "-" . (json_encode($_GET)) . "");
$headers  =  getallheaders();
if (isset($headers['If-None-Match']) && file_exists("cache/" . ($headers['If-None-Match']) . "") || isset($headers['If-Modified-Since']) && (gettype($_tmp1 = strtotime($headers['If-Modified-Since'])) === gettype($_tmp2 = filemtime($cacheName)) && ($_tmp1  >=  $_tmp2 && (($_tmp1 = $_tmp2 = null) || true)) || ($_tmp1 = $_tmp2 = null)) && file_exists($cacheName)) {
	header("HTTP/1.1 304 Not Modified");
	die();
;
}
;
if (file_exists($cacheName)) {
	$content  =  file_get_contents($cacheName);
	list($headers,) = explode("\r\n\r\n", $content);
	$headerList  =  json_decode($headers, true);
	if ((gettype($_tmp1 = strtotime($headerList['Expires'])) === gettype($_tmp2 = time()) && ($_tmp1  >  $_tmp2 && (($_tmp1 = $_tmp2 = null) || true)) || ($_tmp1 = $_tmp2 = null))) {
		foreach ($headerList as $key => $entry) {
			header("" . ($key) . ": " . ($entry) . "");
		}
		unset($entry, $key);
;
		die(replace($content, $headers . "\r\n\r\n", ''));
	} else {
		unlink($cacheName);
	}
;
	// end block;
}
;
$list  =  explode('/', $route);
$module  =  $action  =  '';
$start  =  1;
if ((gettype($_tmp1 = count($list)) === gettype($_tmp2 = 2) && ($_tmp1  >  $_tmp2 && (($_tmp1 = $_tmp2 = null) || true)) || ($_tmp1 = $_tmp2 = null))) {
	$start  =  3;
	list(, $module, $action) = $list;
}
;
if (!$module) {
	$module  =  "main";
}
;
if (!$action) {
	$action  =  "home";
}
;
$params  =  array_slice($list, $start);
header('Server: ');
header('X-Powered-By: ');
try {
	import("flakes/" . ($module) . "/" . ($action) . "");
	$type  =  strtolower($_SERVER["REQUEST_METHOD"]);
	$data  =  call_user_func_array($type, $params);
	$data['basedir']  =  dirname($_SERVER['SCRIPT_NAME']);
	$type  =  ($type === 'get' ? '' : '-' . $type);
	if (file_exists("flakes/" . ($module) . "/" . ($action . $type) . ".tpl")) {
		$result  =  template("flakes/" . ($module) . "/" . ($action . $type) . "", $data);
	} else {
		$result  =  template("flakes/" . ($module) . "/" . ($action) . "", $data);
	}
;
	if ((!empty($data['decorator']))) {
		$result  =  template("flakes/" . ($module) . "/" . ($data['decorator']) . "", Array('body' => $result, 'basedir' => dirname($_SERVER['SCRIPT_NAME'])));
}
;
	// end block;
} catch (Exception $ex) {
$catchGuard = true;
	$page  =  (in("Unable to find file", $ex->getMessage()) ? "404" : "500");
	$result  =  Array();
	try {
		import("flakes/error/" . ($page) . "");
		$result  =  call_user_func_array($type, $params);
} catch (Exception $e) {
$catchGuard = true;
		// ignore;
}
if (!isset($catchGuard)) {
} else {
unset($catchGuard);
}
;
	$CACHING  =  false;
	header("HTTP/1.1 " . ($page) . " Problem");
	$result  =  template("flakes/error/" . ($page) . "", Array('result' => $result, 'exception' => Array('message' => $ex->getMessage(), 'trace' => $ex->getTraceAsString())));
}
if (!isset($catchGuard)) {
} else {
unset($catchGuard);
}
;
if ($CACHING !== false) {
	// if the developer chose to set a caching number;
	if (is_numeric($CACHING)) {
		$cache  =  $CACHING * 60;
		// use the Last-Modified header and Expires...;
		$headers  =  Array("Last-Modified" => gmdate('D, d M Y H:i:s') . " GMT", 
			"Expires" => gmdate('D, d M Y H:i:s', time() + $cache) . " GMT",
			"Cache-Control" => "max-age=" . ($cache) . ", must-revalidate");
	} else {
		// else use the ETag header based on content;
		$cacheName  =  $cacheName . "-" . (md5($result)) . "";
		$headers  =  Array("ETag" => '"' . basename($cacheName) . '"');
	}
;
	(!file_exists('cache') ? mkdir('cache') : null);
	foreach ($headers as $key => $entry) {
		header("" . ($key) . ": " . ($entry) . "");
	}
	unset($entry, $key);
;
	header("Content-Length: " . (strlen($result)) . "");
	file_put_contents($cacheName, "" . (json_encode($headers)) . "\r\n\r\n" . ($result) . "");
}
;
echo($result);
null;;
?>