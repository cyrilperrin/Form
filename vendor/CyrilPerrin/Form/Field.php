<?php

namespace CyrilPerrin\Form;

/**
 * Class to extend to be considered as a field
 */
abstract class Field
{
    /** @var int id counter */
    private static $_counter = 0;
    
    /** @var $_id int id */
    private $_id;
    
    /** @var $_name string name */
    protected $_name;
    
    /** @var $_description string description */
    protected $_description;
    
    /** @var $_error string error message */
    private $_error = null;
    
    /** @var $_attributes string HTML attributes */
    protected $_attributes;
    
    /** @var $_before string before */
    protected $_before = '';
    
    /** @var $_after string after */
    protected $_after = '';
    
    /** @var $_isSubmitted bool submitted element ? */
    protected $_isSubmitted = false;
    
    /** @var $_isValid bool valid element ? */
    protected $_isValid = false;
    
    /** @var $_callback string callback */
    private $_callback = null;
    
    /** @var $_parameters array parameters */
    private $_parameters = array();
    
    /**
     * Constructor
     * @param $name string name
     * @param $description string description
     * @param $attributes string initial HTML attributes
     */
    public function __construct($name,$description=null,$attributes=null)
    {
        // Set attributes
        $this->_id = self::$_counter++;
        $this->_name = $name;
        $this->_description = $description;
        $this->_attributes = $attributes;
    }
    
    /**
     * Get id
     * return int id
     */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * Get description
     * @return string description
     */
    public function getDescription()
    {
        return $this->_description;
    }
    
    /**
     * Know if element is submitted
     * @return bool submitted element ?
     */
    public function isSubmitted()
    {
        return $this->_isValid || $this->_isSubmitted;
    }
    
    /**
     * Get error message
     * @return string error message
     */
    public function getError()
    {
        return $this->_error;
    }
    
    /**
     * Get element name
     * @return string name
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Set error message
     * @param string $error error message
     */
    public function setError($error) 
    {
        $this->_isSubmitted = true;
        $this->_isValid = false;
        $this->_error = $error;
    }
    
    /**
     * Know if element is valid
     * @return bool valid element ?
     */
    public function isValid()
    {
        return $this->_isValid = $this->_isValid && $this->checkCallback();
    }
    
    /**
     * Set callback to valid value
     * @param $callback string callback
     * @param $parameters array parameters
     */
    public function setCallback($callback,$parameters=array())
    {
        $this->_callback = $callback;
        $this->_parameters = $parameters;
    }
    
    /**
     * Call callback to valid value
     * @return bool value valided by callback ?
     */
    private function checkCallback()
    {
        if ($this->_callback != null) {
            if (!call_user_func_array(
                $this->_callback, array_merge(array($this), $this->_parameters)
            )) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Validate element
     * @param string method used by form
     */
    abstract public function validate($method);
    
    /**
     * Add attributes HTML attributes
     * @param $attributes string HTML attributes
     */
    public function addAttributes($attributes)
    {
        if ($this->_attributes == null) {
            $this->_attributes = '';
        } else {
            $this->_attributes .= ' ';
        }
        $this->_attributes .= $attributes;
    }
    
    /**
     * Set enclose
     */
    public function setEnclose($before,$after='')
    {
        $this->_before = $before;
        $this->_after = $after;
    }
    
    /**
     * To string
     */
    abstract public function __toString();
}