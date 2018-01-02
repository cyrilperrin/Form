<?php

namespace CyrilPerrin\Form;

use CyrilPerrin\Form\Field\File;
use CyrilPerrin\Form\Field\Html;
use CyrilPerrin\Form\Field\Submit;
use CyrilPerrin\Form\FormRenderer\Table;

/**
 * Form
 */
class Form
{
    // Methods
    const METHOD_POST = 'post';
    const METHOD_GET = 'get';
    
    /** @var $_method string method */
    private $_method;
    
    /** @var $_action string action */
    private $_action;
    
    /** @var $_fields array fields */
    private $_fields = array();
    
    /** @var $_required array required fields */
    private $_required = array();
    
    /** @var $_group array group fields */
    private $_group = array();
    
    /** @var $_file bool file input in fields ? */
    private $_file = false;
    
    /** @var $_isSubmitted bool submitted form ? */
    private $_isSubmitted = false;
    
    /** @var $_isValid bool valid form ? */
    private $_isValid = false;
    
    /** @var $_attributes string attributes */
    private $_attributes = null;
    
    /** @var $_callback string callback to valid all fields */
    private $_callback = null;
    
    /** @var $_renderer FormRenderer form renderer */
    private $_renderer = null;
    
    /** @var $_error string error message */
    private $_error = null;
    
    /** @var $_errorStart string error messages start */
    private static $_errorStart = '<span style="color:red;">';
    
    /** @var $_errorEnd string error messages end */
    private static $_errorEnd = '</span>';
    
    /** @var $_msgRequired string message displayed when a field is not submitted */
    private static $_msgRequired = 'Required';
    
    /** @var $_msgInvalid string message displayed when a field is not valid */
    private static $_msgInvalid = 'Invalid';
    
    /**
     * Constructor
     * @param $method string method
     * @param $action string action
     * @param $callback string callback to valid field
     * @param $renderer FormRenderer form renderer
     */
    public function __construct($method=self::METHOD_POST,$action='#',
        $callback=null,FormRenderer $renderer=null)
    {
        // Set attributes
        $this->_method = $method;
        $this->_action = $action;
        $this->_callback = $callback;
        if ($renderer === null) {
            $this->_renderer = new Table();
        } else {
            $this->_renderer = $renderer;
        }
    }

    /**
     * Get form start
     * @return string form start
     */
    public function getStart()
    {
        return '<form method="'.$this->_method.'" '.
                     'action="'.$this->_action.'"'.
                     ($this->_file ? ' enctype="multipart/form-data"' : '').
                     ($this->_attributes != null ? ' '.$this->_attributes : '').
               '>';
    }
    
    /**
     * Get form end
     * @return string form end
     */
    public function getEnd()
    {
        return '</form>';
    }
    
    /**
     * Return fields
     * @return array fields
     */
    public function getFields()
    {
        return $this->_fields;
    }
    
    /**
     * Get error for an field
     * @param $field Form|Field field
     * @return string error
     */
    public function getError($field)
    {
        if ($field instanceof Form) {
            if ($this->_error != null) {
                return self::$_errorStart.$this->_error.self::$_errorEnd;
            }
            return null;
        }
        if ($this->_isSubmitted) {
            if ($this->_required[$field->getId()] && !$field->isSubmitted()) {
                return self::$_errorStart.self::$_msgRequired.self::$_errorEnd;
            } else if ($field->isSubmitted() && !$field->isValid()) {
                if ($field->getError() != null) {
                    $error = $field->getError();
                } else {
                    $error = self::$_msgInvalid;
                }
                return self::$_errorStart.$error.self::$_errorEnd;
            }
        }
        return null;
    }
    
    /**
     * Get field group
     * @param $field Field field
     * @return string|array field group
     */
    public function getGroup(Field $field)
    {
        if (isset($this->_group[$field->getId()])) {
           return $this->_group[$field->getId()];
        }
        return null;
    }
    
    /**
     * Set action
     * @param $action string action
     */
    public function setAction($action)
    {
        $this->_action = $action;
    }
    
    /**
     * Set callback
     * @param $callback string callback
     */
    public function setCallback($callback)
    {
        $this->_callback = $callback;
    }
    
    /**
     * Set renderer
     * @param $renderer FormRenderer form renderer
     */
    public function setRenderer(FormRenderer $renderer)
    {
        $this->_renderer = $renderer;
    }
    
    /**
     * Set error
     * @param $error string error
     */
    public function setError($error) 
    {
        $this->_error = $error;
    }
    
    /**
     * Add attributes
     */
    public function addAttributes($attributes)
    {
        if ($this->_attributes == null) {
            $this->_attributes = $attributes;
        } else {
            $this->_attributes .= ' '.$attributes;
        }
    }
    
    /**
     * Add an field
     * @param $field Field field to add
     * @param $required bool required field ?
     * @param $group string|array field group
     * @return Field field added
     */
    public function add(Field $field,$required=true,$group=null)
    {
        // File input ?
        if ($field instanceof File) {
            $this->_file = true;
        }
        
        // Add field, required and group to arrays
        $this->_fields[$field->getId()] = $field;
        $this->_required[$field->getId()] = $required;
        if (is_null($group) || is_array($group)) {
            $this->_group[$field->getId()] = $group;
        } else {
            $this->_group[$field->getId()] = array($group);
        }
        
        // Return field
        return $field;
    }
    
    /**
     * Add HTML content
     * @param $html string HTML content
     * @param $group string|array field group
     */
    public function addHTML($html,$group=null)
    {
        $this->add(new Html($html), false, $group);
    }
    
    /**
     * Know if an field is required
     * @param $field Field field
     * @return bool field is required ?
     */
    public function isRequired(Field $field)
    {
        return isset($this->_group[$field->getId()]);
    }
    
    /**
     * Know if form has been submitted
     * @return bool submitted form ?
     */
    public function isSubmitted()
    {
        return $this->_isSubmitted;
    }
    
    /**
     * Know if form is valid 
     * @return bool valid form ?
     */
    public function isValid()
    {
        return $this->_isvalid;
    }
    
    /**
     * Validate form
     * @param $display bool display form if necessary ?
     * @return bool true if form is valid
     */
    public function validate($display=false)
    {
        // Initialize isSubmitted variable
        $isSubmitted = false;
        
        // Validate each button
        $nbButtons = 0;
        foreach ($this->_fields as $key => $field) {
            if ($field instanceof Submit) {
                $field->validate($this->_method);
                $nbButtons++;
            }
        }
        
        // Check if there is buttons
        if ($nbButtons != 0) {
            // Check if a button has been submitted
            foreach ($this->_fields as $field) {
                if (!$isSubmitted) {
                    $isSubmitted = $field instanceof Submit &&
                                   $field->isValid();
                }
            }
        } else {
            $isSubmitted = true;
        }
        
        // Set submitted attribute
        $this->_isSubmitted = $isSubmitted;
        
        // Initialze isValid variable
        $isValid = $isSubmitted;
                
        // Check if all fields are submitted and valid
        if ($isSubmitted) {
            // Validate each fields
            foreach ($this->_fields as $field) {
                if (!($field instanceof Submit)) {
                    $field->validate($this->_method);
                }
            }
            
            // Check if all fields are valid
            foreach ($this->_fields as $field) {
                if ($isValid) {
                    $isValid = $field instanceof Submit ||
                               $field->isSubmitted() && $field->isValid() ||
                               !$field->isSubmitted() &&
                               !$this->_required[$field->getId()];
                }
            }
            
            // Valid callback if necessary
            if ($this->_callback != null && $isValid) {
                $isValid = call_user_func_array(
                    $this->_callback,
                    array_merge($this->_fields, array($this))
                );
            }
        }
        
        // Set valid attribute
        $this->_isValid = $isValid;
        
        // Display form if necessary
        if ($display && !$isValid) {
            echo $this;
        }
        
        // Return bool
        return $isValid;
    }

    /**
     * Get an field by name
     * @param $name string name
     * @return Field field
     */
    public function __get($name)
    {
        foreach ($this->_fields as $field) {
            if ($field->getName() == $name) {
                return $field;
            }
        }
        return null;
    }
    
    /**
     * To string
     * @return string string form in HTML
     */
    public function __toString()
    {
        // Render form
        return $this->_renderer->renderForm($this);
    }
    
    /**
     * Set error messages start
     * @param $errorStart string error messages start
     */
    public static function setErrorStart($errorStart)
    {
        self::$_errorStart = $errorStart;
    }
    
    /**
     * Set error messages end
     * @param $errorEnd string error messages end
     */
    public static function setErrorEnd($errorEnd)
    {
        self::$_errorEnd = $errorEnd;
    }
        
    /**
     * Set message required
     * @param $msg string message displayed when a field is not submitted
     */
    public static function setMsgRequired($msg)
    {
        self::$_msgRequired = $msg;
    }
    
    /**
     * Set message required
     * @param $msg string message displayed when a field is not valid
     */
    public static function setMsgInvalid($msg)
    {
        self::$_msgInvalid = $msg;
    }
    
}