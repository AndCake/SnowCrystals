Class Storage
=============

This class provides a basic persistent data storage.
You can use it by either creating a new instance of the Storage
or by fetching the global `STORAGE` object.

Override the methods of this class in order to implement an actual
database adapter.

Example:

	# get a Storage instance
	store = new Storage()

	# alternatively, get the existing Storage instance
	store = STORAGE

The following methods are provided in order to retrieve data from
the storage and write data into the storage.


save() -> void
--------------

This method saves/commits the current storage contents onto the harddrive.
Only after saving changes, others can access them.
	
	
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

	# get the user with ID "test@example.org"
	store = STORAGE
	store.get "user", "test@example.org"

Example 2:

	# get the user with the name "Martha"
	store = STORAGE
	store.get "user", "name", "Martha"


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

	# sets the 'Martha' object to the index 'test@example.org'
	store = STORAGE
	store.set "user", ['name': 'Martha', 'age': 21], "test@example.org"

	# will add a new record with an automatically generated ID
	id = store.set "user", ['name': 'Carl', 'age': 23]
	# will return the recently set object
	store.get "user", id

Example 2:

	# update the 'Martha' object to a new age
	store = STORAGE
	store.set "user", ['age': 22], "test@example.org"


remove(type[, id]) -> void
--------------------------
- type (String) - the type to remove or from which to remove the given object
- id (Mixed) - the index used to access the object to be removed (optional)

This method removes either a specfic object (if the `id` is given) or the
whole type (if no `id` is given or the `id` is null).


find(type[, condition]) -> Array
--------------------------------
- type (String) - the type to search in
- condition (Function) - an acceptance function for the search; will be called for every record (optional)

This method returns all objects for which the condition callback
returns `true`. The callback function will be called with one parameter
containing the current element.

Example:

	# find all users older than 22
	store = new Storage()
	store.find "user", fn(obj) <- obj.age > 22
