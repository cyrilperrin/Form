<?php

namespace CyrilPerrin\Form;

/**
 * Form select
 */
class Field_Input_Select extends Field_Input
{
    /** @var  array values */
    private $_values;
    
    /** @var  bool multiple select ? */
    private $_isMultiple;
    
    /** @var  bool select as list ? */
    private $_isList;
    
    /** @var  int size of select input */
    private $_inputSize;
    
    /**
     * Constructor
     * @param $name string name
     * @param $values array values
     * @param $description string description
     * @param $isMultiple bool multiple select ?
     * @param $isList bool select as list ?
     * @param $inputSize int size of select input
     * @param $init ?|array inital value(s)
     * @param $attributes string initial HTML attributes
     */
    public function __construct($name,$values,$description=null,
        $isMultiple=false,$isList=true,$inputSize=null,$init=null,
        $attributes=null)
    {
        // Call parent constructor
        parent::__construct(
            $name, $description, $attributes,
            $init != null || !$isMultiple ? $init : array()
        );
        
        // Set attributes
        $this->_values = $values;
        $this->_isMultiple = $isMultiple;
        $this->_isList = $isList;
        $this->_inputSize = $inputSize;
    }
    
    /**
     * @see Field_Input::validate($method)
     */
    public function validate($method)
    {
        // Checkbox ?
        if ($this->_isMultiple && !$this->_isList) {
            // Submitted
            $this->_isSubmitted = true;
            
            // Selected values
            $selected = array(); $i = 0;
            foreach ($this->_values as $value => $description) {
                if ($method == Form::METHOD_GET ?
                isset($_GET[$this->_name.'_'.$i]) &&
                $_GET[$this->_name.'_'.$i] == 1 :
                isset($_POST[$this->_name.'_'.$i]) &&
                $_POST[$this->_name.'_'.$i] == 1) {
                    $selected[] = $value;
                }
                ++$i;
            }        
            $this->_isValid = $this->setValue($selected);
        } else {
            // Call parent validate method
            parent::validate($method);
        }
    }
    
    /**
     * @see Field_Input::setValue()
     */
    public function setValue($value)
    {
        // Call parent setValue method
        if (parent::setValue($value)) {
            // List/Multiple ?
            if ($this->_isList && $this->_isMultiple) {
                // Check if value is an array
                if (!is_array($value)) {
                    return false;
                }
                
                // Check if there not unexcepted values
                if (count(array_diff($value, array_keys($this->_values))) != 0) {
                    return false;
                }
            } else if (!$this->_isMultiple) {
                // Check if value is a excepted value
                if (!in_array($value, array_keys($this->_values))) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
    
    /**
     * @see Field::__toString()
     */
    public function __toString()
    {
        // Init string
        $string = '';
        
        // List or box
        if ($this->_isList) { // List
            $string .= '<select name="'.$this->_name.($this->_isMultiple ? '[]' : '').'"'.
                       ($this->_isMultiple ? ' multiple="multiple" ' : '').
                       ($this->_inputSize != null ? ' size="'.$this->_inputSize.'" ' : '').
                       ($this->_attributes != null ? ' '.$this->_attributes : '').'>';
            foreach ($this->_values as $value => $description) {
                $string .= '<option value="'.htmlspecialchars($value).'" '.
                           ($this->_isMultiple && in_array($value, $this->getValue()) ||
                           !$this->_isMultiple && $this->getValue() == $value ?
                           'selected="selected" ' : '').'>'.
                           $description.
                           '</option>';
            }
            $string .= '</select>';
        } else { // Box
            $string .= '<ul style="list-style:none;padding:0px;margin:0px;">';
            foreach ($this->_values as $value => $description) {
                $string .= '<li>';
                if ($this->_isMultiple) {
                    $i = isset($i) ? $i+1 : 0;
                    $string .= '<label><input type="checkbox" '.
                               'name="'.$this->_name.'_'.$i.'" value="1" '.
                               (in_array($value, $this->getValue()) ? 'checked="checked" ' : '').
                               '/> '.$description.'</label>';
                } else {
                    $string .= '<label><input type="radio" name="'.$this->_name.
                               '" value="'.htmlspecialchars($value).'" '.
                               ($this->getValue() == $value ? 'checked="checked" ' : '').
                               '/> '.$description.'</label>';
                }
                $string .= '</li>';
            }
            $string .= '</ul>';
        }
        
        // Return string
        return $this->_before.$string.$this->_after;
    }
}