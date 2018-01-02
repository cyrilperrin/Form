<?php

namespace CyrilPerrin\Form\Field;

use CyrilPerrin\Form\Field;

/**
 * Form HTML
 */
class Html extends Field
{
    /** @var $_html string html */
    private $_html;
    
    /**
     * Constructor
     * @param $html string html
     */
    public function __construct($html)
    {
        // Call parent constructor
        parent::__construct(null, null, null);
        
        // Save HTML
        $this->_html = $html;
    }
    
    /**
     * @see Field::validate($method)
     */
    public function validate($method)
    {
        $this->_isSubmitted = true;
        $this->_isValid = true;
    }
    
    /**
     * @see Field::__toString()
     */
    public function __toString()
    {
        return $this->_html;
    }
    
}