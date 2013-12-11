	Snow Crystals
	-------------

	This script is a simple micro-framework based on Snow with proper templating,
	caching and custom routes.

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

		# file is routes/user/select.snow
		import "lib/storage"
		fn get(id)
			store = STORAGE
			<- store.get "user", id

	The template is always called like the crystal. Example (the user object returned by
	the above crystal code has a property `name`):

		<!-- routes/user/select.tpl -->
		<h1>Welcome {{name}}!</h1>
		<p>Glad you made it here!</p>

	Caching can be activated by setting the global `CACHING` variable to anything but 
	`false`. If you set it to a number, it will interpret the number as the minutes the 
	current page should be cached. If it is anything else, it will generate an ETag cache 
	entry.

	If a 404, file not found error occurs, it tries to load the routes/error/404.snow and
	execute it's `get`/`post`/`put` function. Afterwards it will render the 404.tpl template 
	in the same directory. In case the action is not found, only the template will be rendered.

	If any other error occurs during the execution of a custom action, it will always issue
	a 500 error. Therefore it will try to load the routes/error/500.snow and execute it's 
	`get`/`post`/`put` function. Afterwards it will render the 500.tpl template in the same 
	directory. In case the action is not found, only the template will be rendered.

	Custom URLs can also be defined by overriding the main/home crystal, in case the URL 
	does not need to be multi-parted (example: `http://<server>/<pathname>`). The 
	`pathname` segment of the URL will be transmitted to the main/home crystal as the first
	parameter. Alternatively, for multi-part URLs (example: `http://<server>/<pathname1>/<pathname2>...`)
	you can override the error/404 crystal and route to the respective actual crystal.

	All settings are stored on a file called `settings.json`, which is read into the global
	variable `CONFIG`. Any properties you write there can be obtained from the `CONFIG` object.
*
