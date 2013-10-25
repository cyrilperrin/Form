<?php

namespace CyrilPerrin\Form;

/**
 * Form date
 */
class Field_Date extends Field
{
    // Formats
    const FORMAT_YYYY_MM_DD = 1;
    const FORMAT_DD_MM_YYYY = 2;
    
    /** @var $_timestamp int timestamp */
    private $_timestamp;
    
    /** @var $_selectDay Field_Input_Select day */
    private $_selectDay;
    
    /** @var $_selectMonth Field_Input_Select month */
    private $_selectMonth;
    
    /** @var $_selectYear Field_Input_Select year */
    private $_selectYear;
    
    /** @var $_min int minimum timestamp */
    private $_min;
    
    /** @var $_max int maximum timestamp */
    private $_max;
    
    /** @var $_format int used format */
    private $_format;
    
    /**
     * Constructor
     * @param $name string name
     * @param $description string description
     * @param $init int initial timestamp
     * @param $min int minimum timestamp
     * @param $max int maximum timestamp
     * @param format int used format
     * @param $attributes string initial HTML attributes
     */
    public function __construct($name,$description=null,$init=null,$min=null,
        $max=null,$format=Field_Date::FORMAT_YYYY_MM_DD,$attributes=null)
    {
        // Call parent constructor
        parent::__construct($name, $description, $attributes, $init);
        
        // Save attributes
        $this->_min = $min;
        $this->_max = $max;
        $this->_format = $format;
        
        // Year from and to
        $yearFrom = $min === null ? 1930 : date('Y', $min);
        $yearTo = $max === null ? date('Y')+2 : date('Y', $max);
        
        // Initial year, month and day
        if ($init !== null) {
            $initYear = date('Y', $init);
            $initMonth = date('n', $init);
            $initDay = date('j', $init);
        } else {
            $initYear = null;
            $initMonth = null;
            $initDay = null;
        }
        
        // Create year select
        $years = array('----');
        for ($i=$yearFrom;$i<=$yearTo;++$i) {
            $years[$i] = $i; 
        }
        $this->_selectYear = new Field_Input_Select(
            $name.'_year', $years, null, false, true, null, $initYear
        );
        
        // Create month select
        $months = array('--');
        for ($i=1;$i<=12;++$i) {
            $months[$i] = str_pad($i, 2, 0, STR_PAD_LEFT); 
        }
        $this->_selectMonth = new Field_Input_Select(
            $name.'_month', $months, null, false, true, null, $initMonth
        );
        
        // Create day select
        $days = array('--');
        for ($i=1;$i<=31;++$i) {
            $days[$i] = str_pad($i, 2, 0, STR_PAD_LEFT); 
        }
        $this->_selectDay = new Field_Input_Select(
            $name.'_day', $days, null, false, true, null, $initDay
        );
    }
    
    /**
     * @see Field::validate($method)
     */
    public function validate($method)
    {    
        // Validate selects
        $this->_selectDay->validate($method);
        $this->_selectMonth->validate($method);
        $this->_selectYear->validate($method);
        
        // Is submitted ?
        $this->_isSubmitted = $this->_selectDay->isSubmitted() &&
                             $this->_selectDay->getValue() != 0 &&
                             $this->_selectMonth->isSubmitted() &&
                             $this->_selectMonth->getValue() != 0 &&
                             $this->_selectYear->isSubmitted() &&
                             $this->_selectYear->getValue() != 0;
        if (!$this->_isSubmitted) {
            return false;
        }
        
        // Is valid ?
        $this->_isValid =  $this->_selectDay->isValid() &&
                          $this->_selectMonth->isValid() &&
                          $this->_selectYear->isValid();
        if (!$this->_isValid) {
            return false;
        }

        // Check if date is valid
        $this->_isValid = checkdate(
            $this->_selectMonth->getValue(),
            $this->_selectDay->getValue(),
            $this->_selectYear->getValue()
        );
        if (!$this->_isValid) {
            return false;
        }
        
        // Make timestamp
        $this->_timestamp = mktime(
            0, 0, 0, $this->_selectMonth->getValue(),
            $this->_selectDay->getValue(), $this->_selectYear->getValue()
        );
        
        // Check if periode does not exceed min and max timestamps
        if ($this->_max !== null) {
            $this->_isValid = $this->_max >= $this->_timestamp;
            if (!$this->_isValid) {
                return false;
            }
        }
        if ($this->_min !== null) {
            $this->_isValid =  $this->_timestamp >= $this->_max;
            if (!$this->_isValid) {
                return false;
            }
        }
    }
    
    /**
     * Get timestamp
     * @return int timestamp
     */
    public function getTimestamp()
    {
        return $this->_timestamp;
    }
    
    /**
     * @see Field::__toString()
     */
    public function __toString()
    {
        if ($this->_format == self::FORMAT_DD_MM_YYYY) {
            return $this->_before.
                   $this->_selectDay->__toString().' / '.
                   $this->_selectMonth->__toString().' / '.
                   $this->_selectYear->__toString().
                   $this->_after;
        } else {
            return $this->_before.
                   $this->_selectYear->__toString().' / '.
                   $this->_selectMonth->__toString().' / '.
                   $this->_selectDay->__toString().
                   $this->_after;
        }
    }
}