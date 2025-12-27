<?php
/**
 * Validates a student record against a defined set of rules.
 *
 * @param int $student_id The ID of the student record to validate.
 * @return array An array of validation error messages. Empty if validation passes.
 */
function cwa_validate_student_record( int $student_id ): array {
    global $wpdb;

    $errors = [];
    $table_student = $wpdb->prefix . 'cwa_student';
    $table_advisor = $wpdb->prefix . 'cwa_advisor';

    // --- 1. Define Expected Field Names from SQL (for verification) ---
    $expected_fields = [
        'student_id', 'student_call_sign', 'student_time_zone', 'student_timezone_offset',
        'student_youth', 'student_age', 'student_parent', 'student_parent_email',
        'student_level', 'student_class_language', 'student_waiting_list',
        'student_request_date', 'student_semester', 'student_notes', 'student_welcome_date',
        'student_email_sent_date', 'student_email_number', 'student_response',
        'student_response_date', 'student_abandoned', 'student_status', 'student_action_log',
        'student_pre_assigned_advisor', 'student_selected_date', 'student_no_catalog',
        'student_hold_override', 'student_messaging', 'student_assigned_advisor',
        'student_advisor_select_date', 'student_advisor_class_timezone',
        'student_hold_reason_code', 'student_class_priority', 'student_assigned_advisor_class',
        'student_promotable', 'student_excluded_advisor', 'student_survey_completion_date',
        'student_available_class_days', 'student_intervention_required', 'student_copy_control',
        'student_first_class_choice', 'student_second_class_choice', 'student_third_class_choice',
        'student_first_class_choice_utc', 'student_second_class_choice_utc',
        'student_third_class_choice_utc', 'student_catalog_options', 'student_flexible',
        'student_date_created', 'student_date_updated'
    ];

    // --- 2. Define All Fields Used in Validation Rules ---
    $rule_fields = [
        'student_call_sign', 'student_time_zone', 'student_timezone_offset', 'student_level',
        'student_class_language', 'student_semester', 'student_first_class_choice',
        'student_second_class_choice', 'student_third_class_choice', 'student_youth',
        'student_waiting_list', 'student_email_number', 'student_response', 'student_abandoned',
        'student_status', 'student_hold_reason_code', 'student_promotable',
        'student_intervention_required', 'student_hold_override', 'student_flexible',
        'student_pre_assigned_advisor', 'student_assigned_advisor',
        'student_advisor_select_date', 'student_assigned_advisor_class'
    ];

    // --- Field Spelling Verification ---
    $missing_fields = array_diff( $rule_fields, $expected_fields );
    if ( ! empty( $missing_fields ) ) {
        $errors[] = 'Configuration Error: The following fields used in the validation rules do not exist in the table definition: ' . implode( ', ', $missing_fields );
        return $errors; // Halt execution on configuration error
    }

    // --- 3. Retrieve Data ---
    $record = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$table_student} WHERE student_id = %d",
        $student_id
    ) );

    if ( $record === null ) {
        $errors[] = "Error: No student record found with student_id: {$student_id}";
        return $errors;
    }
    
    // --- Get Initialization Array (Required for Rule 2) ---
    // NOTE: The function data_initialization_func() must be defined elsewhere.
    if ( ! function_exists( 'data_initialization_func' ) ) {
         $errors[] = 'Configuration Error: Required function data_initialization_func() is not defined.';
         return $errors;
    }
    $initializationArray = data_initialization_func();
    $language_array = $initializationArray['languageArray'] ?? [];
    
    // Convert object to array for easier access and validation
    $data = (array) $record;

    // --- 4. Apply Validation Rules ---

    // --- Rule 1: Fields Must Not Be Empty ---
    $required_fields = [
        'student_call_sign', 'student_time_zone', 'student_timezone_offset', 'student_level',
        'student_class_language', 'student_semester', 'student_first_class_choice',
        'student_second_class_choice', 'student_third_class_choice'
    ];

    foreach ( $required_fields as $field ) {
        // Use trim() to check for strings consisting only of whitespace
        if ( empty( $data[ $field ] ) && $data[ $field ] !== 0 && $data[ $field ] !== 0.0 ) {
            $errors[] = "Rule 1 Failure: Field `{$field}` must not be empty.";
        }
    }

    // --- Rule 2: Valid Contents/Enumerations/Regex ---
    $enum_rules = [
        'student_youth'               => [ '', 'Y', 'N', 'Yes', 'No' ],
        'student_level'               => [ 'Beginner', 'Fundamental', 'Intermediate', 'Advanced' ],
        'student_waiting_list'        => [ '', 'Y', 'N' ],
        'student_email_number'        => [ '0', '1', '2', '3', '4' ], // Stored as tinyint, but often compared as string/int
        'student_response'            => [ '', 'Y', 'R' ],
        'student_abandoned'           => [ '', 'Y', 'N' ],
        'student_status'              => [ '', 'C', 'N', 'R', 'S', 'V', 'Y', 'U'  ],
        'student_hold_reason_code'    => [ '', 'X', 'E', 'H', 'Q', 'W', 'M', 'B', 'N', 'L' ],
        'student_promotable'          => [ '', 'P', 'N', 'Q', 'W' ],
        'student_intervention_required' => [ '', 'H' ],
        'student_hold_override'       => [ '', 'Y' ],
        'student_flexible'            => [ '', 'Y', 'N' ],
    ];

    foreach ( $enum_rules as $field => $valid_values ) {
        // Check if the actual value is in the array of valid values
        if ( ! in_array( $data[ $field ], $valid_values, true ) ) {
            $errors[] = "Rule 2 Failure: Field `{$field}` has an invalid value: '{$data[$field]}'. Valid values are: " . implode( ', ', $valid_values );
        }
    }

    // Special Rule 2 Checks
    // 2.3 `student_class_language` is in the array $initializationArray['languateArray']
    if ( ! in_array( $data['student_class_language'], $language_array, true ) ) {
        $errors[] = "Rule 2 Failure: Field `student_class_language` ('{$data['student_class_language']}') is not in the allowed list of languages.";
    }

    // 2.5 `student_semester` matches regex ^\d{4}\s[A-Z][a-z]{2}\/[A-Z][a-z]{2}$
    $semester_regex = '/^\d{4}\s[A-Z][a-z]{2}\/[A-Z][a-z]{2}$/';
    if ( ! preg_match( $semester_regex, $data['student_semester'] ) ) {
        $errors[] = "Rule 2 Failure: Field `student_semester` ('{$data['student_semester']}') does not match the required format (e.g., 2026 Jan/Feb).";
    }

    // --- Rule 3: Cross-Table Matches ---

    // 3.1 & 3.2: Helper function for checking advisor call sign validity
    $check_advisor_exists = function( $call_sign_field, $call_sign, $semester ) use ( $wpdb, $table_advisor, &$errors ) {
        if ( $call_sign != '') {
            $advisor_exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT advisor_call_sign FROM {$table_advisor} 
                 WHERE advisor_call_sign = %s AND advisor_semester = %s",
                $call_sign,
                $semester
            ) );
            
            if ( $advisor_exists === null ) {
                $errors[] = "Rule 3 Failure: Field `{$call_sign_field}` ('{$call_sign}') is set but does not match an advisor in table {$table_advisor} for the semester '{$semester}'.";
            }
        }
    };

    // 3.1 student_pre_assigned_advisor check
    $check_advisor_exists( 'student_pre_assigned_advisor', $data['student_pre_assigned_advisor'], $data['student_semester'] );

    // 3.2 student_assigned_advisor check
    $check_advisor_exists( 'student_assigned_advisor', $data['student_assigned_advisor'], $data['student_semester'] );


    // --- Rule 4: Conditional Logic ---
    
    // 4.1 if student_intervention_required = 'H' then student_hold_reason_code must not be empty
    if ( $data['student_intervention_required'] === 'H' && empty( $data['student_hold_reason_code'] ) ) {
        $errors[] = "Rule 4.1 Failure: If `student_intervention_required` is 'H', then `student_hold_reason_code` must not be empty.";
    }

    // 4.2 if student_hold_reason_code is not empty and not 'X' then student_intervention_required must be 'H'
    if ($data['student_hold_reason_code'] != 'X') {
        if ( ! empty( $data['student_hold_reason_code'] ) && $data['student_intervention_required'] !== 'H' ) {
         $errors[] = "Rule 4.2 Failure: If `student_hold_reason_code` is not empty and not 'X', then `student_intervention_required` must be 'H'.";
        }
    }
    
    // 4.3 if student_status = 'S' or 'Y' or 'V' then student_assigned_advisor must not be empty
    if ( in_array( $data['student_status'], ['S', 'Y', 'V'], true ) && empty( $data['student_assigned_advisor'] ) ) {
        $errors[] = "Rule 4.3 Failure: If `student_status` is 'S', 'Y', or 'V', then `student_assigned_advisor` must not be empty.";
    }

    // 4.4 if student_assigned_advisor is not empty then student_status must be 'Y' or 'S' or 'V' or 'R' or 'C'
    if ( ! empty( $data['student_assigned_advisor'] ) && ! in_array( $data['student_status'], ['Y', 'S', 'V', 'R', 'C'], true ) ) {
        $errors[] = "Rule 4.4 Failure: If `student_assigned_advisor` is not empty, then `student_status` must be 'Y', 'S', 'R', 'C',  or 'V'.";
    }
    
    // 4.5 & 4.6: Timezone and Offset cross-check
    $timezone_offset = (float) $data['student_timezone_offset']; // Cast to float for accurate comparison

    // 4.5 if student_time_zone = '??' then student_timezone_offset should be -99.00
    if ( $data['student_time_zone'] === '??' && $timezone_offset !== -99.00 ) {
        $errors[] = "Rule 4.5 Failure: If `student_time_zone` is '??', then `student_timezone_offset` must be -99.00.";
    }
    
    // 4.6 if student_timezone_offset = -99.00 then student_time_zone should be '??'
    if ( $timezone_offset === -99.00 && $data['student_time_zone'] !== '??' ) {
        $errors[] = "Rule 4.6 Failure: If `student_timezone_offset` is -99.00, then `student_time_zone` must be '??'.";
    }

    // 4.7 & 4.8: Assigned Advisor and Select Date cross-check

    // 4.7 if student_assigned_advisor is not empty then student_advisor_select_date should not be empty
    if ( ! empty( $data['student_assigned_advisor'] ) && empty( $data['student_advisor_select_date'] ) ) {
        $errors[] = "Rule 4.7 Failure: If `student_assigned_advisor` is not empty, then `student_advisor_select_date` must not be empty.";
    }
    
    // 4.8 if student_advisor_select_date is not empty then student_assigned_advisor should not be empty
    if ( ! empty( $data['student_advisor_select_date'] ) && empty( $data['student_assigned_advisor'] ) ) {
        $errors[] = "Rule 4.8 Failure: If `student_advisor_select_date` is not empty, then `student_assigned_advisor` must not be empty.";
    }

    // 4.9: Assigned Advisor and Class cross-check
    $assigned_class = (int) $data['student_assigned_advisor_class'];

    // 4.9 if student_assigned_advisor is not empty then student_assigned_advisor_class should be greater than 0
    if ( ! empty( $data['student_assigned_advisor'] ) && $assigned_class <= 0 ) {
        $errors[] = "Rule 4.9 Failure: If `student_assigned_advisor` is not empty, then `student_assigned_advisor_class` must be greater than 0.";
    }

    // 4.10: Assigned Class and Advisor Presence
    $pre_assigned = $data['student_pre_assigned_advisor'];
    $assigned = $data['student_assigned_advisor'];
    
    // 4.10 if student_assigned_advisor_class is greater than 0 then either student_assigned_advisor or student_pre_assigned_advisor must not be blank
    if ( $assigned_class > 0 && empty( $assigned ) && empty( $pre_assigned ) ) {
        $errors[] = "Rule 4.10 Failure: If `student_assigned_advisor_class` is greater than 0, then either `student_assigned_advisor` or `student_pre_assigned_advisor` must be set.";
    }

    // 4.11: Pre-Assigned Advisor and Assigned Class
    
    // 4.11 if student_pre_assigned_advisor is not blank then student_assigned_advisor_class must be greater than 0
    if ( ! empty( $pre_assigned ) && $assigned_class <= 0 ) {
        $errors[] = "Rule 4.11 Failure: If `student_pre_assigned_advisor` is not empty, then `student_assigned_advisor_class` must be greater than 0.";
    }

    return $errors;
}