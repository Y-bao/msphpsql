--TEST--
Prepare and Execute Test
--DESCRIPTION--
Checks the data returned by a query first prepared and then executed multiple times.
Validates that a prepared statement can be successfully executed more than once.
--ENV--
PHPT_EXEC=true
--SKIPIF--
<?php require('skipif.inc'); ?>
--FILE--
<?php
require_once('MsCommon.inc');

function PrepareAndExecute($noPasses)
{
    $testName = "Statement - Prepare and Execute";
    startTest($testName);

    setup();
    $conn1 = connect();

    $tableName = 'TC34test';
    createTable($conn1, $tableName);

    insertRows($conn1, $tableName, 1);

    $values = array();
    $fieldlVal = "";

    // Prepare reference values
    trace("Execute a direct SELECT query on $tableName ...");
    $stmt1 = selectFromTable($conn1, $tableName);
    $numFields1 = sqlsrv_num_fields($stmt1);
    sqlsrv_fetch($stmt1);
    for ($i = 0; $i < $numFields1; $i++) {
        if (useUTF8Data()) {
            $fieldVal = sqlsrv_get_field($stmt1, $i, SQLSRV_PHPTYPE_STRING('UTF-8'));
        } else {
            $fieldVal = sqlsrv_get_field($stmt1, $i, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR));
        }
        if ($fieldVal === false) {
            fatalError("Failed to retrieve field $i");
        }
        $values[$i] = $fieldVal;
    }
    sqlsrv_free_stmt($stmt1);
    trace(" $numFields1 fields retrieved.\n");

    // Prepare once and execute several times
    trace("Prepare a SELECT query on $tableName ...");
    $stmt2 = PrepareQuery($conn1, "SELECT * FROM [$tableName]");
    $numFields2 = sqlsrv_num_fields($stmt2);
    trace(" $numFields2 fields expected.\n");
    if ($numFields2 != $numFields1) {
        setUTF8Data(false);
        die("Incorrect number of fields: $numFields2");
    }

    for ($j = 0; $j < $noPasses; $j++) {
        trace("Executing the prepared query ...");
        sqlsrv_execute($stmt2);
        sqlsrv_fetch($stmt2);
        for ($i = 0; $i < $numFields2; $i++) {
            if (useUTF8Data()) {
                $fieldVal = sqlsrv_get_field($stmt2, $i, SQLSRV_PHPTYPE_STRING('UTF-8'));
            } else {
                $fieldVal = sqlsrv_get_field($stmt2, $i, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR));
            }
            if ($fieldVal === false) {
                fatalError("Failed to retrieve field $i");
            }
            if ($values[$i] != $fieldVal) {
                setUTF8Data(false);
                die("Incorrect value for field $i at iteration $j");
            }
        }
        trace(" $numFields2 fields verified.\n");
    }
    sqlsrv_free_stmt($stmt2);

    dropTable($conn1, $tableName);

    sqlsrv_close($conn1);

    endTest($testName);
}

//--------------------------------------------------------------------
// repro
//
//--------------------------------------------------------------------
function repro()
{
    if (! isWindows()) {
        setUTF8Data(true);
    }
    try {
        PrepareAndExecute(5);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    setUTF8Data(false);
}

repro();

?>
--EXPECT--
Test "Statement - Prepare and Execute" completed successfully.
