<?php

$fieldArray = array(
'user_ID',
'user_call_sign',
'user_first_name',
'user_last_name',
'user_email',
'user_ph_code',
'user_phone',
'user_city',
'user_state',
'user_zip_code',
'user_country_code',
'user_country',
'user_whatsapp_app',
'user_telegram_app',
'user_signal_app',
'user_messenger_app',
'user_action_log',
'user_timezone_id',
'user_languages',
'user_survey_score',
'user_is_admin',
'user_role',
'user_date_created',
'user_date_updated',
'student_id',
'student_call_sign',
'student_time_zone',
'student_timezone_offset',
'student_youth',
'student_age',
'student_parent',
'student_parent_email',
'student_level',
'student_waiting_list',
'student_request_date',
'student_semester',
'student_notes',
'student_welcome_date',
'student_email_sent_date',
'student_email_number',
'student_response',
'student_response_date',
'student_abandoned',
'student_status',
'student_action_log',
'pre_assigned_advisor',
'student_selected_date',
'student_no_catalog',
'student_hold_override',
'student_messaging',
'student_assigned_advisor',
'student_advisor_select_date',
'student_advisor_class_timezone',
'student_hold_reason_code',
'student_class_priority',
'student_assigned_advisor_class',
'student_promotable',
'student_excluded_advisor',
'student_survey_completion_date',
'student_available_class_days',
'istudent_ntervention_required',
'student_copy_control',
'student_first_class_choice',
'student_second_class_choice',
'student_third_class_choice',
'student_first_class_choice_utc',
'student_second_class_choice_utc',
'student_third_class_choice_utc',
'student_catalog_options',
'student_flexible',
'student_date_created',
'student_date_updated');

echo "\n\nSegment 1 - field definitions\n";
foreach($fieldArray as $thisField) {
echo "    \$$thisField = '';\n";
}
echo "\n";
foreach($fieldArray as $thisField) {
$myStr = "\$$thisField" . "_checked";
echo "    $myStr = '';\n";
}

echo "\n\nSegment 2 - str_key\n";
foreach($fieldArray as $thisField) {
$myStr = "$thisField" . "_checked";
echo "            if (\$str_key == '$thisField') {\n";
echo "                $$myStr = 'X';\n";
echo "                \$reportConfig['$myStr'] = 'X';\n";
echo "                if (\$doDebug) {\n";
echo "                    echo \"$thisField included in report<br />\";\n";
echo "                }\n";
echo "            }\n";
}


echo "\n\nSegment 3 - input statements\n";
foreach($fieldArray as $thisField) {
$myStr = "$thisField" . "_checked";
echo "                            <tr><td><input type='checkbox' class='formInputButton' id='$thisField' \n";
echo "                                    name='$thisField' value='$thisField'>\n";
echo "                                    <label for '$thisField'>$thisField</label></td>\n";
echo "                                <td>$thisField</td></tr>\n";
}

echo "\n\nSegment 4 -- header definitions\n";
foreach($fieldArray as $thisField) {
$myStr = str_replace('user_','user<br />',$thisField);
$myStr = str_replace('advisor_','advisor<br />',$myStr);
$myStr = str_replace('advisorclass_','advisorclass<br />',$myStr);
$myStr = str_replace('student_','student<br />',$myStr);
echo "        \$nameConversionArray['$thisField'] = '$myStr';\n";
}

echo "\n\nSegent 5 -- put out table headers\n";
foreach($fieldArray as $thisField) {
$myStr = "$thisField" . "_checked";
echo "             if (\$$myStr == 'X') {\n";
echo "                 \$headerName = \$nameConversionArray['$thisField'];\n";
echo "                 \$content .= \"<th>\$headerName</th>\";\n";
echo "            }\n";
}


echo "\n\nSegment 6 - put out headers for csv\n";
foreach($fieldArray as $thisField) {
$myStr = "$thisField" . "_checked";
echo "            if (\$$myStr == 'X') {\n";
echo "                if (\$needComma) {\n";
echo "                    \$content .= '\t';\n";
echo "                }\n";
echo "                \$headerName = \$nameConversionArray['$thisField'];\n";
echo "                \$headerName = str_replace('<br />','_',\$headerName);\n";
echo "                \$content .= \$headerName;\n";
echo "                \$needComma = TRUE;\n";
echo "            }\n";
}

echo "\n\nSegment 7 -- output table data\n";
foreach($fieldArray as $thisField) {
$myStr = "$thisField" . "_checked";
echo "                       if (\$$myStr == 'X') {\n";
echo "                           \$content .= \"<td style='vertical-align:top'>\$$thisField</td>\";\n";
echo "                        }\n";
}

echo "\n\nSegment 8 -- Output csv data\n";
foreach($fieldArray as $thisField) {
$myStr = "$thisField" . "_checked";
echo "                        if (\$$myStr == 'X') {\n";
echo "                            if (\$needComma) {\n";
echo "                                \$content .= '\t';\n";
echo "                            }\n";
echo "                            \$content .= \$$thisField;\n";
echo "                            \$needComma = TRUE;\n";
echo "                        }\n";
}

?>