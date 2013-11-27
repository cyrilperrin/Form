<?php

namespace CyrilPerrin\Form;

/**
 * Form text
 */
class Field_Input_Text extends Field_Input
{
    // Types
    const TYPE_MONOLINE = 1;
    const TYPE_MULTILINE = 2;
    const TYPE_PASSWORD = 3;
    
    /** @var $_type bool type */
    private $_type;
    
    /** $_inputSize int|array size of text input */
    private $_inputSize;
    
    /** @var $_textSize int max */
    private $_textSize;
    
    /** @var $_placeholder string placeholder */
    private $_placeholder;
    
    /**
     * Constructor
     * @param $name string name
     * @param $description string description
     * @param $type int type
     * @param $inputSize int|array size of text input
     * @param $textSize int maximal number of caracters
     * @param $init string init value
     * @param $placeholder string placeholder
     * @param $attributes string initial HTML attributes
     */
    public function __construct($name,$description=null,
        $type=Field_Input_Text::TYPE_MONOLINE,$inputSize=null,$textSize=null,
        $init=null,$placeholder=null,$attributes=null)
    {
        // Call parent constructor
        parent::__construct($name, $description, $attributes, $init);
        
        // Set attributes
        $this->_type = $type;
        $this->_inputSize = $inputSize;
        $this->_textSize = $textSize;
        $this->_placeholder = $placeholder;
    }
    
    /**
     * @see Field::validate($method)
     */
    public function validate($method)
    {
        // Call parent validate method
        parent::validate($method);
        if (!$this->_isValid) {
            return false;
        }
        
        // Check text size
        $this->_isValid =  $this->_textSize == null ||
                          strlen($this->getValue()) <= $this->_textSize;
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
     * Set type
     * @param $type int type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }
    
    /**
     * Set input size
     * @param $inputSize int|array size of text input
     */
    public function setInputSize($inputSize)
    {
        $this->_inputSize = $inputSize;
    }
    
    /**
     * Set text size
     * @param $textSize int maximal number of caracters
     */
    public function setTextSize($textSize)
    {
        $this->_textSize = $textSize;
    }
    
    /**
     * Set placeholder
     * @param $placeholder string placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->_placeholder = $placeholder;
    }
    
    /**
     * @see Field::__toString()
     */
    public function __toString()
    {
        // Input/Textarea ?
        if ($this->_type == self::TYPE_MONOLINE || $this->_type == self::TYPE_PASSWORD) {
            return $this->_before.'<input type="'.($this->_type == self::TYPE_MONOLINE ? 'text' : 'password').'" '.
                          'name="'.$this->_name.'" '.
                          ($this->getValue() != null ? 'value="'.htmlspecialchars($this->getValue()).'" ' : '').
                          ($this->_inputSize != null ? 'size="'.$this->_inputSize.'" ' : '').
                          ($this->_textSize != null ? 'maxlength="'.$this->_textSize.'" ' : '').
                          ($this->_placeholder != null ? 'placeholder="'.$this->_placeholder.'" ' : '').
                          ($this->_attributes != null ? $this->_attributes.' ' : '').'/>'.$this->_after;
        } else {
            return $this->_before.'<textarea name="'.$this->_name.'" '.
                   ($this->_inputSize != null ?
                       'cols="'.$this->_inputSize[0].'" rows="'.$this->_inputSize[1].'" ' : '').
                   ($this->_textSize != null ?
                       'onchange="if(this.value.length > '.$this->_textSize.') { '.
                       'this.value = this.value.substr(0,'.$this->_textSize.'); }" ' : '').
                   ($this->_placeholder != null ? 'placeholder="'.$this->_placeholder.'" ' : '').
                   ($this->_attributes != null ? $this->_attributes.' ' : '').'>'.
                   ($this->getValue() != null ? htmlspecialchars($this->getValue()) : '').
                   '</textarea>'.$this->_after;
        }
    }
}