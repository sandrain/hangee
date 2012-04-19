
$(document).ready(function() {
    LocalDB.open();

    var sql = "select name from sqlite_master where type='table'";
    LocalDB.query(sql, function (result) {
        //console.log(JSON.stringify(result));

        var tables = new Array();
        var html = '';
        var j = 0;

        if (result.rows.length > 0) {
            for (var i = 0; i < result.rows.length; i++) {
                var row = result.rows.item(i);
                html += row['name'] + '<br />';

                if (row['name'].indexOf('hangee_') == 0) {
                    tables[j++] = row['name'];
                }
            }

            if (tables.length > 0) {
                for (var i in tables) {
                    var sql = 'drop table ' + tables[i];
                    if (i == tables.length - 1) {
                        LocalDB.query(sql, function(result) {
                            LocalDB.init();
                        });
                    }
                    else {
                        LocalDB.query(sql, function(result) {});
                    }
                }
            }
            else
                LocalDB.init();
        }

        $('#result').html(html);
    });

    //LocalDB.init();
});
