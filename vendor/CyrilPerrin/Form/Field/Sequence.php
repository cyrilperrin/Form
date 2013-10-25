<?php

namespace CyrilPerrin\Form;

/**
 * Form sequence
 */
class Field_Sequence extends Field
{
    /** @var $_regexp string regular expression */
    private $_regexp;
    
    /** @var $_callback callback fields provider */
    private $_callback;
    
    /** @var $_fields array fields */
    private $_fields = array();
    
    /**
     * Constructor
     * @param $name string name
     * @param $regexp string regular expression
     * @param $callback callback field provider
     */
    public function __construct($name,$regexp,$callback)
    {
        // Call parent constructor
        parent::__construct($name);
        
        // Set attributes
        $this->_regexp = $regexp;
        $this->_callback = $callback;
    }
    
    /**
     * @see Field::validate()
     */
    public function validate($method)
    {
        // Get matching names to regular expression 
        $names = array_filter(
            array_keys($method == Form::METHOD_GET ? $_GET : $_POST),
            array($this,'isMatchingName')
        );
        
        // If there are matching names to regular expression 
        if (count($names) != 0) {
            // Is valid/Is submitted
            $this->_isValid = true;
            $this->_isSubmitted = false;
            
            // Instanciate fields
            foreach ($names as $name) {
                // Get field
                $field = call_user_func($this->_callback, $name);
                
                // Save field
                $this->_fields[$name] = $field;
                
                // Validate field
                $field->validate($method);
                
                // Is valid/Is submitted ?
                if (!$this->_isSubmitted && $field->isSubmitted()) {
                    $this->_isSubmitted = true;
                }
                if ($this->_isValid && !$field->isValid()) {
                    $this->_isValid = false;
                }
            }
        }
    }
    
    /**
     * Is matching name to regular expression ?
     * @param $name string name
     * @return maching name to regular expression ?
     */
    public function isMatchingName($name)
    {
        return preg_match($this->_regexp, $name) == 1;
    }
    
    /**
     * Get fields
     * @return array fields
     */
    public function getFields()
    {
        return $this->_fields;
    }
    
    /**
     * @see Field::__toString()
     */
    public function __toString()
    {
        return '';
    }
}