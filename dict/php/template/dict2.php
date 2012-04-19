<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>phpMyDict - <?=Z::config("version")?></title>
<meta http-equiv="content-type" content="text/html; charset=UTF8">
<meta name="generator" content="">
<script src="i/jquery-1.4.1.js"></script>
<link rel="stylesheet" type="text/css" href="i/article-style.css" />
<script src="i/jquery-ui-1.8.1.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="i/jquery-ui-1.8.1.custom.css" />

<style type="text/css">
html,body{margin:0;padding:0}
body{font: 76% arial,sans-serif;text-align:center}
p{margin:0 10px 10px}
a{color: white;}
div#header h1{height:60px;line-height:60px;margin:0;
  padding-left:10px;background: #EEE;color: #79B30B}
div#container{text-align:left}
div#content p{line-height:1.4}
div#navigation{background:#B9CAFF}
div#extra{background:#FF8539}
div#footer{background: #333;color: #FFF}
div#footer p{margin:0;padding:5px 10px}

div#container{width:800px;margin:0 auto}
div#content{float:right;width:600px}
div#navigation{float:left;width:200px}
div#extra{float:right;width:600px}
div#footer{clear:both;width:100%}

.wl {
	white-space: nowrap;	
}
.wl div {
	padding:0px;
	maring:0px;	
}
.wl a {
	padding:0px;
	maring:0px;	
}

.wl a {
	text-decoration:none;
	color:black;	
}

a.link {
	color:blue;	
}

#dic_list {
	width:150px;	
}
#dic_descr {
	width:200px;
	valign:top;	
}

</style>


<script>
var timer;
var inRequest = false;
var keyword = false;
var lastQuery = false;
var statData;

$(document).ready( function() {
	// Setup the ajax indicator
	$("body").append('<div id="ajaxBusy"><p><img src="i/loading.gif"></p></div>');
	$('#ajaxBusy').css({
		display:"none",
		margin:"0px",
		paddingLeft:"0px",
		paddingRight:"0px",
		paddingTop:"0px",
		paddingBottom:"0px",
		position:"absolute",
		right:"3px",
		top:"3px",
		width:"auto"
	});
 
	// Ajax activity indicator bound 
	// to ajax start/stop document events
	$(document).ajaxStart(function(){ 
		$('#ajaxBusy').show(); 
	}).ajaxStop(function(){ 
		$('#ajaxBusy').hide();
	});	
	
	$("#keyword").keyup( function(){
 	 	clearTimeout(timer);
	  	timer = setTimeout("findWord()", 300);
	});
	
	$("#keyword").focus();
	
	//showAdmin();
});


function findWord() {
	if (inRequest) return false;

	keyword = $("#keyword").val();

	if (keyword == lastQuery) return false;

	inRequest = true;
	$.get("?m=word&kw="+keyword, function(data) {
		eval("data="+data);
		var tmp = "";
		for (var x in data['w']) {
			tmp = tmp + "<div class=\"wl\"><a href=\"javascript:showWord('"+data['w'][x]+ "')\">"+data['w'][x]+"</a></div>";
		}
		$("#result").html(tmp);
		$("#query_stat").html(data.s);
		
		inRequest = false;
		lastQuery = keyword;
	});
}

function showWord(id) {
	$.get("?m=show&kw="+id, function(data) {
		$("#dic_content").html(data);
	});
}

function showStat() {
	$.get("?m=stat", function(data) {
		eval("data="+data);
		statData = data;
		var wordCount = 0;
		for (var x in data) {
			$("#dic_list").append("<option value="+data[x].fid+">"+data[x].fbookname+"</option>");
			wordCount = wordCount + parseInt(data[x].fwordcount);
		}
		$("#dic_wtotal").html(wordCount);

		$("#dic_list").change( function() {
			for (var x in statData) 
				if (statData[x].fid == $("#dic_list").val()) {
					$("#dic_bookname").html(data[x].fbookname);
					$("#dic_words").html(data[x].fwordcount);
					$("#dic_descr").html(data[x].fdescription);
				}
		});
		
		$("#dlgStat").dialog({width:600, height:300});
	});
	
}

function showAdmin() {
	$("#dlgAdmin form").submit(function() {
		
		$.post("?m=admin.login&ax=1",{"password":$("#dlgAdmin input[type=password]").val()} , function(data) {
			eval("data="+data);
			if (data) {
				location.href = "?m=admin";
				return;	
			}
			alert("Invalid password. Try again");
		});		
		
		return false;
	});


	$("#dlgAdmin").dialog({width:300, height:120});
	$("#dlgAdmin input[type=password]").focus();
	
}

</script>


</head>
<body>


<div id="dlgStat" title="Dictionary statistic" style="display:none;">
	<div>
	  <div style="float:left;width:150px">
			<select id="dic_list" multiple="multiple" style="height:100%">
			</select><br/>
			<b>Total words:</b><span id="dic_wtotal"></span>
	  </div>
	   <div style="float:left;width:400px;text-align:left;margin-left:5px;">
			<b>Bookname:</b>&nbsp;<span id="dic_bookname"></span><br/>
			<b>Words:</b>&nbsp;<span id="dic_words"></span><br/>
			<b>Description:</b>&nbsp;<br/><textarea style="width:100%;height:200px;" id="dic_descr"></textarea>
	   </div>
	   <br clear="both"/>
	</div>
</div>

<div id="dlgAdmin" title="Admin area login" style="display:none;">
	<div>
	<form>
	<input type="password" name="password" value=""><br/><br/>
	<input type="submit" name="submit" value="Login">
	</form>
	
	</div>
</div>


<div id="container">
<div id="header"><h1>phpMyDict - <?=Z::config("version")?></h1></div>
<div id="wrapper">
<div id="content" style="overflow: auto;height:420px">
<p id="dic_content" >
&nbsp;
</p>
</div>
</div>

<div id="navigation">

	<p>
<!--
	Lookup in
	<select>
	<option value="all">All</option>
	</select>
	-->
	<br/>
	<input type="text" name="keyword" value="" id="keyword">
	<p id="result" style="overflow: auto;height:400px"></p>
	</p>


</div>
<div id="extra">
<p><strong id="query_stat">&nbsp;</scrong>
</p>
</div>
<div id="footer">
<p>Copyright by Constantin V. Bosneaga - 2010 (C) - 
<a href="http://a32.me/2010/06/phpmydict-stardict-web-backend-with-php/">Leave feedback</a>&nbsp;|&nbsp;
<a href="javascript:showStat()">Statistic</a>&nbsp;|&nbsp; 
<a href="javascript:showAdmin()">Admin</a>
</p></div>
</div>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-11043631-2']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>




</body>
</html>