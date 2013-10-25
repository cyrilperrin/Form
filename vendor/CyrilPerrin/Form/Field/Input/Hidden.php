<?php

namespace CyrilPerrin\Form;

/**
 * Form hidden
 */
class Field_Input_Hidden extends Field_Input
{
    /**
     * Constructor
     * @param $name string name
     */
    public function __construct($name,$value=null,$attributes=null)
    {
        // Call parent constructor
        parent::__construct($name, null, $attributes, $value);
    }
    
    /**
     * @see Field_Input::setValue($value)
     */
    public function setValue($value)
    {
        // Check if value is a string
        return parent::setValue($value) && is_string($value);
    }
    
    /**
     * @see Field::__toString()
     */
    public function __toString()
    {
        return $this->_before.
               '<input type="hidden" name="'.$this->_name.'" '.
               'value="'.htmlspecialchars($this->_value).'" '.
               ($this->_attributes != null ? $this->_attributes.' ' : '').
               '/>'.
               $this->_after;
    }
}