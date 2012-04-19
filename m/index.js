var profile = null;

function saveUserName() {
    var name = $('#username').val().trim();
    if (name == '') {
        alert("이름을 입력해 주세요");
        return;
    }

    LocalDB.setUser(name, function() {document.location.reload();});
}

function showProfileInput() {
    $('#profileinput').hide();
    $('#profileinput').fadeIn();
}

function showCurrentProfile() {
    $('#profilename').html(profile.name);
    LocalDB.getHanGREStatus(function (count) {
        var val = 4532 - count;
        var rate = (val / 4532.0) * 100;
        $('#hancount').html(val);
        $('#hanrate').html(rate.toFixed(2));
    });
    LocalDB.getGoManStatus(function (count) {
        var val = 2340 - count;
        var rate = (val / 2340.0) * 100;
        $('#gocount').html(val);
        $('#gorate').html(rate.toFixed(2));
    });

    $('#currentprofile').fadeIn();
}

function renderPage(result) {
    profile = result;

    if (profile == null) {
        showProfileInput();
    }
    else {
        showCurrentProfile();
    }
}

// main driver
$(document).ready(function() {
    menuSelect('you');

    LocalDB.open();
    LocalDB.getStatus(renderPage);
});

