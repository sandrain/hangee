<h1>Reindex dictionary</h1>

<table border="1" width="600px;">
<colgroup>
	<col width="200">
	<col width="50">
	<col>
	<col width="200">
</colgroup>
<tr>
	<td>Dictionary name</td>
	<td>Words</td>
	<td>Description</td>
	<td>Error</td>
</tr>	

<?foreach($dic_list as $v) {?>
<tr>
	<td><?=sh($v["fbookname"])?></td>
	<td><?=sh($v["fwordcount"])?></td>
	<td><?=sh($v["fdescription"])?></td>
	<td><?=sh($v["ferror"])?></td>
</tr>	
<?}?>