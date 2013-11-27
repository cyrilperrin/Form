<?php

namespace CyrilPerrin\Form;

/**
 * Form file
 */
class Field_File extends Field
{
    /** @var $_path string path */
    protected $_path = null;
    
    /** @var $_inputSize int size of file input */
    private $_inputSize;
    
    /** @var $_maxFileSize int maximal size of file (in bytes) */
    private $_maxFileSize;
    
    /** @var $_acceptedExtensions array valid extensions */
    private $_acceptedExtensions;
    
    /** @var $_acceptedMimetypes array valid mime types */
    private $_acceptedMimetypes;
    
    /** @var $_errorCode int error code */
    protected $_errorCode = null;
    
    /** @var $_isSaved bool saved ? */
    protected $_isSaved = false;
    
    // Error codes
    const ERROR_MISSING     = 1;
    const ERROR_EXTENSION   = 2;
    const ERROR_MIMETYPE    = 3;
    const ERROR_SIZE        = 4;
        
    /**
     * Constructor
     * @param $name string name
     * @param $description string description
     * @param $inputSize int size of file input
     * @param $maxFileSize int maximal size of file in bytes
     * @param $acceptedExtensions array valid extensions
     * @param $acceptedMimetypes array valid mime types
     * @param $attributes string initial HTML attributes
     */
    public function __construct($name,$description=null,$inputSize=null,
        $maxFileSize=null,$acceptedExtensions=null,$acceptedMimetypes=null,
        $attributes=null)
    {
        // Call parent constructor
        parent::__construct($name, $description, $attributes);
        
        // Set attributes
        $this->_inputSize = $inputSize;
        $this->_maxFileSize = $maxFileSize;
        $this->_acceptedExtensions = $acceptedExtensions;
        $this->_acceptedMimetypes = $acceptedMimetypes;
    }
    
    /**
     * Get file name
     * @return string file name
     */
    public function getFileName()
    {
        if ($this->_isSubmitted) {
            if ($this->_isSaved) {
                return basename($this->_path);
            } else {
                return $_FILES[$this->_name]['name'];
            }
        }
        return null;
    }
    
    /**
     * Get mime type
     * @return string mime type
     */
    public function getMimeType()
    {
        if ($this->_isSubmitted && function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            return strtolower(finfo_file($finfo, $this->_path));
        }
        return null;
    }
    
    /**
     * Get extension
     * @return string extension
     */
    public function getExtension()
    {
        if ($this->_isSubmitted) {
            return strtolower(
                pathinfo($_FILES[$this->_name]['name'], PATHINFO_EXTENSION)
            );
        }
        return null;
    }
    
    /**
     * Get file size
     * @return int size in bytes
     */
    public function getSize()
    {
        return $this->_isSubmitted ? $_FILES[$this->_name]['size'] : null;
    }
    
    /**
     * Get error code
     * @return int error code
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }
    
    /**
     * Get file path
     * @return string file path
     */
    public function getPath()
    {
        return $this->_path;
    }
    
    /**
     * Move file
     * @param $path string destination
     * @param $name string name without extension 
     * @param $overwrite bool overwrite if a file exists
     * @return bool|string file path, false if unsuccessful operation
     */
    public function moveTo($path,$name=null,$overwrite=false)
    {
        // Complete path
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        if ($name != null) {
            $path .= $name.'.'.$this->getExtension();
        } else {
            $path .= $this->getFileName();
        }
        
        // Check if a file exists
        if (!$overwrite && file_exists($path)) {
            return false;
        }
        
        // Move file
        if ($this->_isSaved) {
            if (!rename($this->_path, $path)) {
                return false;
            }
        } else {
            if (!move_uploaded_file($this->_path, $path)) {
                return false;
            }
        }
        
        // Updated attributes
        $this->_path = $path;
        $this->_isSaved = true;
        
        // Return file path
        return $path;
    }
    
    /**
     * Copy file
     * @param $path string destination
     * @param $name string name, without extension 
     * @param $overwrite bool overwrite if a file exists
     * @return bool|string file path, false if unsuccessful operation
     */
    public function copyTo($path,$name=null,$overwrite=false)
    {
        // Complete path
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        if ($name != null) {
            $path .= $name.'.'.$this->getExtension();
        } else {
            $path .= $this->getFileName();
        }
        
        // Check if a file exists
        if (!$overwrite && file_exists($path)) {
            return false;
        }
        
        // Copy file
        if (!copy($this->_path, $path)) {
            return false;
        }
        
        // Return file path
        return $path;
    }
    
    /**
     * @see Field::validate($method)
     */
    public function validate($method)
    {
        // Is submitted ?
        if (empty($_FILES[$this->_name]['name']) ||
        $_FILES[$this->_name]['error'] != UPLOAD_ERR_OK) {
            $this->_errorCode = self::ERROR_MISSING;
            return false;
        }
        
        // isSubmitted to true
        $this->_isSubmitted = true;
        
        // Path
        $this->_path = $_FILES[$this->_name]['tmp_name'];
        
        // Valid extension ?
        if ($this->_acceptedExtensions != null &&
        !in_array($this->getExtension(), $this->_acceptedExtensions)) {
            $this->_errorCode = self::ERROR_EXTENSION;
            return false;
        }
        
        // Valid mimetype ?
        if ($this->_acceptedMimetypes != null && $this->getMimeType() != null &&
        !in_array($this->getMimeType(), $this->_acceptedMimetypes)) {
            $this->_errorCode = self::ERROR_MIMETYPE;
            return false;
        }
        
        // Valid size ?
        if ($this->_maxFileSize != null &&
        $this->getSize() > $this->_maxFileSize) {
            $this->_errorCode = self::ERROR_SIZE;
            return false;
        }
        
        // IsValid to true
        $this->_isValid = true;
    }
    
    /**
     * Set input size
     * @param $inputSize int size of file input
     */
    public function setInputSize($inputSize)
    {
        $this->_inputSize = $inputSize;
    }
    
    /**
     * Set max file size
     * @param $maxFileSize int maximal size of file in bytes
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->_maxFileSize = $maxFileSize;
    }
    
    /**
     * Set accepted extensions
     * @param $acceptedExtensions array valid extensions
     */
    public function setAcceptedExtensions($acceptedExtensions)
    {
        $this->_acceptedExtensions = $acceptedExtensions;
    }
    
    /**
     * Set accepted mimetypes
     * @param $acceptedMimetypes array valid mime types
     */
    public function setAcceptedMimetypes($acceptedMimetypes)
    {
        $this->_acceptedMimetypes = $acceptedMimetypes;
    }
    
    /**
     * @see Field::__toString()
     */
    public function __toString()
    {
        return $this->_before.'<input type="file" name="'.$this->_name.'" '.
               ($this->_inputSize != null ? $this->_inputSize.' ' : '').
               ($this->_attributes != null ? $this->_attributes.' ' : '').
               '/>'.$this->_after;
    }
}