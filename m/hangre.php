<?php
// hangre.php
require $_SERVER['DOCUMENT_ROOT'].'/hangee/m/lib/__core.php';

$page = new Page();
$page->setTitle('HanGEE::HanGRE');
$page->setMaster('m');
$page->setScript('/hangee/m/hangre.js');

ob_start();
?>

<div id="wordselector">
    <div class="left">
        <p>
            <span onclick="toggleCase();" class='aA'>a/A</span>
            <span onclick="toggleMarkedList();" class='star'>
                <img src="/hangee/m/images/star_off_32.png">
            </span>
        </p>
    </div>
    <div class="right">
        <select id="selday" onchange="selectCallback(this)">
            <option value="a"> A </option>
            <option value="b"> B </option>
            <option value="c"> C </option>
            <option value="d"> D </option>
            <option value="e"> E </option>
            <option value="f"> F </option>
            <option value="g"> G </option>
            <option value="h"> H </option>
            <option value="i"> I </option>
            <option value="j"> J </option>
            <option value="k"> K </option>
            <option value="l"> L </option>
            <option value="m"> M </option>
            <option value="n"> N </option>
            <option value="o"> O </option>
            <option value="p"> P </option>
            <option value="q"> Q </option>
            <option value="r"> R </option>
            <option value="s"> S </option>
            <option value="t"> T </option>
            <option value="u"> U </option>
            <option value="v"> V </option>
            <option value="w"> W </option>
            <option value="x"> X </option>
            <option value="y"> Y </option>
            <option value="z"> Z </option>
        </select>
    </div>
    <div class="dummyclear"></div>
</div>

<div id="wordlist">
</div>

<?php
$page->setContent(ob_get_contents());
ob_clean();

$page->render();
?>
