<?php


$fieldArray1 = array(
'user_master_id 8 8 x',
'user_master_call_sign 15 15 x',
'user_first_name 30 30 s',
'user_last_name 50 50 s',
'user_email 50 50 s',
'user_ph_code 8 8 x',
'user_phone 20 20 s',
'user_city 30 30 s',
'user_state 30 60 s',
'user_zip_code 20 20 s',
'user_country_code 5 5 s',
'user_country 30 50 x',
'user_whatsapp 20 20 s',
'user_telegram 20 20 s',
'user_signal 20 20 s',
'user_messenger 20 20 s',
'user_timezone_id 50 50 s',
'user_languages text text s',
'user_survey_score 5 5 d',
'user_is_admin 5 5 s',
'user_role 10 10 s',
'user_master_date_created 20 20 s',
'user_master_date_updated 20 20 s',
'user_master_action_log text text s');



// $fieldArray = array_merge($fieldArray1,$fieldArray2,$fieldArray3);


echo "\n\ninitialization segment\n";
foreach($fieldArray as $fieldInfo) {
$myArray = explode(" ",$fieldInfo);
$thisField = $myArray[0];
$thisSize = $myArray[1];
$thisLength = $myArray[2];
$thisType = $myArray[3];
if ($thisType != 'x') {
$myStr1 = "inp_" . "$thisField";
echo "    \$$myStr1 = '';\n";
}
}



echo "\n\nrequest segment 2\n";
foreach($fieldArray2 as $fieldInfo) {
$myArray = explode(" ",$fieldInfo);
$thisField = $myArray[0];
$thisSize = $myArray[1];
$thisLength = $myArray[2];
$thisType = $myArray[3];
$myStr1 = "inp_" . "$thisField";
if ($thisType != 'x') {
echo "            if (\$str_key == '$myStr1') {\n";
echo "                \$$myStr1 = \$str_value;\n";
echo "                \$$myStr1 = filter_var(\$$myStr1,FILTER_UNSAFE_RAW);\n";
echo "            }\n";
}
}



echo "\n\nrequest segment 3\n";
foreach($fieldArray3 as $fieldInfo) {
$myArray = explode(" ",$fieldInfo);
$thisField = $myArray[0];
$thisSize = $myArray[1];
$thisLength = $myArray[2];
$thisType = $myArray[3];
$myStr1 = "inp_" . "$thisField";
if ($thisType != 'x') {
echo "            if (\$str_key == '$myStr1') {\n";
echo "                \$$myStr1 = \$str_value;\n";
echo "                \$$myStr1 = filter_var(\$$myStr1,FILTER_UNSAFE_RAW);\n";
echo "            }\n";
}
}




echo "\n\ninitial display segment 2\n";
$slot = 0;
foreach($fieldArray2 as $fieldInfo) {
$myArray = explode(" ",$fieldInfo);
$thisField = $myArray[0];
$thisSize = $myArray[1];
$thisLength = $myArray[2];
$thisType = $myArray[3];
$myStr = str_replace('advisor_','Advisor<br />',$thisField);
$myStr = str_replace('advisorclass_','Advisor Class<br />',$myStr);
$myStr = str_replace('student_','Student<br />',$myStr);
switch ($slot) {
case 0:
echo "    <tr><td style='vertical-align:top;'><b>$myStr</b><br />\$$thisField</td>\n";
$slot++;
break;
case 1:
echo "        <td style='vertical-align:top;'><b>$myStr</b><br />\$$thisField</td>\n";
$slot++;
break;
case 2:
echo "        <td style='vertical-align:top;'><b>$myStr</b><br />\$$thisField</td>\n";
$slot++;
break;
case 3:
echo "        <td style='vertical-align:top;'><b>$myStr</b><br />\$$thisField</td></tr>\n";
$slot = 0;
break;
}
}
if ($slot != 0) {
switch ($slot) {
case 1:
echo "        <td></td><td></td><td></td></tr>\n";
break;
case 2:
echo "       <td></td><td></td></tr>\n";
break;
case 3:
echo "       <td></td></tr>\n";
break;
}
}





echo "\n\ninitial display segment 3\n";
$slot = 0;
foreach($fieldArray3 as $fieldInfo) {
$myArray = explode(" ",$fieldInfo);
$thisField = $myArray[0];
$thisSize = $myArray[1];
$thisLength = $myArray[2];
$thisType = $myArray[3];
$myStr = str_replace('advisor_','Advisor<br />',$thisField);
$myStr = str_replace('advisorclass_','Advisor Class<br />',$myStr);
$myStr = str_replace('student_','Student<br />',$myStr);
switch ($slot) {
case 0:
echo "    <tr><td style='vertical-align:top;'><b>$myStr</b><br />\$$thisField</td>\n";
$slot++;
break;
case 1:
echo "        <td style='vertical-align:top;'><b>$myStr</b><br />\$$thisField</td>\n";
$slot++;
break;
case 2:
echo "        <td style='vertical-align:top;'><b>$myStr</b><br />\$$thisField</td>\n";
$slot++;
break;
case 3:
echo "        <td style='vertical-align:top;'><b>$myStr</b><br />\$$thisField</td></tr>\n";
$slot = 0;
break;
}
}
if ($slot != 0) {
switch ($slot) {
case 1:
echo "        <td></td><td></td><td></td></tr>\n";
break;
case 2:
echo "       <td></td><td></td></tr>\n";
break;
case 3:
echo "       <td></td></tr>\n";
break;
}
}


echo "\n\ninput segment 2\n";
foreach($fieldArray2 as $fieldInfo) {
$myArray = explode(" ",$fieldInfo);
$thisField = $myArray[0];
$thisSize = $myArray[1];
$thisLength = $myArray[2];
$thisType = $myArray[3];
$myStr = ucwords(str_replace("_"," ",$thisField));
if ($thisType == 'x') {
echo "        <tr><td>$myStr</td>\n";
echo "            <td>\$$thisField</td></tr>\n";
} else {
$myStr1 = "inp_" . "$thisField";
echo "        <tr><td>$myStr</td>\n";
if ($thisSize == 'text') {
echo "            <td><textarea class='formInputText' name='$myStr1' rows='5' cols= '50'>
$$thisField</textarea></td></tr>\n";
} else {
echo "            <td><input type='text' class='formInputText' name='$myStr1' size='$thisSize' maxlenth='$thisLength' value='\$$thisField'></td></tr>\n";
}
}
}

echo "\n\ninput segment 3\n";
foreach($fieldArray3 as $fieldInfo) {
$myArray = explode(" ",$fieldInfo);
$thisField = $myArray[0];
$thisSize = $myArray[1];
$thisLength = $myArray[2];
$thisType = $myArray[3];
$myStr = ucwords(str_replace("_"," ",$thisField));
if ($thisType == 'x') {
echo "        <tr><td>$myStr</td>\n";
echo "            <td>\$$thisField</td></tr>\n";
} else {
$myStr1 = "inp_" . "$thisField";
echo "        <tr><td>$myStr</td>\n";
if ($thisSize == 'text') {
echo "            <td><textarea class='formInputText' name='$myStr1' rows='5' cols= '50'>
$$thisField</textarea></td></tr>\n";
} else {
echo "            <td><input type='text' class='formInputText' name='$myStr1' size='$thisSize' maxlenth='$thisLength' value='\$$thisField'></td></tr>\n";
}
}
}




echo "\n\compare segment\n";
foreach($fieldArray as $fieldInfo) {
$myArray = explode(" ",$fieldInfo);
$thisField = $myArray[0];
$thisSize = $myArray[1];
$thisLength = $myArray[2];
$thisType = $myArray[3];
$myStr1 = "inp_" . "$thisField";
if ($thisType != 'x') {
echo "        if (\$$myStr1 != \$$thisField) {\n";
echo "            \$doTheUpdate = TRUE;\n";
echo "            \$updateParams['$thisField'] = \$$myStr1;\n";
echo "            \$updateFormat[] = \"%$thisType\";\n";
echo "            \$actionContent .= \"Updated $thisField of \$$thisField to \$$myStr1. \";\n";
echo "        }\n";
}
}



?>