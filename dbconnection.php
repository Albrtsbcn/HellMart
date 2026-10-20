<?php

class DatabaseException extends Exception
{
}

class DBconnection
{
    private $dbconn;

    public function __construct()
    {
        $host = "localhost";
        $port = "3306";
        $dbname = "proyek_pbd";
        $user = "root";
        $password = "";

        $this->dbconn = @mysqli_connect($host, $user, $password, $dbname, $port);

        if (!$this->dbconn) {
            throw new DatabaseException("Connection failed: " . mysqli_connect_error());
        }
    }

    public function get_connection()
    {
        return $this->dbconn;
    }
}
?>