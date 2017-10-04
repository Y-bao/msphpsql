--TEST--
Server Info Test
--DESCRIPTION--
Verifies the functionality of �sqlsrv_server_info�.
--ENV--
PHPT_EXEC=true
--SKIPIF--
<?php require('skipif.inc'); ?>
--FILE--
<?php
require_once('MsCommon.inc');

function ServerInfo()
{
    $testName = "Connection - Server Info";
    startTest($testName);

    setup();
    $conn1 = connect();

    $serverinfo1 = sqlsrv_server_info($conn1);
    $count1 = count($serverinfo1);
    if ($count1 != 3) {
        die("Unexpected size for server_info array: ".$count1);
    }

    ShowInfo($serverinfo1, 'CurrentDatabase');
    ShowInfo($serverinfo1, 'SQLServerName');
    ShowInfo($serverinfo1, 'SQLServerVersion');

    sqlsrv_close($conn1);

    endTest($testName);
}

function ShowInfo($serverInfo, $infoTag)
{
    $info = $serverInfo[$infoTag];
    if (traceMode()) {
        echo "$infoTag\t";
        if (strlen($infoTag) <= 15) {
            echo "\t";
        }
        echo "$info\n";
    }
}

//--------------------------------------------------------------------
// repro
//
//--------------------------------------------------------------------
function repro()
{
    try {
        ServerInfo();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

repro();

?>
--EXPECT--
Test "Connection - Server Info" completed successfully.
