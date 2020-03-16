<?php   namespace Johntaa\I18N_Arabic;
/**
 * ----------------------------------------------------------------------
 *  
 * Copyright (c) 2006-2013 Khaled Al-Shamaa.
 *  
 * http://www.ar-php.org
 *  
 * PHP Version 5 
 *  
 * ----------------------------------------------------------------------
 *  
 * LICENSE
 *
 * This program is open source product; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public License (LGPL)
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *  
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *  
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 *  
 * ----------------------------------------------------------------------
 *  
 * Class Name: PHP and Arabic Language
 *  
 * Filename:   Arabic.php
 *  
 * Original    Author(s): Khaled Al-Sham'aa <khaled@ar-php.org>
 *  
 * Purpose:    Set of PHP classes developed to enhance Arabic web 
 *             applications by providing set of tools includes stem-based searching, 
 *             translitiration, soundex, Hijri calendar, charset detection and
 *             converter, spell numbers, keyboard language, Muslim prayer time, 
 *             auto-summarization, and more...
 *              
 * ----------------------------------------------------------------------
 *
 * @desc   Set of PHP classes developed to enhance Arabic web
 *         applications by providing set of tools includes stem-based searching, 
 *         translitiration, soundex, Hijri calendar, charset detection and
 *         converter, spell numbers, keyboard language, Muslim prayer time, 
 *         auto-summarization, and more...
 *          
 * @category  I18N 
 * @package   I18N_Arabic
 * @author    Khaled Al-Shamaa <khaled@ar-php.org>
 * @copyright 2006-2013 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @version   3.6.0 released in Jan 20, 2013
 * @link      http://www.ar-php.org
 */

// New in PHP V5.3: Namespaces
// namespace I18N\Arabic;

// error_reporting(E_STRICT);

/**
 * Core PHP and Arabic language class
 *  
 * @category  I18N 
 * @package   I18N_Arabic
 * @author    Khaled Al-Shamaa <khaled@ar-php.org>
 * @copyright 2006-2013 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @link      http://www.ar-php.org
 */  
class I18N_Arabic
{
    private $_inputCharset  = 'utf-8';
    private $_outputCharset = 'utf-8';
    private $_useAutoload;
    private $_useException;
    private $_compatibleMode;
    
    private $_compatible = array('EnTransliteration'=>'Transliteration', 
                                  'ArTransliteration'=>'Transliteration',
                                  'ArAutoSummarize'=>'AutoSummarize',
                                  'ArCharsetC'=>'CharsetC',
                                  'ArCharsetD'=>'CharsetD',
                                  'ArDate'=>'Date',
                                  'ArGender'=>'Gender',
                                  'ArGlyphs'=>'Glyphs',
                                  'ArIdentifier'=>'Identifier',
                                  'ArKeySwap'=>'KeySwap',
                                  'ArNumbers'=>'Numbers',
                                  'ArQuery'=>'Query',
                                  'ArSoundex'=>'Soundex',
                                  'ArStrToTime'=>'StrToTime',
                                  'ArWordTag'=>'WordTag',
                                  'ArCompressStr'=>'CompressStr',
                                  'ArMktime'=>'Mktime',
                                  'ArStemmer'=>'Stemmer',
                                  'ArStandard'=>'Standard',
                                  'ArNormalise'=>'Normalise',
                                  'a4_max_chars'=>'a4MaxChars',
                                  'a4_lines'=>'a4Lines',
                                  'swap_ea'=>'swapEa',
                                  'swap_ae'=>'swapAe');
    
    /**
     * @ignore
     */
    public $myObject;
    
    /**
     * @ignore
     */
    public $myClass;
    
    /**
     * @ignore
     */
    public $myFile;

    /**
     * Load selected library/Arabic class you would like to use its functionality
     *          
     * @param string  $library        [AutoSummarize|CharsetC|CharsetD|Date|Gender|
     *                                Glyphs|Identifier|KeySwap|Numbers|Query|Salat|
     *                                Soundex|StrToTime|WordTag|CompressStr|Mktime|
     *                                Transliteration|Stemmer|Standard|Normalise]
     * @param boolean $useAutoload    True to use Autoload (default is false)
     * @param boolean $useException   True to use Exception (default is false)
     * @param boolean $compatibleMode True to support old naming style before 
     *                                version 3.0 (default is true)
     *
     * @desc Load selected library/class you would like to use its functionality
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public function __construct(
        $library, $useAutoload=false, $useException=false, $compatibleMode=true
    ) {
        $this->_useAutoload    = $useAutoload;
        $this->_useException   = $useException;
        $this->_compatibleMode = $compatibleMode;
 
        /* Set internal character encoding to UTF-8 */
        mb_internal_encoding("utf-8");

        if ($this->_useAutoload) {
            // It is critical to remember that as soon as spl_autoload_register() is
            // called, __autoload() functions elsewhere in the application may fail 
            // to be called. This is safer initial call (PHP 5 >= 5.1.2):
            if (false === spl_autoload_functions()) {
                if (function_exists('__autoload')) {
                    spl_autoload_register('__autoload', false);
                }
            }
            
            spl_autoload_extensions('.php,.inc,.class');
            spl_autoload_register('I18N_Arabic::autoload', false);
        }
        
        if ($this->_useException) {
            set_error_handler('I18N_Arabic::myErrorHandler');
        }
        
        if ($library) {
            if ($this->_compatibleMode 
                && array_key_exists($library, $this->_compatible)
            ) {
                $library = $this->_compatible[$library];
            }
			$this->load($library);
        }
    }

    /**
     * Include file that include requested class
     * 
     * @param string $className Class name
     * 
     * @return null      
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */ 
    public static function autoload($className) 
    {
        include self::getClassFile($className);
    }

    /**
     * Error handler function
     * 
     * @param int    $errno   The level of the error raised
     * @param string $errstr  The error message
     * @param string $errfile The filename that the error was raised in
     * @param int    $errline The line number the error was raised at
     * 
     * @return boolean FALSE      
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */ 
    public static function myErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if ($errfile == __FILE__ 
            || file_exists(
                dirname(__FILE__).DIRECTORY_SEPARATOR.'Arabic'.
                DIRECTORY_SEPARATOR.basename($errfile)
            )
        ) {
            $msg  = '<b>Arabic Class Exception:</b> ';
            $msg .= $errstr;
            $msg .= " in <b>$errfile</b>";
            $msg .= " on line <b>$errline</b><br />";
    
            throw new ArabicException($msg, $errno);
        }
        
        // If the function returns false then the normal error handler continues
        return false;
    }

    /**
     * Load selected Arabic library and create an instance of its class
     * 
     * @param string $library Library name
     * 
     * @return null      
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */ 
    public function load($library)
    {
        if ($this->_compatibleMode 
            && array_key_exists($library, $this->_compatible)
        ) {
            
            $library = $this->_compatible[$library];
        }

        $this->myFile  = $library;
        //$this->myClass = 'I18N_Arabic_' . $library; Nasser
       // $class         = 'I18N_Arabic_' . $library; Nasser
        $this->myClass = '\Johntaa\Arabic\Arabic\I18N_Arabic_' . $library ;
        $class         = '\Johntaa\Arabic\Arabic\I18N_Arabic_' . $library;

        if (!$this->_useAutoload) {
			 
           if(!class_exists($class)) include self::getClassFile($this->myFile); 
        }
 
		  $this->myObject   = new $class();
		  $this->{$library} = &$this->myObject; 
		 
		 
    }
    
    /**
     * Magic method __call() allows to capture invocation of non existing methods. 
     * That way __call() can be used to implement user defined method handling that 
     * depends on the name of the actual method being called.
     *
     * @param string $methodName Method name
     * @param array  $arguments  Array of arguments
     * 
     * @method Call a method from loaded sub class and take care of needed
     *         character set conversion for both input and output values.
     *
     * @return The value returned from the __call() method will be returned to 
     *         the caller of the method.
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */                                  
    public function __call($methodName, $arguments)
    {
        if ($this->_compatibleMode 
            && array_key_exists($methodName, $this->_compatible)
        ) {
            
            $methodName = $this->_compatible[$methodName];
        }

        // Create an instance of the ReflectionMethod class
        $method = new \ReflectionMethod($this->myClass, $methodName);//Added namspace slash Nasser.
        
        $params     = array();
        $parameters = $method->getParameters();

        foreach ($parameters as $parameter) {
            $name  = $parameter->getName();
            $value = array_shift($arguments);
            
            if (is_null($value) && $parameter->isDefaultValueAvailable()) {
                $value = $parameter->getDefaultValue();
            }
            
            $params[$name] = $this->coreConvert(
                $value, 
                $this->getInputCharset(), 
                'utf-8'
            );
        }

        $value = call_user_func_array(array(&$this->myObject, $methodName), $params);

        if ($methodName == 'tagText') {
            foreach ($value as $key=>$text) {
                $value[$key][0] = $this->coreConvert(
                    $text[0], 'utf-8', $this->getOutputCharset()
                );
            }
        } else {
            $value = $this->coreConvert($value, 'utf-8', $this->getOutputCharset());
        }

        return $value;
    }

    /**
     * Garbage collection, release child objects directly
     *          
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public function __destruct() 
    {
        $this->_inputCharset  = null;
        $this->_outputCharset = null;
        $this->myObject      = null;
        $this->myClass       = null;
    }

    /**
     * Set charset used in class input Arabic strings
     *          
     * @param string $charset Input charset [utf-8|windows-1256|iso-8859-6]
     *      
     * @return TRUE if success, or FALSE if fail
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public function setInputCharset($charset)
    {
        $flag = true;
        
        $charset = strtolower($charset);
        
        if (in_array($charset, array('utf-8', 'windows-1256', 'iso-8859-6'))) {
            $this->_inputCharset = $charset;
        } else {
            $flag = false;
        }
        
        return $flag;
    }
    
    /**
     * Set charset used in class output Arabic strings
     *          
     * @param string $charset Output charset [utf-8|windows-1256|iso-8859-6]
     *      
     * @return boolean TRUE if success, or FALSE if fail
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public function setOutputCharset($charset)
    {
        $flag = true;
        
        $charset = strtolower($charset);
        
        if (in_array($charset, array('utf-8', 'windows-1256', 'iso-8859-6'))) {
            $this->_outputCharset = $charset;
        } else {
            $flag = false;
        }
        
        return $flag;
    }

    /**
     * Get the charset used in the input Arabic strings
     *      
     * @return string return current setting for class input Arabic charset
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public function getInputCharset()
    {
        return $this->_inputCharset;
    }
    
    /**
     * Get the charset used in the output Arabic strings
     *         
     * @return string return current setting for class output Arabic charset
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public function getOutputCharset()
    {
        return $this->_outputCharset;
    }
    
    /**
     * Convert Arabic string from one charset to another
     *          
     * @param string $str           Original Arabic string that you would like
     *                              to convert
     * @param string $inputCharset  Input charset
     * @param string $outputCharset Output charset
     *      
     * @return string Converted Arabic string in defined charset
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public function coreConvert($str, $inputCharset, $outputCharset)
    {
        if ($inputCharset != $outputCharset) {
            if ($inputCharset == 'windows-1256') {
                $inputCharset = 'cp1256';
            }
            
            if ($outputCharset == 'windows-1256') {
                $outputCharset = 'cp1256';
            }
            
            $convStr = iconv($inputCharset, "$outputCharset", $str);

            if ($convStr == '' && $str != '') {
                include self::getClassFile('CharsetC');

                $c = I18N_Arabic_CharsetC::singleton();
                
                if ($inputCharset == 'cp1256') {
                    $convStr = $c->win2utf($str);
                } else {
                    $convStr = $c->utf2win($str);
                }
            }
        } else {
            $convStr = $str;
        }
        
        return $convStr;
    }

    /**
     * Convert Arabic string from one format to another
     *          
     * @param string $str           Arabic string in the format set by setInput
     *                              Charset
     * @param string $inputCharset  (optional) Input charset 
     *                              [utf-8|windows-1256|iso-8859-6]
     *                              default value is NULL (use set input charset)
     * @param string $outputCharset (optional) Output charset 
     *                              [utf-8|windows-1256|iso-8859-6]
     *                              default value is NULL (use set output charset)
     *                                  
     * @return string Arabic string in the format set by method setOutputCharset
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public function convert($str, $inputCharset = null, $outputCharset = null)
    {
        if ($inputCharset == null) {
            $inputCharset = $this->_inputCharset;
        }
        
        if ($outputCharset == null) {
            $outputCharset = $this->_outputCharset;
        }
        
        $str = $this->coreConvert($str, $inputCharset, $outputCharset);

        return $str;
    }

    /**
     * Get sub class file path to be included (mapping between class name and 
     * file name/path become independent now)
     *          
     * @param string $class Sub class name
     *                                  
     * @return string Sub class file path
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    protected static function getClassFile($class)
    {
        $dir  = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Arabic';
        $file = $dir . DIRECTORY_SEPARATOR . $class . '.php';

        return $file;
    }
    
    /**
     * Send/set output charset in several output media in a proper way
     *
     * @param string   $mode [http|html|mysql|mysqli|pdo|text_email|html_email]
     * @param resource $conn The MySQL connection handler/the link identifier
     *                                  
     * @return string header formula if there is any (in cases of html, 
     *                text_email, and html_email)
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public static function header($mode = 'http', $conn = null)
    {
        $mode = strtolower($mode);
        $head = '';
        
        switch ($mode) {
        case 'http':
            header('Content-Type: text/html; charset=' . $this->_outputCharset);
            break;
        
        case 'html':
            $head .= '<meta http-equiv="Content-type" content="text/html; charset=';
            $head .= $this->_outputCharset . '" />'; 
            break;
        
        case 'text_email':
            $head .= 'MIME-Version: 1.0\r\nContent-type: text/plain; charset=';
            $head .= $this->_outputCharset . '\r\n'; 
            break;

        case 'html_email':
            $head .= 'MIME-Version: 1.0\r\nContent-type: text/html; charset=';
            $head .= $this->_outputCharset . '\r\n'; 
            break;
        
        case 'mysql':
            if ($this->_outputCharset == 'utf-8') {
                mysql_set_charset('utf8');
            } elseif ($this->_outputCharset == 'windows-1256') {
                mysql_set_charset('cp1256');
            }
            break;

        case 'mysqli':
            if ($this->_outputCharset == 'utf-8') {
                $conn->set_charset('utf8');
            } elseif ($this->_outputCharset == 'windows-1256') {
                $conn->set_charset('cp1256');
            }
            break;

        case 'pdo':
            if ($this->_outputCharset == 'utf-8') {
                $conn->exec('SET NAMES utf8');
            } elseif ($this->_outputCharset == 'windows-1256') {
                $conn->exec('SET NAMES cp1256');
            }
            break;
        }
        
        return $head;
    }

    /**
     * Get web browser chosen/default language using ISO 639-1 codes (2-letter)
     *          
     * @return string Language using ISO 639-1 codes (2-letter)
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public static function getBrowserLang()
    {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2); // ar, en, etc...

        return $lang;
    }

    /**
     * There is still a lack of original, localized, high-quality content and 
     * well-structured Arabic websites; This method help in tag HTML result pages 
     * from Arabic forum to enable filter it in/out.
     *
     * @param string $html The HTML content of the page in question
     *
     * @return boolean True if the input HTML is belong to a forum page
     * @author Khaled Al-Shamaa <khaled@ar-php.org>
     */
    public static function isForum($html)
    {
        $forum = false;
        
        if (strpos($html, 'vBulletin_init();') !== false) {
            $forum = true;
        }
        
        return $forum;
    }
}

/**
 * Arabic Exception class defined by extending the built-in Exception class.
 *  
 * @category  I18N
 * @package   I18N_Arabic
 * @author    Khaled Al-Shamaa <khaled@ar-php.org>
 * @copyright 2006-2013 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @link      http://www.ar-php.org
 */  
class ArabicException extends \Exception
{
    /**
     * Make sure everything is assigned properly
     * 
     * @param string $message Exception message
     * @param int    $code    User defined exception code            
     */         
    public function __construct($message, $code=0)
    {
        parent::__construct($message, $code);
    }
}
