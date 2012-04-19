
function selectCallback(obj) {
    fetchWordList(obj.value);
}

function fetchWordList(day) {
    showLoading();

    var request = new Object();
    request.day = day;

    $.post('/hangee/m/handlers/gomanHandler.php', {request:JSON.stringify(request)},
        function (response) {
            if (response.result == 'success')
                renderWordList(response.data);
        },
        'json');
}

// main driver
$(document).ready(function() {
    menuSelect('goman');
    LocalDB.open();

    fetchWordList(1);
});

