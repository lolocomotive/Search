<?php
include 'dbconnect.php';
$dbConn = OpenCon();

# function indexDir($dir)
# {
#     $dirs = scandir($dir);
#     foreach ($dirs as $file) {
#         if (is_dir($file)) {
#             indexDir($file);
#         } else {
#             $sql = "INSERT INTO files_tmp (Name,Path) VALUES ('" . basename($file) . "','" . realpath($file) . "')<br/>";
#         }
#         echo (var_dump($file) . "<br/>");
#     }
# }
$arr = array();
$start_sql = "INSERT INTO files_tmp (Name,Path) VALUES ";

function indexDir($path = '.', $level = 0, $dbConn, $arr, $start_sql)
{
    $ignore = array('.', '..', '$RECYCLE.BIN');
    // Directories to ignore when listing output. Many hosts 
    // will deny PHP access to the cgi-bin. 

    $dh = @opendir($path);
    // Open the directory to the handle $dh 

    while (false !== ($file = readdir($dh))) {
        // Loop through the directory 

        if (!in_array($file, $ignore)) {
            // Check that this file is not to be ignored 

            $spaces = str_repeat('&nbsp;', ($level * 4));
            // Just to add spacing to the list, to better 
            // show the directory tree. 

            if (is_dir("$path/$file")) {
                // Its a directory, so we need to keep reading down... 
                indexDir("$path\\$file", ($level + 1), $dbConn, $arr, $start_sql);
                // Re-call this same function but on a new directory. 
                // this is what makes function recursive. 

            } else {
                $sql = "('" . $file . "','" . str_replace("\\", "\\\\", $path) . "\\\\" . $file . "')";
                $arr = bulkSQL($arr, 128, $start_sql, $sql, $dbConn);
            }
        }
    }

    closedir($dh);
    // Close the directory handle 
}
function toString($arr)
{
    $r = "";
    foreach ($arr as $str) {
        $r .= $str;
    }
    return $r;
}
function bulkSQL($arr, $max_arr, $start_sql, $sql, $dbConn)
{
    if (count($arr) == $max_arr) {
        $query = $start_sql . toString($arr) . $sql . ";";
        mysqli_query($dbConn, $query);
        return array();
    } else {
        return array_push($arr, $sql . ",");
    }
}
function SQLFlush($arr, $start_sql, $dbConn)
{
    $query = $start_sql . toString($arr) . ";";
    mysqli_query($dbConn, $query);
}
echo "done";
indexDir("D:\\loic\\Pokered", 0, $dbConn, $arr, $start_sql);
SQLFlush($arr, $start_sql, $dbConn);
closeCon($dbConn);
