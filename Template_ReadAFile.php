<?php

// 	read a txt file template

$records = 0;

$fileName = readline("File Name: ");

$fp = @fopen($fileName, "r");
if ($fp) {
    while (($buffer = fgets($fp)) !== false) {
//      echo $buffer;
		$thisRecord .= trim($buffer);
		$records++;
    }
    if (!feof($fp)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($fp);
}
exit;
?>


<?php

// 	read a csv file template

$records = 0;

$fileName = readline("File Name: ");

$fp = @fopen($fileName, "r");
if ($fp) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo $data[$c] . "<br />\n";
        }
    }
    fclose($handle);
}
exit;
?>
