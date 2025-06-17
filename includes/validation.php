<?php
/**
 * Validation Functions
 * PHP 8.4 Pure Functional Script
 */

/**
 * Validate required field
 *
 * @param string $value Field value
 * @param string $field_name Field name for error message
 * @return string|null Error message or null if valid
 */
function validate_required($value, $field_name) {
    if (empty(trim($value))) {
        return ucfirst($field_name) . ' is required';
    }
    return null;
}

/**
 * Validate email format
 *
 * @param string $email Email to validate
 * @return string|null Error message or null if valid
 */
function validate_email($email) {
    if (empty(trim($email))) {
        return 'Email is required';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email format';
    }
    
    return null;
}

/**
 * Validate password strength
 *
 * @param string $password Password to validate
 * @return string|null Error message or null if valid
 */
function validate_password($password) {
    if (empty($password)) {
        return 'Password is required';
    }
    
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must contain at least one number';
    }
    
    return null;
}

/**
 * Validate password confirmation
 *
 * @param string $password Password
 * @param string $confirm_password Password confirmation
 * @return string|null Error message or null if valid
 */
function validate_password_confirm($password, $confirm_password) {
    if ($password !== $confirm_password) {
        return 'Passwords do not match';
    }
    
    return null;
}

/**
 * Validate numeric field
 *
 * @param mixed $value Value to validate
 * @param string $field_name Field name for error message
 * @return string|null Error message or null if valid
 */
function validate_numeric($value, $field_name) {
    if (!is_numeric($value)) {
        return ucfirst($field_name) . ' must be a number';
    }
    
    return null;
}

/**
 * Validate minimum length
 *
 * @param string $value Value to validate
 * @param int $min Minimum length
 * @param string $field_name Field name for error message
 * @return string|null Error message or null if valid
 */
function validate_min_length($value, $min, $field_name) {
    if (strlen(trim($value)) < $min) {
        return ucfirst($field_name) . ' must be at least ' . $min . ' characters long';
    }
    
    return null;
}

/**
 * Validate maximum length
 *
 * @param string $value Value to validate
 * @param int $max Maximum length
 * @param string $field_name Field name for error message
 * @return string|null Error message or null if valid
 */
function validate_max_length($value, $max, $field_name) {
    if (strlen(trim($value)) > $max) {
        return ucfirst($field_name) . ' must not exceed ' . $max . ' characters';
    }
    
    return null;
}

/**
 * Validate file upload
 *
 * @param array $file File from $_FILES
 * @param array $allowed_types Allowed MIME types
 * @param int $max_size Maximum file size in bytes
 * @return string|null Error message or null if valid
 */
function validate_file($file, $allowed_types, $max_size) {
    if (empty($file['tmp_name'])) {
        return 'No file uploaded';
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'File upload error: ' . $file['error'];
    }
    
    if ($file['size'] > $max_size) {
        return 'File is too large (maximum ' . ($max_size / 1024 / 1024) . 'MB)';
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime_type, $allowed_types)) {
        return 'Invalid file type. Allowed types: ' . implode(', ', $allowed_types);
    }
    
    return null;
}

/**
 * Validate form data and return errors
 *
 * @param array $data Form data
 * @param array $rules Validation rules
 * @return array Validation errors
 */
function validate_form($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $field_rules) {
        foreach ($field_rules as $rule) {
            $rule_name = $rule['rule'];
            $params = $rule['params'] ?? [];
            
            // Skip validation if field is not required and empty
            if ($rule_name !== 'validate_required' && 
                empty($data[$field]) && 
                !in_array('validate_required', array_column($field_rules, 'rule'))) {
                continue;
            }
            
            // Prepare parameters for the validation function
            $value = $data[$field] ?? '';
            $func_params = [$value];
            
            if (!empty($params)) {
                $func_params = array_merge($func_params, $params);
            }
            
            // Add field name for better error messages
            $field_name = $rule['field_name'] ?? $field;
            $func_params[] = $field_name;
            
            // Call validation function
            $error = call_user_func_array($rule_name, $func_params);
            
            if ($error !== null) {
                $errors[$field] = $error;
                break; // Stop validation for this field after first error
            }
        }
    }
    
    return $errors;
}