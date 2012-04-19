<?php

$sense = "n. 명사 v. 동사 a. 형용사";

/*
$pattern[0] = "n.";
$pattern[1] = "v.";
$pattern[2] = "a.";

$repacement[0] = "<br>n.";
$repacement[1] = "<br>v.";
$repacement[2] = "<br>v.";

str_replace($pattern, $replacement, $sense);
*/

$pattern = '/((n|v|a)\.)/';
$replacement = "<br>$1";

echo preg_replace($pattern, $replacement, $sense);

?>
