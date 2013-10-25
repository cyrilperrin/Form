<?php

namespace CyrilPerrin\Form;

/**
 * Form image
 */
class Field_File_Image extends Field_File
{
    /** @var $_width int width */
    private $_width;
    
    /** @var $_height int height */
    private $_height;
    
    /** @var $_type string type */
    private $_type;
    
    /** @var $_maxWidth int maximal width */
    private $_maxWidth;
    
    /** @var $_maxHeight int maximal height */
    private $_maxHeight;
    
    /** @var $_maxResize bool resize image if dimensions exceed the maximum */
    private $_maxResize;
    
    /** @var $_minWidth int minimal width */
    private $_minWidth;
    
    /** @var $_minHeight int minimal height */
    private $_minHeight;
    
    /** @var $_minResize bool resize image if dimensions exceed the minimum */
    private $_minResize;
    
    // Error codes
    const ERROR_INVALID        = 5;
    const ERROR_DIMENSIONS     = 6;
    
    /**
     * Constructor
     * @param $name string name
     * @param $description string description
     * @param $inputSize int size of file input
     * @param $fileSize int maximal size of file in bytes
     * @param $acceptedExtensions array valid extensions
     * @param $acceptedMimetypes array valid mime types
     * @param $maxWidth int maximal width
     * @param $maxHeight int maximal height
     * @param $maxResize bool resize image if dimensions exceed the maximum
     * @param $minWidth int minimal width
     * @param $minHeight int minimal width
     * @param $minResize bool resize image if dimensions exceed the minimum
     * @param $attributes string initial HTML attributes
     */
    public function __construct($name,$description=null,$inputSize=null,
        $fileSize=null,$acceptedExtensions=null,$acceptedMimetypes=null,
        $maxWidth=null, $maxHeight=null,$maxResize=true,$minWidth=null,
        $minHeight=null, $minResize=false,$attributes=null)
    {
        // Call parent constructor
        parent::__construct(
            $name, $description, $inputSize, $fileSize, $acceptedExtensions,
            $acceptedMimetypes, $attributes
        );
        
        // Set attributes
        $this->_maxWidth = $maxWidth;
        $this->_maxHeight = $maxHeight;
        $this->_maxResize = $maxResize;
        $this->_minWidth = $minWidth;
        $this->_minHeight = $minHeight;
        $this->_minResize = $minResize;
    }
    
    /**
     * Get width
     * @return int width
     */
    public function getWidth()
    {
        return $this->_width;
    }
    
    /**
     * Get height
     * @return int height
     */
    public function getHeight()
    {
        return $this->_height;
    }
    
    /**
     * @see Field_File::validate($method)
     */
    public function validate($method)
    {
        // Call parent validate method
        parent::validate($method);
        if (!$this->isValid()) {
            return false;
        }
        
        // Get image infos
        $infos = getimagesize($this->_path);
        if (!$infos) {
            $this->_errorCode = self::ERROR_INVALID;
            $this->_isValid = false;
            return false;
        }
        
        // Set width, height and type
        list($this->_width,$this->_height,$this->_type) = $infos;
        
        // Exceed the maximum dimensions ?
        if ($this->_maxWidth != null && $this->_width > $this->_maxWidth ||
        $this->_maxHeight != null && $this->_height > $this->_maxHeight) {
            if (!$this->_maxResize) {
                $this->_errorCode = self::ERROR_DIMENSIONS;
                $this->_isValid = false;
                return false;
            } elseif ($this->_minWidth != null || $this->_minHeight != null) {
                // Ratio
                $ratio = 0;
                if ($this->_maxWidth == null) {
                    $ratio = $this->_maxHeight/$this->_height;
                } else if ($this->_minHeight == null) {
                    $ratio = $this->_maxWidth/$this->_width;
                } else {
                    $ratio = min(
                        $this->_maxWidth/$this->_width,
                        $this->_maxHeight/$this->_height
                    );
                }

                // Check if new dimensions don't exceed minimum
                if ($this->_minWidth != null &&
                $this->_width*$ratio < $this->_minWidth ||
                $this->_minHeight != null &&
                $this->_height*$ratio < $this->_minHeight) {
                    $this->_errorCode = self::ERROR_DIMENSIONS;
                    $this->_isValid = false;
                    return false;
                }
            }
        }
        
        // Exceed the minimum dimensions ?
        if ($this->_minWidth != null && $this->_width < $this->_minWidth ||
        $this->_minHeight != null && $this->_height < $this->_minHeight) {
            if (!$this->_minResize) {
                $this->_errorCode = self::ERROR_DIMENSIONS;
                $this->_isValid = false;
                return false;
            } elseif ($this->_maxWidth != null || $this->_maxHeight != null) {
                // Ratio
                $ratio = 0;
                if ($this->_minWidth == null) {
                    $ratio = $this->_minHeight/$this->_height;
                } else if ($this->_minHeight == null) {
                    $ratio = $this->_minWidth/$this->_width;
                } else {
                    $ratio = max(
                        $this->_minWidth/$this->_width,
                        $this->_minHeight/$this->_height
                    );
                }

                // Check if new dimensions don't exceed maximum
                if ($this->_minWidth != null &&
                $this->_width*$ratio < $this->_minWidth ||
                $this->_minHeight != null &&
                $this->_height*$ratio < $this->_minHeight) {
                    $this->_errorCode = self::ERROR_DIMENSIONS;
                    $this->_isValid = false;
                    return false;
                }
            }
        }
        
        // IsValid to true
        $this->_isValid = true;
    }
    
    /**
     * MoveTo surcharge
     * @param $path string destination, ending by /
     * @param $name string name, without extension 
     * @param $overwrite bool overwrite if a file exists
     * @return bool|string file path, false if unsuccessful
     */
    public function moveTo($path,$name=null,$overwrite=false)
    {
        // Call parent moveTo method
        $path = parent::moveTo($path, $name, $overwrite);
        if (!$path) {
            return false;
        }
        
        // Resize
        $infos = self::resize(
            $path,
            $this->_maxWidth, $this->_maxHeight,
            $this->_minWidth, $this->_minHeight
        );
        if (!$infos) {
            unlink($this->_path);
            $this->_path = null;
            return false;
        }
        list($this->_width, $this->_height) = $infos;
        
        // Return path
        return $path;
    }
    
    /**
     * CopyTo surcharge
     * @param $path string destination, ending by /
     * @param $name string name, without extension 
     * @param $overwrite bool overwrite if a file exists
     * @param $maxWidth int width
     * @param $maxHeight int height
     * @param $minWidth int minimal width to don't exceed
     * @param $minHeight int maximal or minimal height to don't exceed
     * @return bool|array array like array($path,$width,$height),
     *                    false if unsuccessful 
     */
    public function copyTo($path,$name=null,$overwrite=false,$maxWidth=null,
        $maxHeight=null,$minWidth=null,$minHeight=null)
    {
        // Call parent copyTo method
        $path = parent::copyTo($path, $name, $overwrite);
        if (!$path) {
            return false;
        }
        
        // Resize
        $infos = self::resize(
            $path,
            $maxWidth, $maxHeight,
            $minWidth, $minHeight
        );
        if (!$infos) {
            return false;
        }
        list($width, $height) = $infos;
        
        // Return path and dimensions
        return array($path,$width,$height);
    }
    
    /**
     * Resize image
     * @param $path string path
     * @param $maxWidth int width
     * @param $maxHeight int height
     * @param $minWidth int minimal width to don't exceed
     * @param $minHeight int maximal or minimal height to don't exceed
     * @return bool|array array like array($width,$height),
     *                    false if unsuccessful 
     */
    public static function resize($path,$maxWidth=null,$maxHeight=null,
        $minWidth=null,$minHeight=null)
    {
        // Get infos
        $infos = getimagesize($path);
        if (!$infos) {
            return false;
        }
        list($oldWidth, $oldHeight, $type) = $infos;
        
        // Ratio
        if ($maxWidth != null && $oldWidth > $maxWidth ||
        $maxHeight != null && $oldHeight > $maxHeight) {
            // Ratio
            $ratio = 0;
            if ($maxWidth == null) {
                $ratio = $maxHeight/$oldHeight;
            } else if ($maxHeight == null) {
                $ratio = $maxWidth/$oldWidth;
            } else {
                $ratio = min($maxWidth/$oldWidth, $maxHeight/$oldHeight);
            }
            
            // Check if new dimensions don't exceed minimum
            if ($minWidth != null && $oldWidth*$ratio < $minWidth ||
            $minHeight != null && $oldHeight*$ratio < $minHeight) {
                return false;
            }
        } elseif ($minWidth != null && $oldWidth < $minWidth ||
            $minHeight != null && $oldHeight < $minHeight) {
            // Ratio
            $ratio = 0;
            if ($minWidth == null) {
                $ratio = $minHeight/$oldHeight;
            } else if ($minHeight == null) {
                $ratio = $minWidth/$oldWidth;
            } else {
                $ratio = max($minWidth/$oldWidth, $minHeight/$oldHeight);
            }
            
            // Check if new dimensions don't exceed maximum
            if ($maxWidth != null && $oldWidth*$ratio > $maxWidth ||
            $maxHeight != null && $oldHeight*$ratio > $maxHeight) {
                return false;
            }
        } else {
            return array($oldWidth, $oldHeight);
        }
        
        // New with and height
        $newWidth = $oldWidth*$ratio;
        $newHeight = $oldHeight*$ratio;
        
        // Load image
        switch($type) {
            case IMAGETYPE_JPEG :
                $old = imagecreatefromjpeg($path);
                if (!$old) {
                    return false;
                }
                break;
            case IMAGETYPE_GIF :
                $old = imagecreatefromgif($path);
                if (!$old) {
                    return false;
                }
                break;
            case IMAGETYPE_PNG :
                $old = imagecreatefrompng($path);
                if (!$old) {
                    return false;
                }
                break;
            default :
                return false;
        }
        
        // Resize
        $new = imagecreatetruecolor($newWidth, $newHeight);
        if (!$new) {
            return false;
        }
        $result = imagecopyresampled(
            $new, $old, 0, 0, 0, 0, $newWidth, $newHeight,
            $oldWidth, $oldHeight
        );
        
        // Save image
        switch($type) {
            case IMAGETYPE_JPEG :
                if (!imagejpeg($new, $path)) {
                    return false;
                }
                break;
            case IMAGETYPE_GIF :
                if (!imagegif($new, $path)) {
                    return false;
                }
                break;
            case IMAGETYPE_PNG :
                if (!imagepng($new, $path)) {
                    return false;
                }
                break;
        }
        
        // Return new dimensions
        return array($newWidth,$newHeight);
    }
    
}