<?php

namespace CyrilPerrin\Form;

/**
 * Form captcha
 */
class Field_Captcha extends Field
{
    /** @var $_publicKey string public key */
    private $_publicKey;
    
    /** @var $_privateKey string private key */
    private $_privateKey;
    
    /** @var $_errorCaptcha string error captcha */
    private $_errorCaptcha = null;
    
    /**
     * Constructor
     * @param $publicKey string public key
     * @param $privateKey string private key
     */
    public function __construct($publicKey,$privateKey)
    {
        // Call parent constructor
        parent::__construct(null, null, null);
        
        // Set attributes
        $this->_publicKey = $publicKey;
        $this->_privateKey = $privateKey;
    }
    
    /**
     * @see Field::validate($method)
     */
    public function validate($method)
    {
        // Submitted ?
        if ($method == Form::METHOD_POST) {
            $this->_isSubmitted = !empty($_POST['recaptcha_challenge_field']) &&
                                 !empty($_POST['recaptcha_response_field']); 
        } else {
            $this->_isSubmitted = !empty($_GET['recaptcha_challenge_field']) &&
                                 !empty($_GET['recaptcha_response_field']);
        }
        
        if (!$this->_isSubmitted) {
            return false;
        }
        
        // Challenge and response
        if ($method == Form::METHOD_POST) {
            $userChallenge = $_POST['recaptcha_challenge_field'];
            $userResponse = $_POST['recaptcha_response_field'];
        } else {
            $userChallenge = $_GET['recaptcha_challenge_field'];
            $userResponse = $_GET['recaptcha_response_field'];
        }

        // Data
        $data = array (
            'privatekey' => $this->_privateKey,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
            'challenge' => $userChallenge,
            'response' => $userResponse
        );
        
        // Prepare request
        $request = array();
        foreach ($data as $key => $value) {
            $request[] = $key.'='.urlencode(stripslashes($value));
        }
        $request = implode('&', $request);

        // Prepare http request
        $httpRequest = 'POST /recaptcha/api/verify HTTP/1.0'."\r\n".
                       'Host: www.google.com'."\r\n".
                       'Content-Type: application/x-www-form-urlencoded;'.
                       "\r\n".
                       'Content-Length: '.strlen($request)."\r\n".
                       'User-Agent: reCAPTCHA/PHP'."\r\n".
                       "\r\n".
                       $request;

        // Open socket
        $fs = @fsockopen('www.google.com', 80, $errno, $errstr, 10);
        if ($fs == false) {
            throw new Exception('Could not open socket');
        }
        
        // Write
        fwrite($fs, $httpRequest);

        // Listen
        $response = '';
        while (!feof($fs)) {
            $response .= fgets($fs);
        }
        
        // Close socket
        fclose($fs);
        
        // Check result
        $response = explode("\r\n\r\n", $response, 2);
        $answers = explode("\n", $response[1]);
        if (trim($answers[0]) == 'true') {
            $this->_isValid = true;
            $this->_errorCaptcha = null;
        } else {
            $this->_errorCaptcha = $answers[1];
        }
    }
    
    /**
     * @see Field::__toString()
     */
    public function __toString()
    {
        $error = '';
        if ($this->_errorCaptcha != null) {
            $error = '&error='.$this->_errorCaptcha;
        }   
        return $this->_before.
               '<script type="text/javascript" src="http://www.google.com/reca'.
               'ptcha/api/challenge?k='.$this->_publicKey.$error.'"></script>
                <noscript>
                     <iframe src="http://www.google.com/recaptcha/api/noscript'.
                     '?k='.$this->_publicKey.$error.'" height="300" width="500'.
                     '" frameborder="0"></iframe><br/>
                     <textarea name="recaptcha_challenge_field" rows="3" cols='.
                     '"40"></textarea>
                     <input type="hidden" name="recaptcha_response_field" valu'.
                     'e="manual_challenge"/>
                </noscript>'.
                $this->_after;
    }
}