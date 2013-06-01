<?php namespace CC\Helper;

class Mail
{
    /**
     * @param string $subject
     * @param string $message
     * @param array $to
     */
    public function send($subject, $message, Array $to)
    {
        $to_with_email_key = array();
        foreach ($to as $email) {
            $to_with_email_key[] = array(
                'email' => $email
            );
        }

        $mandrill = new \Mandrill('dgL0RgqzhF4JBZSfUV0s6A');
        $mandrill->call('/messages/send', array(
            'message' => array(
                'text' => $message,
                'subject' => $subject,
                'from_email' => 'no-reply@dewlearning.com',
                'from_name' => 'Dew Learning - Lesson Designer',
                'to' => $to_with_email_key
            )
        ));
    }
}

