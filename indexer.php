<?php
include 'dbconnect.php';
session_start();
const MAX_QUERY_SIZE = 8192;
const START_SQL = "INSERT INTO files_tmp (Name,Path) VALUES ";
$fp = 0;
$dbConn = OpenCon();
$arr = [START_SQL];
$start_time = microtime(true);

function indexDir($path = '.')
{
    global $arr, $fp, $dbConn;
    $ignore = array('.', '..', '$RECYCLE.BIN'); // Filenames to be ignored
    $dh = @opendir($path); // Directory handle
    if ($dh instanceof bool) {
        SQLFlush($arr);
        closeCon($dbConn);
        die($path);
    }
    while (false !== ($file = readdir($dh))) {
        if (!in_array($file, $ignore)) {
            if (is_dir("$path/$file")) {
                indexDir("$path\\$file");
            } else {
                $sql = "('" . $file . "','" . str_replace("\\", "\\\\", $path) . "\\\\" . $file . "')";
                $arr = bulkSQL($arr, $sql);
                $fp++;
            }
        }
    }
    closedir($dh);
}
function toString($arr)
{
    $r = ""; // Return value
    foreach ($arr as $str) {
        $r .= $str;
    }
    return $r;
}
function bulkSQL($arr, $sql)
{
    global $dbConn, $start_time, $fp;
    if (count($arr) == MAX_QUERY_SIZE) {
        $query = toString($arr) . $sql . ";";
        mysqli_query($dbConn, $query);
        $len = microtime(true) - $start_time;
        $_SESSION["elapsed"] = $len;
        $_SESSION["fp"] = $fp;
        return [START_SQL];
    } else {
        array_push($arr, $sql . ",");
        return $arr;
    }
}
function SQLFlush($arr)
{
    global $dbConn;
    $query = substr(toString($arr), 0, -1) . ";";
    mysqli_query($dbConn, $query);
    echo mysqli_error($dbConn);
}
$sql = "TRUNCATE TABLE  files_tmp;";
mysqli_query($dbConn, $sql);
set_time_limit(0);
indexDir("D:");
SQLFlush($arr);
closeCon($dbConn);
$len = microtime(true) - $start_time;
echo "Done !<br/> " . $fp . " files in " . $len . " seconds (" . $fp / $len . " files per second)";
