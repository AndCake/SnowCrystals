<?php
if(!function_exists('in')){function in($needle, $haystack){switch(gettype($haystack)){case 'string': return strpos($haystack, $needle) !== false; case 'array': return in_array($needle, $haystack); case 'integer': return $haystack - $needle >= 0; case 'object':return isset($haystack->$needle);default:return null;}}}
if(!function_exists('replace')){function replace($haystack, $pattern, $rep) { if (is_array($haystack)) { $pos = array_search($pattern, $haystack, true); if ($pos === false) return $haystack; return array_replace($haystack, Array($pos => $rep)); } $pp = '/^[^a-zA-Z0-9\\\\s].*' . str_replace('/', '\/', $pattern[0]) . '[imsxADSUXJu]*$/m'; if (preg_match($pp, $pattern)) return preg_replace($pattern, $rep, $haystack); else return str_replace($pattern, $rep, $haystack);}}
if(!function_exists('template')){function template($file, $data, $rootCore = '$data', $eval = true, $depth = 0) { $code = str_replace('\'', '\\\'', (file_exists($file.'.tpl') ? file_get_contents($file . '.tpl') : $file)); $parseVar = function($var, $root = '$data') use ($rootCore) { if (trim($var) == '.') { return $root; } $parts = explode('.', trim($var)); if ($parts[0] == '') $start = $rootCore; else $start = $root . '[\'' . $parts[0] . '\']'; for ($i = 1; $i < count($parts); $i++) { $start .= '[\'' . $parts[$i]. '\']'; } return $start; }; $parseVars = function($code, $root = '$data') use ($parseVar) { preg_match_all('/\{\{(?!#\/\^)((?:[^}]|\}[^}])+)\}\}/m', $code, $matches); foreach ($matches[1] as $key => $match) { $var = $parseVar($match, $root); $var = '(is_object('.$var.') && is_callable('.$var.') ? '.$var.'() : '.$var . ')'; if (trim($match) != '.') { $var = 'function_exists(\''.$match.'\') ? '.$match.'() : ' . $var; } $code = str_replace($matches[0][$key], '\' . (' . $var . ') . \'', $code); } return $code; }; $parseTpl = function($code, $root = '$data') use ($depth, $file) { preg_match_all('/\{\{>((?:[^}]|\}[^\}])+)\}\}/m', $code, $matches); foreach ($matches[1] as $key => $match) { $code = str_replace($matches[0][$key], template(dirname((file_exists($file.'.tpl') ? $file : __FILE__))."/".trim($match), null, $root, false, $depth + 1), $code); } return $code; }; preg_match_all('/\{\{(#|\^)((?:[^}]|\}[^}])+)\}\}((?:[^{]|\{[^{]|\{\{[^#\^])*)\{\{\/\2\}\}/m', $code, $matches); foreach ($matches[1] as $key => $match) { $var = $parseVar($matches[2][$key]); if ($match == '^') { $code = str_replace($matches[0][$key], '\'; if (empty('.$var.')) { $__p .= \'' . $matches[3][$key] . '\'; } $__p .= \'', $code); } else if ($match == '#') { $replacement = '\'; if (!empty('.$var.')) { if (is_array('.$var.')) foreach (' . $var . ' as $counter => $entry'.$depth.') { $__p .= \''; $replacement .= $parseVars($parseTpl($matches[3][$key], '$entry'.$depth), '$entry'.$depth) . '\'; } else { $__p .= \'' . $matches[3][$key] . '\'; }} $__p .= \''; $code = str_replace($matches[0][$key], $replacement, $code); } } $code = $parseVars($parseTpl($code, $rootCore), $rootCore); if ($eval) { $code = '$__p = \'' . $code . '\';'; eval($code); return $__p; } return $code; }}
/** 
	Class Storage
	=============

	This class provides a basic persistent data storage.
	You can use it by either creating a new instance of the Storage
	or by fetching the global `STORAGE` object.

	Override the methods of this class in order to implement an actual
	database adapter.

	Example:

		"get a Storage instance"
		store = new Storage()

		"alternatively, get the existing Storage instance"
		store = STORAGE

	The following methods are provided in order to retrieve data from
	the storage and write data into the storage.
**/;
class Storage {
	private $handle = null;
	private $read = 0;
	public $name = "JSON storage";
	function __construct() {global $STORAGE;

		$STORAGE  =  $this;
		$this->_load();
;
	}
	function _load() {global $CONFIG;

		$conf  =  $CONFIG;
		if ((gettype($_tmp1 = filemtime($conf->database)) === gettype($_tmp2 = $this->read) && ($_tmp1  >  $_tmp2 && (($_tmp1 = $_tmp2 = null) || true)) || ($_tmp1 = $_tmp2 = null))) {
			$this->handle  =  json_decode(file_get_contents($conf->database), true);
			$this->read  =  time();
}
;
	/**
		save() -> void
		---------------------------------------

		This method saves/commits the current storage contents onto the harddrive.
		Only after saving changes, others can access them.
	*/;
		// end block;
	}
	function save() {global $CONFIG;

		$conf  =  $CONFIG;
		file_put_contents($conf->database, json_encode($this->handle));
	}
	/**
		get(type, attribute[, value]) -> Object
		---------------------------------------
		- type (String) - the type in which to search for the attribute value
		- attribute (Mixed) - either the name of the attribute to retrieve it's value for or the target object's ID
		- value (Mixed) - the value of the attribute of the object to be retrieved (optional)

		This method returns an object of the given `type`, which was previously stored into the Storage. 
		In case the object cannot be found for the given `type`, the method will return `null`.

		If multiple objects match the attribute-value combination of the given `type`, this method will
		only return the first entry. To retrieve multiple entries, use the Storage.find() method.

		Example 1:

			'get the user with ID "test@example.org"'
			store = STORAGE
			store.get "user", "test@example.org"

		Example 2:

			'get the user with the name "Martha"'
			store = STORAGE
			store.get "user", "name", "Martha"
	*/
	function get($type, $attr, $value = null) {
		$this->_load();
;
		if (isset($value)) {
			foreach ($this->handle[$type] as $key => $el) {
				if (isset($el[$attr]) && $el[$attr] === $value) {
					return $el;
;
			}
;
				// end block;
			}
			unset($el, $key);
;
			return null;
;
		} else {
			return $this->handle[$type][$attr];
;
		}
;
	/**
		set(type, obj[, id]) -> Mixed
		-----------------------------
		- type (String) - the type in which to store the object
		- obj (Object) - the object to store
		- id (Mixed) - the index under which the object should be found (optional).

		This method sets a new object into the given `type`. If the record at the given
		`id` already exists, it will be overidden. If it does not exist, the object will
		be added as a new record. If no `id` is given, it will always add the respective
		object.

		Example 1:

			"sets the 'Martha' object to the index 'test@example.org'"
			store = STORAGE
			store.set "user", ['name': 'Martha', 'age': 21], "test@example.org"

			"will add a new record with an automatically generated ID"
			id = store.set "user", ['name': 'Carl', 'age': 23]
			"will return the recently set object"
			store.get "user", id

		Example 2:

			"update the 'Martha' object to a new age"
			store = STORAGE
			store.set "user", ['age': 22], "test@example.org"
	*/;
		// end block;
	}
	function set($type, $el, $id = null) {
		$this->_load();
;
		if (empty($this->handle[$type])) {
			$this->handle[$type]  =  Array();
	}
;
		$id  =  ($id === null ? count($this->handle[$type]) : $id);
		$el['id']  =  $id;
		if (isset($this->handle[$type][$id])) {
			$el  =  array_merge($this->handle[$type][$id], $el);
	}
;
		$this->handle[$type][$id]  =  $el;
		return $id;
;
	}
	/**
		remove(type[, id]) -> void
		- type (String) - the type to remove or from which to remove the given object
		- id (Mixed) - the index used to access the object to be removed (optional)

		This method removes either a specfic object (if the `id` is given) or the
		whole type (if no `id` is given or the `id` is null).
	*/
	function remove($type, $id = null) {
		$this->_load();
;
		if ($id === null) {
			unset($this->handle[$type]);
		} else {
			unset($this->handle[$type][$id]);
		}
;
	/**
		find(type[, condition]) -> Array
		- type (String) - the type to search in
		- condition (Function) - an acceptance function for the search; will be called for every record (optional)

		This method returns all objects for which the condition callback
		returns `true`. The callback function will be called with one parameter
		containing the current element.

		Example:

			"find all users older than 22"
			store = new Storage()
			store.find "user", fn(obj) <- obj.age > 22
	*/;
		// end block;
	}
	function find($type, $query = null) {
		$condition  =  (is_callable($query) ? $query : function () {return true;

	});
		$list  =  Array();
		if (is_array($this->handle[$type])) {
			foreach ($this->handle[$type] as $key => $all) {
				if (is_array($all)) {
					if (call_user_func($condition, $all)) {
						array_push($list, $all);
				}
;
					// end block;
			}
;
				// end block;
			}
			unset($all, $key);
;
			// end block;
	}
;
		return $list;
;
	}
	// end block
};
$__dummyDB  =  new Storage();
null;;
?>