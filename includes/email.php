<?php
/**
 * Email Functions
 * PHP 8.4 Pure Functional Script
 */
  use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    
// بررسی وجود autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
  
    $phpmailer_available = true;
} else {
    $phpmailer_available = false;
}

/**
 * Send an email
 *
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email body
 * @param array $attachments Optional attachments
 * @return bool True if email sent successfully
 */
function send_email($to, $subject, $message, $attachments = []) {
    global $phpmailer_available;
    
    if (!$phpmailer_available) {
        // استفاده از mail() ساده PHP اگر PHPMailer در دسترس نیست
        return send_simple_email($to, $subject, $message);
    }
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        
        // Recipients
        $mail->setFrom(MAIL_USERNAME, SITE_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        // Add attachments
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                $mail->addAttachment($attachment);
            }
        }
        
        // Send email
        $mail->send();
        
        return true;
    } catch (Exception $e) {
        error_log('Email Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send an HTML email
 *
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $html_message HTML email body
 * @param string $text_message Text email body (fallback)
 * @param array $attachments Optional attachments
 * @return bool True if email sent successfully
 */
function send_html_email($to, $subject, $html_message, $text_message = '', $attachments = []) {
    global $phpmailer_available;
    
    if (!$phpmailer_available) {
        // استفاده از mail() ساده PHP اگر PHPMailer در دسترس نیست
        return send_simple_email($to, $subject, strip_tags($html_message));
    }
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        
        // Recipients
        $mail->setFrom(MAIL_USERNAME, SITE_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_message;
        
        // Set plain text alternative
        if (!empty($text_message)) {
            $mail->AltBody = $text_message;
        } else {
            $mail->AltBody = strip_tags($html_message);
        }
        
        // Add attachments
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                $mail->addAttachment($attachment);
            }
        }
        
        // Send email
        $mail->send();
        
        return true;
    } catch (Exception $e) {
        error_log('Email Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send simple email using PHP's mail() function
 *
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email body
 * @return bool True if email sent successfully
 */
function send_simple_email($to, $subject, $message) {
    $headers = [
        'From: ' . SITE_NAME . ' <' . SITE_EMAIL . '>',
        'Reply-To: ' . SITE_EMAIL,
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    $result = mail($to, $subject, $message, implode("\r\n", $headers));
    
    if (!$result) {
        error_log('Simple Email Error: Failed to send email to ' . $to);
    }
    
    return $result;
}

/**
 * Generate email verification message
 *
 * @param string $name User's name
 * @param string $verify_token Verification token
 * @return array HTML and plain text messages
 */
function get_verification_email($name, $verify_token) {
    $verify_url = SITE_URL . '/verify.php?token=' . $verify_token;
    
    // Plain text version
    $text = "Hello $name,\n\n"
          . "Thank you for registering at " . SITE_NAME . ". "
          . "Please verify your email by clicking the link below:\n\n"
          . $verify_url . "\n\n"
          . "This link will expire in 24 hours.\n\n"
          . "Regards,\n" . SITE_NAME . " Team";
    
    // HTML version
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <title>Verify Your Email</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 20px; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; }
            .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; 
                      text-decoration: none; border-radius: 4px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Verify Your Email Address</h2>
            </div>
            <p>Hello $name,</p>
            <p>Thank you for registering at " . SITE_NAME . ". Please verify your email by clicking the button below:</p>
            <p style='text-align: center;'>
                <a href='$verify_url' class='button'>Verify Email</a>
            </p>
            <p>Or copy and paste the following link into your browser:</p>
            <p>$verify_url</p>
            <p>This link will expire in 24 hours.</p>
            <p>Regards,<br>" . SITE_NAME . " Team</p>
            <div class='footer'>
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return [
        'html' => $html,
        'text' => $text
    ];
}

/**
 * Generate password reset email message
 *
 * @param string $name User's name
 * @param string $reset_token Reset token
 * @return array HTML and plain text messages
 */
function get_password_reset_email($name, $reset_token) {
    $reset_url = SITE_URL . '/reset-password.php?token=' . $reset_token;
    
    // Plain text version
    $text = "Hello $name,\n\n"
          . "You requested a password reset for your account at " . SITE_NAME . ". "
          . "Please click the link below to reset your password:\n\n"
          . $reset_url . "\n\n"
          . "This link will expire in 1 hour. If you did not request this reset, "
          . "please ignore this email.\n\n"
          . "Regards,\n" . SITE_NAME . " Team";
    
    // HTML version
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <title>Reset Your Password</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 20px; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; }
            .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; 
                      text-decoration: none; border-radius: 4px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Reset Your Password</h2>
            </div>
            <p>Hello $name,</p>
            <p>You requested a password reset for your account at " . SITE_NAME . ". Please click the button below to reset your password:</p>
            <p style='text-align: center;'>
                <a href='$reset_url' class='button'>Reset Password</a>
            </p>
            <p>Or copy and paste the following link into your browser:</p>
            <p>$reset_url</p>
            <p>This link will expire in 1 hour. If you did not request this reset, please ignore this email.</p>
            <p>Regards,<br>" . SITE_NAME . " Team</p>
            <div class='footer'>
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return [
        'html' => $html,
        'text' => $text
    ];
}

/**
 * Test email configuration
 *
 * @return array Test results
 */
function test_email_config() {
    global $phpmailer_available;
    
    $results = [
        'phpmailer_available' => $phpmailer_available,
        'mail_function_available' => function_exists('mail'),
        'constants_defined' => [
            'MAIL_HOST' => defined('MAIL_HOST'),
            'MAIL_USERNAME' => defined('MAIL_USERNAME'),
            'MAIL_PASSWORD' => defined('MAIL_PASSWORD'),
            'MAIL_PORT' => defined('MAIL_PORT'),
            'MAIL_ENCRYPTION' => defined('MAIL_ENCRYPTION')
        ]
    ];
    
    return $results;
}