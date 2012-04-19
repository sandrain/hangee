
function selectCallback(obj) {
    fetchWordList(obj.value);
}

function fetchWordList(character) {
    showLoading();

    var request = new Object();
    request.character = character;

    $.post('/hangee/m/handlers/hangreHandler.php', {request:JSON.stringify(request)},
        function (response) {
            if (response.result == 'success')
                renderWordList(response.data);
        },
        'json');
}

// main driver
$(document).ready(function() {
    menuSelect('hangre');
    LocalDB.open();

    fetchWordList('a');
});

