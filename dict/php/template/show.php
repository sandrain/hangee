<div style="padding:10px;">

<?$dic=""; foreach($result as $v) {?>

<div class="gddictname"><?=sh($v["fbookname"])?></div>	
<div class="gdword"><?=sh($v["word"])?></div>	
<div class="1gdarticle"><?=nl2br($v["text"])?></div>
<?}?>

</div>