/** LocalDB.js
 *
 */

var LocalDB = {};

LocalDB.db = null;

LocalDB.size = 5 * 1024 * 1024; // 5MB
LocalDB.tableScript = new Array();
LocalDB.tableScript[0] = 
        'create table if not exists hangee_marked'
        + '(wid integer primary key asc, count integer)';
LocalDB.tableScript[1] =
        'create table if not exists hangee_memo'
        + '(wid integer primary key asc, memo text)';
LocalDB.tableScript[2] =
        'create table if not exists hangee_study'
        + '(wid integer primary key asc)';
LocalDB.tableScript[3] =
        'create table if not exists hangee_status'
        + '(name text, pos integer)';

LocalDB.onError = function () {
}

LocalDB.open = function () {
    LocalDB.db = openDatabase('dbHanGEE', '1.0', 'Local storage for HanGEE', LocalDB.size, LocalDB.init);
};

LocalDB.init = function () {
    LocalDB.db.transaction(function(tx) {
        for (var i in LocalDB.tableScript) {
            sql = LocalDB.tableScript[i];
            tx.executeSql(sql);
        }
    });

    $.post('/hangee/m/handlers/initListHandler.php', {request:JSON.stringify(null)}, function (response) {
        if (response.result == 'success') {
            LocalDB.db.transaction(function(tx) {
                var list = response.data;

                for (var i in list) {
                    tx.executeSql("insert into hangee_marked (wid, count) values (?, ?)", [list[i], 1]);
                }
            });
        }
    }, 'json');
};

LocalDB.addMark = function (wid, callback) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("select * from hangee_marked where wid=?", [wid], function (tx, result) {
            if (result.rows.length > 0)
                callback(wid);
            else
                tx.executeSql("insert into hangee_marked values (?,?)", [wid, 1], function (tx, result) {
                    callback(wid);
                },
                function (tx, e) {
                    alert(e.message);
                });
        },
        function (tx, e) {
            alert(e.message);
        });
    });
};

LocalDB.deleteMark = function (wid, callback) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("select * from hangee_marked where wid=?", [wid], function (tx, result) {
            if (result.rows.length > 0) {
                tx.executeSql("delete from hangee_marked where wid=?", [wid], function (tx, result) {
                    callback(wid);
                },
                function (tx, e) {
                    alert(e.message);
                });
            }
            else {
                callback(wid);   // do nothing
            }
        },
        function (tx, e) {
            alert(e.message);
        });
    });
};

LocalDB.addMemo = function (wid, memo) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("select * from hangee_memo where wid=?", [wid], function (tx, result) {
            if (result.rows.length > 0)
                tx.executeSql("update hangee_memo set memo=? where wid=?", [memo, wid]);
            else
                tx.executeSql("insert into hangee_memo values (?,?)", [wid, memo]);
        });
    });
};

LocalDB.deleteMemo = function (wid) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("delete from hangee_memo where wid=?", [wid]);
    });
};

LocalDB.prepareStudy = function (wids) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("delete from hangee_study", [], function (tx, result) {
            for (i in wids) {
                tx.executeSql("insert into hangee_study values (?)", [wids[i]]);
            }
        });
    });
};

LocalDB.getStatus = function(callback) {
    LocalDB.db.readTransaction(function(tx) {
        tx.executeSql("select * from hangee_status", [],
            function (tx, result) {
                var current = null;
                if (result.rows.length > 0) {
                    var row = result.rows.item(0);
                    current = new Object();
                    current.name = row['name'];
                    current.pos = row['pos'];
                }

                callback(current);
            },
            function () {
                callback(null)
            });
    });
}

LocalDB.setUser = function (name, callback) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("insert into hangee_status (name,pos) values (?,?)", [name, -1],
            function (tx, result) {
                callback();
            },
            function (tx, e) {
                alert(e.message);
            });
    });
}

LocalDB.setPos = function (name, pos) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("update hangee_status set pos=? where name=?", [pos, name]);
    });
}

LocalDB.checkMarked = function (wid, callback) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("select * from hangee_marked where wid=?", [wid],
            function (tx, result) {
                var marked = false;
                if (result.rows.length > 0)
                    marked = true;
                callback(wid, marked);
            },
            function (tx, e) {
                alert(e.message);
            });
    });
}

LocalDB.getMarkedList = function (min, max, callback) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("select wid from hangee_marked where wid >= ? and wid <= ?", [min, max],
            function (tx, result) {
                if (result.rows.length > 0)
                    callback(result.rows);
                else
                    callback(null);
            },
            function (tx, e) {
                alert(e.message);
            });
    });
}

LocalDB.getHanGREStatus = function (callback) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("select count(*) as count from hangee_marked where wid < 5000", [],    // <= 4532
            function (tx, result) {
                var row = result.rows.item(0);
                callback(row['count']);
            });
    });
}

LocalDB.getGoManStatus = function (callback) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql("select count(*) as count from hangee_marked where wid > 5000", [],    // >= 6873
            function (tx, result) {
                var row = result.rows.item(0);
                callback(row['count']);
            });
    });
}

LocalDB.query = function (sql, callback) {
    LocalDB.db.transaction(function(tx) {
        tx.executeSql(sql, [], function (tx, result) {
            callback(result);
        });
    });
}

