<?php

namespace CyrilPerrin\Form;

/**
 * Form submit
 */
class Field_Submit extends Field
{
    /** @var $_text string text */
    private $_text;
    
    /**
     * Constructor
     * @param $name string name
     * @param $text string text
     * @param $attributes string initial HTML attributes 
     */
    public function __construct($name='submit',$text=null,$attributes=null)
    {
        // Call parent constructor
        parent::__construct($name, null, $attributes);
        
        // Set text
        $this->_text = $text;
    }
    
    /**
     * @see Field::validate($method)
     */
    public function validate($method)
    {
        if ($method == Form::METHOD_GET) {
            $this->_isValid = $this->_isSubmitted =  isset($_GET[$this->_name]);
        } else {
            $this->_isValid = $this->_isSubmitted =  isset($_POST[$this->_name]);
        }
    }
    
    /**
     * @see Field::__toString()
     */
    public function __toString()
    {
        return $this->_before.
               '<input type="submit" name="'.$this->_name.'" '.
               ($this->_text != null ? 'value="'.$this->_text.'" ' : '').
               ($this->_attributes != null ? $this->_attributes.' ' : '').
               '/>'.
               $this->_after;
    }
}