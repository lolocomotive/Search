<?php function OpenCon()
{
    $dbhost = "localhost";
    $dbuser = "id15159226_root";
    $dbpass = "Lp74fC79DbPMZ4c-";
    $db = "search";
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $db) or die("Connect failed: %s\n" . $conn->error);
    return $conn;
}
function CloseCon($conn)
{
    $conn->close();
}
