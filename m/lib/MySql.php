<?php
// MySql.php
//

class MySql 
{
    private $conn = null;
    private $host = 'localhost';
    private $username = 'sandrain';
    private $password = '359035';
    private $dbname = 'sandrain';

    function connect() {
        $res = mysql_connect($this->server, $this->username, $this->password);
        if ($res == false)
            return;
        $this->conn = $res;

        $res = mysql_select_db($this->dbname, $this->conn);
        if ($res == false)
            return;
    }

    function query($qstr) {
        return mysql_query($qstr, $this->conn);
    }

    function free() {
        @mysql_free_result($this->conn);
    }

    function close() {
        @mysql_close($this->conn);
    }
}
?>
