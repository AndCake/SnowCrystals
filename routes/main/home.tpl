{{>decorator}}

<h1>Welcome to Crystal <span class='small'>A snow framework</span></h1>
	
<p>Congratulations! At the moment this is the default page, feel free to modify it and display whatever content you may choose. Below is a list of the currently active flakes. Click on them to call the respective default action.</p>

<h3>Available Flakes:</h3>
<ul class='flake-list'>
	{{#flakes}}
		<li>
			<a class="btn btn-default" role="button" href="{{name}}/home">{{name}}</a>
		</li>
	{{/flakes}}
	{{^flakes}}
		<li>None currently exist.</li>
	{{/flakes}}
</ul>

{{>footer}}