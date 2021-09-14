<?php 
/*
Plugin Name: WP SendGrid Mailer
Plugin URI: https://github.com/devwael
Description: Just another contact form plugin. Simple but flexible.
Author: Takayuki Miyoshi
Author URI: https://github.com/devwael
Version: 1.0
*/
//WP_SENDGRID_MAILER_API_KEY
include 'sendgrid-php/sendgrid-php.php';

/**
 * Sends an email, similar to PHP's mail function.
 *
 * A true return value does not automatically mean that the user received the
 * email successfully. It just only means that the method used was able to
 * process the request without any errors.
 *
 * The default charset is based on the charset used on the blog. The charset can
 * be set using the {@see 'wp_mail_charset'} filter.
 *
 * @param string|string[] $to          Array or comma-separated list of email addresses to send message.
 * @param string          $subject     Email subject.
 * @param string          $message     Message contents.
 * @param string|string[] $headers     Optional. Additional headers.
 * @param string|string[] $attachments Optional. Paths to files to attach.
 * @return bool Whether the email was sent successfully.
 */

if( defined( 'WP_SENDGRID_MAILER_API_KEY' ) ){
    function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ){
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom( "test@innoshop.co", "Innoshop Store" );
        $email->setSubject( $subject );
        $email->addTo( $to );
        $email->addContent( "text/html", $message );

        if ( ! empty( $attachments ) ) {
            foreach ( $attachments as $attachment ) {
                $file_encoded = base64_encode( file_get_contents( $attachment ) );
                $email->addAttachment( $file_encoded );
            }
        }

        $sendgrid = new \SendGrid( WP_SENDGRID_MAILER_API_KEY );
        try {
            $response = $sendgrid->send( $email );
            if( $result < 200 || $result > 299 ){
                $error_content = json_decode( $response->body() ); //get response 

                /**
                 * Fires after a PHPMailer\PHPMailer\Exception is caught.
                 *
                 * @since 4.4.0
                 *
                 * @param WP_Error $error A WP_Error object with the PHPMailer\PHPMailer\Exception message, and an array
                 *                        containing the mail recipient, subject, message, headers, and attachments.
                 */
                do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $error_content->errors[0]->message, $mail_error_data ) );
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) { //failed to send email
            $mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /**
             * Fires after a PHPMailer\PHPMailer\Exception is caught.
             *
             * @since 4.4.0
             *
             * @param WP_Error $error A WP_Error object with the PHPMailer\PHPMailer\Exception message, and an array
             *                        containing the mail recipient, subject, message, headers, and attachments.
             */
            do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_error_data ) );

            return false;
        }
        return false;
    }
}