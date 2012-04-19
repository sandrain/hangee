/* General helper functions.. */

function trim(str) {
    return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

String.prototype.trim = function() {
    return this.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

/* Main menu related operations.. */

var imgLoaded = 0;
function menuSelect(name) {
    switch (name) {
        case 'you':     $('#menuYou').attr('class', 'td_selected');     break;
        case 'study':   $('#menuStudy').attr('class', 'td_selected');   break;
        case 'hangre':  $('#menuHanGRE').attr('class', 'td_selected');  break;
        case 'goman':   $('#menuGoMan').attr('class', 'td_selected');   break;
        default: return;
    }

    $('#topmenu').find('img').each(function(index) {
        $(this).load(function(e) {
            imgLoaded++;
            if (imgLoaded >= 4)
                $('#topmenu').show();
        });
    });


    setTimeout(function() { window.scrollTo(0, 1); }, 600);
}

function navigateYou() {
    location.href = '/hangee/m/index.php';
}

function navigateStudy() {
    location.href = '/hangee/m/study.php';
}

function navigateHanGRE() {
    location.href = '/hangee/m/hangre.php';
}

function navigateGoMan() {
    location.href = '/hangee/m/goman.php';
}

function navigateHelp() {
    location.href = '/hangee/m/help.php';
}

// wordlist..
var currentUpper = false;
function toggleCase() {
    $('#wordlist').find('.wordtd').each(function() {
        if (currentUpper) {
            $('#wordselector').find('.aA').css('color', '#999');
            this.innerHTML = this.innerHTML.toLowerCase();
        }
        else {
            $('#wordselector').find('.aA').css('color', '#ff0');
            this.innerHTML = this.innerHTML.toUpperCase();
        }
    });
    currentUpper = !currentUpper;
}

var currentStarred = false;
function toggleStarred() {
}

function wordListToggleSense(id) {
    $('#s' + id).toggle();
}

function wordListShowSense(id) {
    $('#s' + id).slideDown();
}

function wordListHideSense(id) {
    $('#s' + id).slideUp();
}

function getMarkedStarHtml(wid) {
    return '<a href="javascript:void(0)" onclick="toggleMark(' + wid + ', true)">'
            + '<img src="/hangee/m/images/star_on_32.png" alt="Starred" /></a>';
}

function getUnmarkedStarHtml(wid) {
    return '<a href="javascript:void(0)" onclick="toggleMark(' + wid + ', false)">'
            + '<img src="/hangee/m/images/star_off_32.png" alt="Not Starred" style="opacity:0.3" /></a>';
}

function toggleMark(wid, currentlyMarked) {
    if (currentlyMarked) {
        LocalDB.deleteMark(wid, function (wid) {
            $('#st' + wid).html(getUnmarkedStarHtml(wid));
            updateWordListMarked(wid, false);
        });
    }
    else {
        LocalDB.addMark(wid, function (wid) {
            $('#st' + wid).html(getMarkedStarHtml(wid));
            updateWordListMarked(wid, true);
        });
    }
}

var wordList = null;
var fullListed = true;

function updateWordListMarked(wid, marked) {
    for (var i in wordList) {
        var current = wordList[i];
        if (current.wid == wid)
            current.marked = marked;
    }
}

function renderWordList(rows) {
    wordList = new Array();
    fullListed = true;

    for (var i in rows) {
        current = rows[i];
        current.marked = false;

        wordList[i] = current;
    }

    LocalDB.getMarkedList(wordList[0].wid, wordList[wordList.length - 1].wid,
        function (rows) {
            if (rows) {
                for (var i = 0; i < rows.length; i++) {
                    var row = rows.item(i);
                    updateWordListMarked(row['wid'], true);
                }
            }
            renderFullWordList();
        });
}

function pronounce(word) {
    // TODO: doesn't work in mobile, why??
    //var audio = document.getElementById('wordplayer');

    var audio = new Audio();
    audio.src = 'http://www.gstatic.com/dictionary/static/sounds/de/0/' + word + '.mp3';
    audio.play();
}

function renderWordListItem(num, current) {
    var star = current.marked ? getMarkedStarHtml(current.wid) : getUnmarkedStarHtml(current.wid);
    return '<tr><td class="numtd" onclick="window.scrollTo(0,1)">' + num + '</td>'
            + '<td class="wordtd" onclick="wordListToggleSense(' + current.wid + ');">' 
            + current.word + '</td><td id="st' + current.wid + '" class="startd">' + star + '</td></tr>'
            + '<tr id="s' + current.wid + '" class="sensetr"><td></td>'
            + '<td class="sensetd" colspan="2" onclick="pronounce(\'' + current.word + '\')">'
            + current.sense + '</td></tr>';
}

function renderFullWordList() {
    var html = '<audio id="wordplayer"></audio><table>';
    var num = 1;

    for (var i in wordList) {
        current = wordList[i];
        html += renderWordListItem(num, current);
        num++;
    }
    html += '</table>';
    $('#wordlist').html(html);

    if (currentUpper) {
        currentUpper = false;
        toggleCase();
    }

    hideLoading();
}

function renderMarkedWordList() {
    var html = '<table>';
    var num = 1;

    for (var i in wordList) {
        current = wordList[i];
        if (current.marked) {
            html += renderWordListItem(num, current);
            num++;
        }
    }
    html += '</table>';
    $('#wordlist').html(html);

    if (currentUpper) {
        currentUpper = false;
        toggleCase();
    }

    hideLoading();
}

function toggleMarkedList() {
    if (fullListed) {
        $('#wordselector').find('.star').html('<img src="/hangee/m/images/star_on_32.png" style="opacity:1.0" />');
        renderMarkedWordList();
    }
    else {
        $('#wordselector').find('.star').html('<img src="/hangee/m/images/star_off_32.png" />');
        renderFullWordList();
    }

    fullListed = !fullListed;
}

function showLoading() {
    $('#loading').show();
}

function hideLoading() {
    $('#loading').hide();
}

