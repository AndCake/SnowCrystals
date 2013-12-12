Snow Crystals
=============

Snow Crystals is a simple micro-framework based on [Snow](http://github.com/AndCake/snow) 
with templating, caching and custom routes, called "snowflakes".

In order to write a new page, simply add a new directory in `flakes` and add a new
`.snow` file called after the crystal-part in that directory. The URL will then look like that:

	http://<myserver>/<base-dir>/<snowflake>/<snowcrystal>

In the crystal file, you need to define at least a `get` function that will be called
for all GET requests. In addition to that, you can also define a `post` function for
POST requests or a `put` function for PUT requests and so on.

The result of such a function should always be an array that contains the data to be
used in the template. Example for a URL of the format 
`http://<myserver>/<base-dir>/user/select/<id>`:

	# file is flakes/user/select.snow
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

If a 404, file not found error occurs, it tries to load the flakes/error/404.snow and
execute it's `get`/`post`/`put` function. Afterwards it will render the 404.tpl template 
in the same directory. In case the action is not found, only the template will be rendered.

If any other error occurs during the execution of a custom action, it will always issue
a 500 error. Therefore it will try to load the flakes/error/500.snow and execute it's 
`get`/`post`/`put` function. Afterwards it will render the 500.tpl template in the same 
directory. In case the action is not found, only the template will be rendered.

All settings are stored on a file called `settings.json`, which is read into the global
variable `CONFIG`. Any properties you write there can be obtained from the `CONFIG` object.