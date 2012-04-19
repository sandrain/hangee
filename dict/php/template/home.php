<h1>Dictionary</h1>

<input type="text" name="keyword" value="" id="keyword"><br/>
<div id="status">&nbsp;</div>
<div id="result" style="width:200px;height:400px;overflow: scroll;border:1px solid black;float:left"></div>

<div id="content" style="width:500px;height:400px;overflow: scroll;border:1px solid black"></div>

<script>
var timer;
var inRequest = false;

$("#keyword").keyup( function(){
  clearTimeout(timer);
  timer = setTimeout("findWord()", 300);
});


function findWord() {
	if (inRequest) return;

	$("#status").html("Searching");
	inRequest = true;
	$.get("?m=word&kw="+$("#keyword").val(), function(data) {
		eval("data="+data);
		var tmp = "";
		for (var x in data) {
			tmp = tmp + "<a href=\"javascript:showWord('"+data[x]+ "')\">"+data[x]+"</a><br/>";
		}
		$("#status").html("&nbsp;");
		$("#result").html(tmp);
		inRequest = false;
	});
}

function showWord(id) {
	$.get("?m=show&kw="+id, function(data) {
		$("#content").html(data);
	});
}

</script>



