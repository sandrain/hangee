<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

	<title>Simple Layout Demo</title>

<script src="js/jquery-1.4.2.min.js"></script>
<script src="js/jquery-ui-1.8.1.custom.min.js"></script>
<script src="js/jquery.layout.min.js"></script>


	<script type="text/javascript">

	var myLayout; // a var is required because this page utilizes: myLayout.allowOverflow() method

	$(document).ready(function () {

		myLayout = $('body').layout({
			// enable showOverflow on west-pane so popups will overlap north pane
			west__showOverflowOnHover: true
		//,	west__fxSettings_open: { easing: "easeOutBounce", duration: 750 }
		});
		
 	});

	</script>


	<style type="text/css">
	/**
	 *	Basic Layout Theme
	 * 
	 *	This theme uses the default layout class-names for all classes
	 *	Add any 'custom class-names', from options: paneClass, resizerClass, togglerClass
	 */

	.ui-layout-pane { /* all 'panes' */ 
		background: #FFF; 
		border: 1px solid #BBB; 
		padding: 10px; 
		overflow: auto;
	} 

	.ui-layout-resizer { /* all 'resizer-bars' */ 
		background: #DDD; 
	} 

	.ui-layout-toggler { /* all 'toggler-buttons' */ 
		background: #AAA; 
	} 


	</style>


	<style type="text/css">
	/**
	 *	ALL CSS below is only for cosmetic and demo purposes
	 *	Nothing here affects the appearance of the layout
	 */

	body {
		font-family: Arial, sans-serif;
		font-size: 0.85em;
	}
	p {
		margin: 1em 0;
	}

	</style>

</head>
<body>

<!-- manually attach allowOverflow method to pane -->
<div class="ui-layout-north" onmouseover="myLayout.allowOverflow('north')" onmouseout="myLayout.resetOverflow(this)">
phpDICT
</div>

<!-- allowOverflow auto-attached by option: west__showOverflowOnHover = true -->
<div class="ui-layout-west">
	This is the west pane, closable, slidable and resizable

	<ul>
		<li>
			<ul>
				<li>one</li>
				<li>two</li>
				<li>three</li>
				<li>four</li>
				<li>five</li>
			</ul>
			Pop-Up <!-- put this below so IE and FF render the same! -->
		</li>
	</ul>

	<p><button onclick="myLayout.close('west')">Close Me</button></p>

	<p><a href="#" onClick="showOptions(myLayout,'defaults.fxSettings_open');showOptions(myLayout,'west.fxSettings_close')">Show Options.Defaults</a></p>

</div>

<div class="ui-layout-south">
	Copyright 2009
</div>

<div class="ui-layout-east">
	This is the east pane, closable, slidable and resizable

	<!-- attach allowOverflow method to this specific element -->
	<ul onmouseover="myLayout.allowOverflow(this)" onmouseout="myLayout.resetOverflow('east')">
		<li>
			<ul>
				<li>one</li>
				<li>two</li>
				<li>three</li>
				<li>four</li>
				<li>five</li>
			</ul>
			Pop-Up <!-- put this below so IE and FF render the same! -->
		</li>
	</ul>

	<p><button onclick="myLayout.close('east')">Close Me</button></p>

	<p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p>
	<p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p>
	<p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p>
	<p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p>
</div>

<div class="ui-layout-center">
	This center pane auto-sizes to fit the space <i>between</i> the 'border-panes'
	<p>This layout was created with only <b>default options</b> - no customization</p>
	<p>Only the <b>applyDefaultStyles</b> option was enabled for <i>basic</i> formatting</p>
	<p>The Pop-Up and Drop-Down lists demonstrate the <b>allowOverflow()</b> utility</p>
	<p>The Close and Toggle buttons are examples of <b>custom buttons</b></p>
	<p><a href="http://layout.jquery-dev.net/demos.html">Go to the Demos page</a></p>
	<p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p>
	<p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p>
	<p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p>
	<p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p><p>...</p>
</div>

</body>
</html>