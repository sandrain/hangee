<?php
// goman.php
require $_SERVER['DOCUMENT_ROOT'].'/hangee/m/lib/__core.php';

$page = new Page();
$page->setTitle('HanGEE::GoMan');
$page->setMaster('m');
$page->setScript('/hangee/m/goman.js');

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
            <option value="1">Day 1</option>
            <option value="2">Day 2</option>
            <option value="3">Day 3</option>
            <option value="4">Day 4</option>
            <option value="5">Day 5</option>
            <option value="6">Day 6</option>
            <option value="7">Day 7</option>
            <option value="8">Day 8</option>
            <option value="9">Day 9</option>
            <option value="10">Day 10</option>
            <option value="11">Day 11</option>
            <option value="12">Day 12</option>
            <option value="13">Day 13</option>
            <option value="14">Day 14</option>
            <option value="15">Day 15</option>
            <option value="16">Day 16</option>
            <option value="17">Day 17</option>
            <option value="18">Day 18</option>
            <option value="19">Day 19</option>
            <option value="20">Day 20</option>
            <option value="21">Day 21</option>
            <option value="22">Day 22</option>
            <option value="23">Day 23</option>
            <option value="24">Day 24</option>
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
