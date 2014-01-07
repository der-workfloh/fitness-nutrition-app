<?php
/**
 * <!--
 * This file is part of the wsCatalyst-Extensions (wsC) for the
 * Adventure-PHP-Framework published under
 * https://sourceforge.net/projects/wscatalyst.
 *
 * The wsC is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The wsC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with the wsC. If not, see http://www.gnu.org/licenses/lgpl-3.0.txt.
 * -->
 */

/**
 * @file wsList.php
 * @namespace WSC\tools\listhandler
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 27.07.2010<br/>
 * Version 1.1, 26.02.2011<br/>
 * Version 1.2, 02.04.2011<br/>
 * Version 2.0, 15.11.2013<br/>
 *
 */

namespace WSC\tools\listhandler;

use APF\core\pagecontroller\APFObject;
use APF\core\registry\Registry;
use WSC\core\language\LanguageManager;

abstract class DoublyLinkedListHandler extends APFObject
{
    const FILTER_ALL                = 0;
    const FILTER_NUMERIC            = 1;
    const FILTER_AF                 = 2;
    const FILTER_GL                 = 3;
    const FILTER_MR                 = 4;
    const FILTER_SW                 = 5;
    const FILTER_XYZ                = 6;
    const SEARCH_ACTIVE             = true;
    const SEARCH_INACTIVE           = false;
    const LIMIT_DEFAULT             = 25;
    const OFFSET_DEFAULT            = 0;
    const DATA_DISPLAY_QUANTITY_DEFAULT = 0;
    const ORDER_ASC                 = 'ASC';
    const ORDER_DESC                = 'DESC';
    
    
    
    /**
     * Data list
     * @var \SplDoublyLinkedList 
     */
    protected $__splDllData         = null;
    
    
    
    /**
     * Flag if list handler is initialized
     * @var boolean
     */
    protected $__isInitialized      = false;
    
    
    
    /**
     * Data set limit
     * @var int
     */
    protected $__iLimit             = self::LIMIT_DEFAULT;
    
    
    /**
     * Allowed types
     * @var array
     */
    protected $__aAllowedTypes      = array( '0' );
    
    
    
    /**
     * Offset
     * @var int
     */
    protected $__iOffset            = self::OFFSET_DEFAULT;
    
    
    
    /**
     * Returned amount of data sets
     * @var int 
     */
    protected $__iDataQuantity      = self::DATA_DISPLAY_QUANTITY_DEFAULT;
    
    
    
    /**
     * Order type
     * @var string
     */
    protected $__sOrder             = self::ORDER_ASC;
    
    
    
    /**
     * URL attribute to manage order type
     * @var string
     */
    protected $__sOrderAttr         = '';
    
    
    
    /**
     * Set of filters
     * @var array 
     */
    protected $__aFilter = array(
        0 => 'REGEXP \'.*\'',
        1 => 'REGEXP \'[0-9]{1,}\'',
        2 => 'REGEXP \'[a-fA-F]{1,}\'',
        3 => 'REGEXP \'[g-lG-L]{1,}\'',
        4 => 'REGEXP \'[m-rM-R]{1,}\'',
        5 => 'REGEXP \'[s-wS-W]{1,}\'',
        6 => 'REGEXP \'[xyzXYZ]{1,}\'' );
    
    
    
    /**
     * Explicit used filter type, see $__aFilter
     * @var int
     */
    protected $__iFilter            = self::FILTER_ALL;
    
    
    
    /**
     * Flag using search
     * @var boolean
     */
    protected $__bSearch            = self::SEARCH_INACTIVE;
    
    
    
    /**
     * Search string value
     * @var string
     */
    protected $__sSearch            = '';



    /**
     * Clone
     */
    private function  __clone() 
    {}
    
    

    /**
     * Constructor
     * $initParams: Offset, Max
     * @param array $initParams
     * @param boolean $bDelay
     */
    public function __construct( array $init = array() , $bDelay = false )
    {
        $this->__initialize($init);
        if ($bDelay === false) {
            $this->__createList($init);
        }
    }
    
    
    
    /**
     * Create list manually
     * @param array $init 
     */
    public function createList(array $init = array())
    {        
        $this->__createList($init);
    }



    /**
     * $initParams: Offset, Max
     * @param array $initParams
     */
    protected function __initialize(array $initParams = array())
    {
        if ($this->__isInitialized === false) {
            $this->setContext(Registry::retrieve('APF\core', 'App'));
            $this->setLanguage(LanguageManager::getShortLocale());
            $this->__splDllData = new \SplDoublyLinkedList();
            $this->__isInitialized = true;

            // --- Lege Startpunkt der Liste fest
            if (\array_key_exists('Offset', $initParams) && \is_numeric($initParams['Offset'])) {
                $this->__iOffset = $initParams['Offset'];
            }

            // --- Lege Laenge der Anzeige fest
            if (\array_key_exists('Max', $initParams) && \is_numeric($initParams['Max'])) {
                $this->__iLimit = $initParams['Max'];
            }
        }
    }



    /**
     * Get the list elements as array
     * @param array $initParams
     * @return array
     */
    abstract public function getListAsArray(array $initParams = array());


    
    /**
     * Get the list
     * @param array $aInit
     * @return SplDoublyLinkedList
     */
    public function getList(array $aInit = array()) 
    { 
        return $this->__splDllData; 
    }



    /**
     * Holt einen Teil der Liste aus der DB, um diese dann aufzubauen
     * @param array $initParams
     * @return void
     */
    abstract protected function __createList(array $initParams = array());



    /**
     * Set maximum element quantity
     * @param int $iMaxElements
     * @return void|bool
     */
    public function setLimit($iMaxElements)
    {
        if (!\is_int($iMaxElements) || $iMaxElements < 0) {
            throw new \InvalidArgumentException( '['.\get_class().'::'.
                    'setMaxElements()] Invalid argument!');     
        }
        $this->__iLimit = $iMaxElements;
    }
    
    
    
    /**
     * Set the offset
     * @param int $iOffset 
     */
    public function setOffset($iOffset)
    {
        if (!\is_int($iOffset) || $iOffset < 0) {
            throw new \InvalidArgumentException( '['.\get_class().'::'.
                    'setOffset()] Invalid argument!');        
        }
        $this->__iOffset = $iOffset;
    }
    
    
    
    /**
     * Set the filter index<br/>
     * Possibilities:<br/>
     * <ul>
     *  <li>wsList::FILTER_ALL</li>
     *  <li>wsList::FILTER_NUMERIC</li>
     *  <li>wsList::FILTER_AF</li>
     *  <li>wsList::FILTER_GL</li>
     *  <li>wsList::FILTER_MR</li>
     *  <li>wsList::FILTER_SW</li>
     *  <li>wsList::FILTER_XYZ</li>
     * </ul>
     * @param int $iFilter 
     */
    public function setFilter($iFilter)
    {
        switch ($iFilter) {
            case self::FILTER_ALL:
                $this->__iFilter = self::FILTER_ALL;
                break;
            case self::FILTER_NUMERIC:
                $this->__iFilter = self::FILTER_NUMERIC;
                break;
            case self::FILTER_AF:
                $this->__iFilter = self::FILTER_AF;
                break;
            case self::FILTER_GL:
                $this->__iFilter = self::FILTER_GL;
                break;
            case self::FILTER_MR:
                $this->__iFilter = self::FILTER_MR;
                break;
            case self::FILTER_SW:
                $this->__iFilter = self::FILTER_SW;
                break;
            case self::FILTER_XYZ:
                $this->__iFilter = self::FILTER_XYZ;
                break;
            default:
                throw new \InvalidArgumentException('['.\get_class().'::'.
                    'setFilter()] Invalid argument!');
        }
    }
    
    
    
    /**
     * Set order type
     * @param string $sOrder
     */
    public function setOrder($sOrder) 
    { 
        $this->__sOrder = $sOrder;
    }
    
    
    
    /**
     * Set order attribut
     * @param string $sOrderAttribute
     */
    public function setOrderAttribute($sOrderAttribute) 
    { 
        $this->__sOrderAttr = $sOrderAttribute; 
    }

    
    
    /**
     * Set allowed types
     * @param array $aAllowedTypes
     */
    public function setAllowedTypes(array $aAllowedTypes) 
    { 
        $this->__aAllowedTypes = $aAllowedTypes; 
    }
    
    
    
    /**
     * Set if search is being used<br/>
     * Possibilities:<br/>
     * <ul>
     *  <li>wsList::SEARCH_ACTIVE</li>
     *  <li>wsList::SEARCH_INACTIVE</li>
     * </ul>
     * @param boolean $bSearchActivity 
     */
    public function setSearchMode($bSearchActivity = self::SEARCH_INACTIVE)
    {
        switch ($bSearchActivity) {
            case self::SEARCH_ACTIVE:
                $this->__bSearch = self::SEARCH_ACTIVE;
                break;
            case self::SEARCH_INACTIVE:
                $this->__bSearch = self::SEARCH_INACTIVE;
                break;
            default:
                throw new \InvalidArgumentException('['.\get_class().'::'.
                    'setSearchMode()] Invalid argument!');
        }
    }
    
    
    
    /**
     * Set the search value
     * @param string $sSearch 
     */
    public function setSearchValue($sSearch) 
    { 
        $this->__sSearch = $sSearch; 
    }



    /**
     * Get maximum element quantity
     * @return int
     */
    public function getLimit() 
    { 
        return $this->__iLimit; 
    }

    
    
    /**
     * Get allowed types
     * @return array
     */
    public function getAllowedTypes() 
    { 
        return $this->__aAllowedTypes; 
    }

    
    
    /**
     * Get offset
     * @return int
     */
    public function getOffset() 
    { 
        return $this->__iOffset; 
    }
    
    
    
    /**
     * Get order type
     * @return string
     */
    public function getOrder() 
    { 
        return $this->__sOrder; 
    }
    
    
    
    /**
     * Get order attribute
     * @return string
     */
    public function getOrderAttribute() 
    { 
        return $this->__sOrderAttr; 
    }

    
    
    /**
     * Check if already initialized
     * @return bool
     */
    public function isInitialized() 
    { 
        return $this->__isInitialized; 
    }

    
    
    /**
     * Count element quantity
     * @return int
     */
    public function count() 
    { 
        return (int)$this->__iDataQuantity; 
    }
}
