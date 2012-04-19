<?php
#
# help.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

hangee_init();

hangee_header("HanGEE: GRE Word Memorizing Helper");
?>

<div id="help_content">

<h1>HanGEE Word Memorizing Helper</h1>

<h2>About</h2>
<div class="explain">
<p>You can freely use this program. If you find any bugs please contanct the
administrator(sandrain __AT__ gmail __DOT__ com).</p>
</div>

<h2>Menu</h2>
<div class="explain">
<ul>
<li><strong>Home</strong> : To the first page.</li>
<li><strong>Browser</strong> : Browsing words in HanGEE book.</li>
<li><strong>Tester</strong> : Studying words</li>
<li><strong>Sheets</strong> : Word sheets, 20 words per sheet.</li>
<li><strong>Help</strong> : This page.</li>
</ul>
</div>

<h2>How to use</h2>
<h3>Browser</h3>
<div class="explain">
<p>You can browse words sorted by alphabetical order. Clicking one of the alphabet
will show you the table that lists words beginning with the alphabet.</p>
<p>The <strong>Sense</strong> column is hidden and can be shown by placing the cursor
on the row to which the word belongs. On <strong>Hint</strong> column, you can write down or
edit some hint text which might be useful to remind the word.</p>
<p>The <strong>Word</strong> and <strong>Sense</strong> columns are read-only to you.
Only administrator can modify them. If you find something wrong on those columns, please contact
the administrator by clicking the <strong>Num</strong> column.</p>
<p>By clicking the <strong>Word</strong> column you can mark a word. Marked
words are shown with red stars; the number of stars shows the number of times
you marked. To un-mark the word, simply click the stars. Maximum marking count
is up to 5.</p>
<p>Shortcut keys on this page.
<ul>
<li><strong>'Shift + Enter'</strong> : show and focus/hide the search form (toggle).</li>
<li><strong>'Shift + Alt + [A-Z]'</strong> : browse the word list [A-Z] (eg. shift+alt+a, shift+alt+j,...)</li>
<li><strong>'Shift+S'</strong> : show senses (while browsing)</li>
<li><strong>'Shift+H'</strong> : hide senses (while browsing)</li>
<li><strong>'Shift+U'</strong> : show words in upper case (while browsing)</li>
<li><strong>'Shift+L'</strong> : show words in lower case (while browsing)</li>
</ul>
</p>
</div>

<h3>Tester</h3>
<div class="explain">
<p>First of all, you should initialize a test condition, which means the range
of words you like to study. Choose an alphabet character in the select box, then
the pages, and finally check the marked option. Clicking the <strong>ADD</strong>
button will show you the test range you have just created. You may add more
ranges by repeating above steps or directly <strong>Start the Test</strong>. You can
study words in a random order or an alphabetical order as the popup asks you.
Click the <strong>Clear</strong> button if you like to reset the range.</p>
<p>Once the test begins, your status is saved in the server database. It is
not destroyed until you explicitly destroy the test condition.</p>
<p>On the test process you can use following shortcuts.
<ul>
<li><strong>'N'</strong>: next page</li>
<li><strong>'P'</strong>: previous page</li>
<li><strong>'0'</strong>: first page</li>
<li><strong>'9'</strong>: last page</li>
<li><strong>'U'</strong>: upper/lower text toggle</li>
<li><strong>'A'</strong>: show/hide sense</li>
<li><strong>'Shift + A'</strong>: Edit</li>
<li><strong>'H'</strong>: show/hide hint</li>
<li><strong>'Shift + H'</strong>: Edit hint</li>
<li><strong>'M'</strong>: mark current word</li>
<li><strong>'Shift + M'</strong>: unmark current word</li>
<li><strong>'Space'</strong>: pronounce</li>
</ul>
Note that pronunciation data is from Google server and audio files for a few
words doesn't exist.
</p>
</div>

<h3>Sheets</h3>
<div class="explain">
<p>You have to create a test in <strong>Tester</strong> page to use <strong>Sheets</strong>. This
page will show you the words within your test range. 20 words are listed per
a sheet.</p>
<p>Clicking a word will show you the sense and clicking the number will show you
the hint text of the word. Besides the navigation links below the list, you can also
use following shortcuts to move:
<ul>
<li><strong>'N'</strong>: next page</li>
<li><strong>'P'</strong>: previous page</li>
<li><strong>'Shift + H'</strong> : hide hint/sense</li>
<li><strong>'A'</strong> (while hint is open) : show sense</li>
<li><strong>'M'</strong> (while sense is open) : mark current word</li>
</ul>
</p>
</div>

<h2>NOTES</h2>
<div class="explain">
If your browser is Firefox with Vimperator addon, I love it, you may experience problems like
sudden crashes. Not exact, but it seems that shortcut javascript codes doesn't go well with it.
I'm sorry that I have no time to fix my codes. If you have the troubles, please disable the Vimperator addon.
</div>

</div>

<?php
hangee_footer();
hangee_exit();
?>
