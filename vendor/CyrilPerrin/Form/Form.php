<?php

namespace CyrilPerrin\Form;

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
    
    /** @var $_elements array elements */
    private $_elements = array();
    
    /** @var $_required array required elements */
    private $_required = array();
    
    /** @var $_group array group elements */
    private $_group = array();
    
    /** @var $_file bool file input in elements ? */
    private $_file = false;
    
    /** @var $_isSubmitted bool submitted form ? */
    private $_isSubmitted = false;
    
    /** @var $_isValid bool valid form ? */
    private $_isValid = false;
    
    /** @var $_attributes string attributes */
    private $_attributes = null;
    
    /** @var $_callback string callback to valid all elements */
    private $_callback = null;
    
    /** @var $_renderer IFormRenderer form renderer */
    private $_renderer = null;
    
    /** @var $_error string error message */
    private $_error = null;
    
    /** @var $_errorStart string error messages start */
    private static $_errorStart = '<span style="color:red;">';
    
    /** @var $_errorEnd string error messages end */
    private static $_errorEnd = '</span>';
    
    /** @var $_msgRequired string message displayed when a element is not submitted */
    private static $_msgRequired = 'Required';
    
    /** @var $_msgInvalid string message displayed when a element is not valid */
    private static $_msgInvalid = 'Invalid';
    
    /**
     * Constructor
     * @param $method string method
     * @param $action string action
     * @param $callback string callback to valid element
     * @param $renderer IFormRenderer form renderer
     */
    public function __construct($method=Form::METHOD_POST,$action='#',
        $callback=null,IFormRenderer $renderer=null)
    {
        // Set attributes
        $this->_method = $method;
        $this->_action = $action;
        $this->_callback = $callback;
        if ($renderer === null) {
            $this->_renderer = new FormRenderer_Table();
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
     * Return elements
     * @return array elements
     */
    public function getElements()
    {
        return $this->_elements;
    }
    
    /**
     * Get error for an element
     * @param $element Form|Field element
     * @return string error
     */
    public function getError($element)
    {
        if ($element instanceof Form) {
            if ($this->_error != null) {
                return self::$_errorStart.$this->_error.self::$_errorEnd;
            }
            return null;
        }
        if ($this->_isSubmitted) {
            if ($this->_required[$element->getId()] && !$element->isSubmitted()) {
                return self::$_errorStart.self::$_msgRequired.self::$_errorEnd;
            } else if ($element->isSubmitted() && !$element->isValid()) {
                if ($element->getError() != null) {
                    $error = $element->getError();
                } else {
                    $error = self::$_msgInvalid;
                }
                return self::$_errorStart.$error.self::$_errorEnd;
            }
        }
        return null;
    }
    
    /**
     * Get element group
     * @param $element Field element
     * @return string|array element group
     */
    public function getGroup(Field $element)
    {
        if (isset($this->_group[$element->getId()])) {
           return $this->_group[$element->getId()];
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
     * @param $renderer IFormRenderer form renderer
     */
    public function setRenderer(IFormRenderer $renderer)
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
     * Add an element
     * @param $element Field element to add
     * @param $required bool required element ?
     * @param $group string|array element group
     * @return Field element added
     */
    public function add(Field $element,$required=true,$group=null)
    {
        // File input ?
        if ($element instanceof Field_File) {
            $this->_file = true;
        }
        
        // Add element, required and group to arrays
        $this->_elements[$element->getId()] = $element;
        $this->_required[$element->getId()] = $required;
        if (is_null($group) || is_array($group)) {
            $this->_group[$element->getId()] = $group;
        } else {
            $this->_group[$element->getId()] = array($group);
        }
        
        // Return element
        return $element;
    }
    
    /**
     * Add HTML content
     * @param $html string HTML content
     * @param $group string|array element group
     */
    public function addHTML($html,$group=null)
    {
        $this->add(new Field_Html($html), false, $group);
    }
    
    /**
     * Know if an element is required
     * @param $element Field element
     * @return bool element is required ?
     */
    public function isRequired(Field $element)
    {
        return isset($this->_group[$element->getId()]);
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
        foreach ($this->_elements as $key => $element) {
            if ($element instanceof Field_Submit) {
                $element->validate($this->_method);
                $nbButtons++;
            }
        }
        
        // Check if there is buttons
        if ($nbButtons != 0) {
            // Check if a button has been submitted
            foreach ($this->_elements as $element) {
                if (!$isSubmitted) {
                    $isSubmitted = $element instanceof Field_Submit &&
                                   $element->isValid();
                }
            }
        } else {
            $isSubmitted = true;
        }
        
        // Set submitted attribute
        $this->_isSubmitted = $isSubmitted;
        
        // Initialze isValid variable
        $isValid = $isSubmitted;
                
        // Check if all elements are submitted and valid
        if ($isSubmitted) {
            // Validate each elements
            foreach ($this->_elements as $element) {
                if (!($element instanceof Field_Submit)) {
                    $element->validate($this->_method);
                }
            }
            
            // Check if all elements are valid
            foreach ($this->_elements as $element) {
                if ($isValid) {
                    $isValid = $element instanceof Field_Submit ||
                               $element->isSubmitted() && $element->isValid() ||
                               !$element->isSubmitted() &&
                               !$this->_required[$element->getId()];
                }
            }
            
            // Valid callback if necessary
            if ($this->_callback != null && $isValid) {
                $isValid = call_user_func_array(
                    $this->_callback,
                    array_merge($this->_elements, array($this))
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
     * Get an element by name
     * @param $name string name
     * @return Field element
     */
    public function __get($name)
    {
        foreach ($this->_elements as $element) {
            if ($element->getName() == $name) {
                return $element;
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
     * @param $msg string message displayed when a element is not submitted
     */
    public static function setMsgRequired($msg)
    {
        self::$_msgRequired = $msg;
    }
    
    /**
     * Set message required
     * @param $msg string message displayed when a element is not valid
     */
    public static function setMsgInvalid($msg)
    {
        self::$_msgInvalid = $msg;
    }
    
}