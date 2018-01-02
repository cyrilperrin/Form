<?php

namespace CyrilPerrin\Form\Field;

use CyrilPerrin\Form\Field;
use CyrilPerrin\Form\Form;

/**
 * Class to extend to become a form input
 */
abstract class Input extends Field
{
    /** @var $_value string value */
    protected $_value;
    
    /** @var $_regexp array regular expression */
    private $_regexp = null;
    
    /**
     * Constructor
     * @param $name string name
     * @param $description string description
     * @param $attributes string initial HTML attributes
     * @param $init string initial value
     */
    public function __construct($name,$description=null,$attributes=null,
        $init=null)
    {
        // Call parent constructor
        parent::__construct($name, $description, $attributes);
        
        // Value
        $this->_value = $init;
    }
    
    /**
     * Get value
     * @return string value
     */
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * Set value
     * @param string value
     * @return bool valid value ?
     */
    public function setValue($value)
    {
        // Set value
        $this->_value = $value;
        
        // Check if value is valid
        return $this->_regexp == null ||
               preg_match($this->_regexp, $this->_value) != 0;
    }
    
    /**
     * Set regexp
     * @param $regexp string regular expression to valid value
     */
    public function setRegexp($regexp)
    {
        $this->_regexp = $regexp;
    }
    
    /**
     * See Field::validate($method)
     */
    public function validate($method)
    {
        // Is submitted ?
        if ($method == Form::METHOD_GET) {
            $this->_isSubmitted = array_key_exists($this->_name, $_GET) &&
                                 $_GET[$this->_name] !== '';
        } else {
            $this->_isSubmitted = array_key_exists($this->_name, $_POST) &&
                                 $_POST[$this->_name] !== '';
        }
        
        // Set value/Is valid ?
        if ($this->_isSubmitted) {
            if ($method == Form::METHOD_GET) {
                $this->_isValid = $this->setValue($_GET[$this->_name]);
            } else {
                $this->_isValid = $this->setValue($_POST[$this->_name]);
            }
        }
    }
}