Snowflakes
==========

Snowflake Crystals are `*.snow` files in the SnowFlake's directory. Each SnowFlake Crystal
should have at least one of the HTTP Methods ('get', 'post', 'put', 'delete', ...) as function.

All data returned by the function is directly handed over to the respective template (which has
the same name as the Crystal itself, but ends in `.tpl`). For each request method, there 
can be an extra template. To distinguish them, change the name to `[crystal]-[method].tpl`.
If for example, there is a post request coming in for the Crystal "show", the `post` method
in that Crystal will be called and in addition to that, the `show-post.tpl` file will be used
as a template. In case that template does not exist, it will continue using the `show.tpl` 
template instead.

By having a Snowflake's Crystal return a "decorator" attribute with it's value 
pointing to a template which has a `{{body}}` tag, you can wrap the decorator around 
the actual Crystal's template.

Example:

Crystal "list" (`list.snow`):

	fn get <- ['decorator': 'basehtml', 'num': rand(1, 100)]

"list"'s template (`list.tpl`):

	<span class='bold'>Hello world number {{num}}!</span>

Decorator `basehtml.tpl`:

	<div class='page'>
		{{body}}
	</div>

Will result in:

	<div class='page'>
		<span class='bold'>Hello world!</span>
	</div>

Custom URLs can be defined by overriding the main/home Crystal, in case the URL 
does not need to be multi-parted (example: `http://<server>/<pathname>`). The 
`pathname` segment of the URL will be transmitted to the main/home crystal as the first
parameter. Alternatively, for multi-part URLs (example: `http://<server>/<pathname1>/<pathname2>...`)
you can override the error/404 crystal and execute to the respective actual crystal.

If the URL contains further parts besides the Snowflake and it's Crystal to be called,
these additional parts will be provided to the respective request method function as parameters.

Example:

	# the URL looks like http://example.org/user/edit/5/complete
	# then the <get> function for it would look like that:
	fn get(id, state = '')
		# ...

In the above example, the parameter `id` is mandatory and thus always needs to be transmitted
as part of the URL, whereas the `state` parameter is optional and can be left away.