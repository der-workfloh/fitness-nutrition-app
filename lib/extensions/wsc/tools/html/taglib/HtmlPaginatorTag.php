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
 * @file HtmlPaginatorTag.php
 * @namespace WSC\tools\html\taglib
 * 
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 06.09.2010<br/>
 * Version 1.1, 02.04.2011<br/>
 * Version 1.2, 03.08.2011<br/>
 * Version 2.0, 15.11.2013<br/>
 *
 */

namespace WSC\tools\html\taglib;

use APF\core\pagecontroller\Document;
use APF\extensions\htmllist\taglib\html_taglib_list;
use APF\tools\request\RequestHandler;
use WSC\tools\link\RouterHandler;

class HtmlPaginatorTag extends Document
{
    const SEARCH_VISIBLE        = true;
    const SEARCH_INVISIBLE      = false;
    
    protected $__aOrder = array(
        0 => 'ASC',
        1 => 'DESC' );
    protected $__aLimit = array( 
        0 => 10, 
        1 => 25, 
        2 => 50, 
        3 => 100 );
    protected $__aFilter = array(
        0 => 'alle',
        1 => '0-9',
        2 => 'A-F',
        3 => 'G-L',
        4 => 'M-R',
        5 => 'S-W',
        6 => 'XYZ' );
    protected $__iDataQuantity  = 0;
    protected $__iOffset        = 0;
    protected $__iLimit         = 1;
    protected $__iFilterType    = 0;
    protected $__iOrder         = 0;
    protected $__sOrderAttr     = '';
    protected $__bSearch        = self::SEARCH_VISIBLE;
    protected $__sSearch        = '';
    protected $__bSearchUsed    = false;
    protected $__aExtendedURLString = array();
    
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        /**
         * @TODO: Probleme mit UTF-8
         * $this->__iOffset    = RequestHandler::getValue( 'paginator_start' , 0 );
         * $this->__iLimit            = RequestHandler::getValue( 'paginator_offset' , 1 );
         * $this->__iFilterType        = RequestHandler::getValue( 'paginator_filter_index' , 0 );
         */
        $this->__iOffset        = \array_key_exists('paginator_offset',$_GET) ? (int) $_GET['paginator_offset'] : 0 ;
        $this->__iLimit         = \array_key_exists('paginator_limit',$_GET) ? (int) $_GET['paginator_limit'] : 1 ;
        $this->__iFilterType    = \array_key_exists('paginator_filter',$_GET) ? (int) $_GET['paginator_filter'] : 0; 
        $this->__iOrder         = \array_key_exists('paginator_order',$_GET) ? (int) $_GET['paginator_order'] : 0 ;
        $this->__sOrderAttr     = \array_key_exists('paginator_order_attr',$_GET) ? trim($_GET['paginator_order_attr']) : '' ;
        if (\array_key_exists('paginator_search',$_GET)) {
            $this->__sSearch = trim($_GET['paginator_search']);
            $this->__bSearchUsed = true;
        }
    }
    
    
    
    /**
     * Tranform content
     * @return string
     */
    public function transform()
    {
        $this->__Content = '';
        if ($this->__bSearch === self::SEARCH_VISIBLE) {
            $this->__Content .= $this->__buildUpSearch();
        }
            
        $this->__Content .= '<div class="paginator_pages">Seiten: '.$this->getCurrentPage().'/'.$this->getPageCount().'</div>';
        $this->__Content .= '<div class="paginator_elements">Gesamteinträge: '.$this->getDataQuantity().'</div>';
        
        $list = new html_taglib_list();
        $list->addList( 'list:unordered' , array( 'id' => 'paginator_filter_alpha' , 'class' => 'paginator_filter_alpha' ) );
        $list->addList( 'list:unordered' , array( 'id' => 'paginator_filter_quantity' , 'class' => 'paginator_filter_quantity' ) );
        $list->addList( 'list:unordered' , array( 'id' => 'paginator_filter_pages' , 'class' => 'paginator_filter_pages' ) );

        $this->__buildUpQuantityFilter($list);
        $this->__buildUpAlphaFilter($list);
        $this->__buildUpPager($list);

        $this->__Content .= '<div class="paginator_filter">'.$list->transform().'</div>';

        $this->__Content = '<div class="paginator">'.$this->__Content.'</div><div class="paginator_end" />';

        return $this->__Content;
    }
    
    
    
    /**
     * Transform content
     * @return string
     */
    public function transformOnPlace()
    {
        return $this->transform();
    }
    
    
    
    /**
     * Set data quantity
     * @param int $Value 
     */
    public function setDataQuantity($Value)
    {
        if (!\is_int($Value)) {
            throw new \InvalidArgumentException( '['.\get_class().'::'.
                    'setDataQuantity()] Invalid argument!');
        }
        $this->__iDataQuantity = $Value;
    }
    
    
    
    /**
     * Add extended url parameter
     * @param string $sKey
     * @param string $sValue 
     */
    public function addExtendedURLString($sKey, $sValue) 
    { 
        $this->__aExtendedURLString[$sKey] = $sValue;
    }
    
    
    
    /**
     * Set if the search formular will be rendered or not
     * @param boolean $bVisible 
     */
    public function setSearchVisibility($bVisible = self::SEARCH_INVISIBLE)
    {
        switch ($bVisible) {
            case self::SEARCH_VISIBLE:
                $this->__bSearch = self::SEARCH_VISIBLE;
                break;
            case self::SEARCH_INVISIBLE:
                $this->__bSearch = self::SEARCH_INVISIBLE;
                break;
            default:
                throw new \InvalidArgumentException('['.\get_class().'::setSearchVisibility()] '.
                        'Invalid argument!');
        }
    }
    


    /**
     * Get extended url string with specific key
     * @param string $sKey
     * @return int
     */
    public function getExtendedURLString($sKey) 
    { 
        if (!\array_key_exists( $sKey , $this->__aExtendedURLString) ) {
            return null;
        }
        return $this->__aExtendedURLString[$sKey];
    }

    
    
    /**
     * Get data quantity
     * @return int
     */
    public function getDataQuantity() 
    { 
        return $this->__iDataQuantity; 
    }
    
    
    
    /**
     * Get current page
     * @return int
     */
    public function getCurrentPage() 
    { 
        $iCurrentPage = (int)(($this->__iOffset+1) / $this->__aLimit[$this->__iLimit]);
        if (($this->__iOffset+1) % $this->__aLimit[$this->__iLimit] !== 0) {
            ++$iCurrentPage;
        }
        
        if ($iCurrentPage > $this->getPageCount()) {
            $iCurrentPage = $this->getPageCount() * ($this->__iLimit - 1); 
        }
        
        return $iCurrentPage;
    }

    
    
    /**
     * Get page quantity
     * @return int
     */
    public function getPageCount() 
    {
        $iMaxPages = (int)($this->__iDataQuantity / $this->__aLimit[$this->__iLimit]);
        if ($this->__iDataQuantity % $this->__aLimit[$this->__iLimit] !== 0) {
            ++$iMaxPages;
        }
            
        return $iMaxPages; 
    }

    
    
    /**
     * Get start limit
     * @return int
     */
    public function getOffset() 
    { 
        return $this->__iOffset; 
    }

    
    
    /**
     * Get offset
     * @return int
     */
    public function getLimit() 
    { 
        return $this->__aLimit[$this->__iLimit]; 
    }

    
    
    /**
     * Get filter
     * @return string
     */
    public function getFilter() 
    { 
        return $this->__aFilter[$this->__iFilterType]; 
    }

    
    
    /**
     * Get filter index
     * @return int
     */
    public function getFilterIndex() 
    { 
        return $this->__iFilterType; 
    }
    
    
    
    /**
     * Get data order
     * return string
     */
    public function getOrder() 
    { 
        return $this->__aOrder[$this->__iOrder]; 
    }
    
    
    
    /**
     * Get data order attribute
     * return string
     */
    public function getOrderAttribute() 
    { 
        return $this->__sOrderAttr; 
    }

    
    
    /**
     * Get link appendix
     * @return string
     */
    public function getLinkAppendix() 
    { 
        return $this->__sLinkAppendix; 
    }

    
    
    /**
     * Get array of page entry possibilities
     * @return array
     */
    public function getAllLimits() 
    { 
        return $this->__aLimit; 
    }

    
    
    /**
     * Get array of filter entry possibilities
     * @return array
     */
    public function getAllFilters() 
    { 
        return $this->__aFilter; 
    }
    
    
    
    /**
     * Get a prepared link for ordering data
     * @param boolean $bSortASC
     * @return string
     */
    public function getOrderLinkAppendix($bSortASC = true)
    {
        RouterHandler::addQueryValue('paginator_offset', '0');
        RouterHandler::addQueryValue('paginator_limit', $this->__iLimit);            
        RouterHandler::addQueryValue('paginator_filter', $this->__iFilterType);
        RouterHandler::addQueryValue('paginator_order', $bSortASC ? '0' : '1');
        RouterHandler::addQueryValue('paginator_order_attr', '');
        foreach ($this->__aExtendedURLString as $k => $v) {
            RouterHandler::addQueryValue($k, $v);
        }
        $s = RouterHandler::getManipulatedUrl();
        foreach ($this->__aExtendedURLString as $k => $v) {
            RouterHandler::removeQueryValue($k, $v);
        }
        RouterHandler::removeQueryValue('paginator_order_attr');
        RouterHandler::removeQueryValue('paginator_order');
        RouterHandler::removeQueryValue('paginator_filter');
        RouterHandler::removeQueryValue('paginator_limit');
        RouterHandler::removeQueryValue('paginator_offset');
        return $s;
    }
    
    
    
    /**
     * Check if the search has been used
     * @return boolean 
     */
    public function isSearchInUse() 
    { 
        return $this->__bSearchUsed; 
    }
    
    
    
    /**
     * Get the search value
     * @return search 
     */
    public function getSearchValue() 
    { 
        return $this->__sSearch; 
    }
    
    
    
    /**
     * Build up quantity filter link list
     * @param html_taglib_list $oList 
     */
    protected function __buildUpQuantityFilter(html_taglib_list &$oList)
    {
        $eList = $oList->getListById('paginator_filter_quantity');
        foreach ($this->getAllLimits() as $k => $v) {
            if ((int)$k === (int)$this->__iLimit) {
                $eList->addElement( '<span class="current_page">'.$v.'</span>' ); 
                continue;
            }
            
            RouterHandler::addQueryValue('paginator_offset', '0');
            RouterHandler::addQueryValue('paginator_limit', $k);            
            RouterHandler::addQueryValue('paginator_filter', $this->__iFilterType);
            RouterHandler::addQueryValue('paginator_order', $this->__iOrder);
            RouterHandler::addQueryValue('paginator_order_attr', $this->__sOrderAttr);
            
            if ($this->__bSearch === self::SEARCH_VISIBLE) {
                RouterHandler::addQueryValue( 'paginator_search' , $this->__sSearch );
            }
            
            foreach ($this->__aExtendedURLString as $key => $val) {
                RouterHandler::addQueryValue($key, $val);
            }
            
            $l = '<a href="'.RouterHandler::getManipulatedUrl().'" >'.$v.'</a>';
            $eList->addElement($l);
            
            foreach ($this->__aExtendedURLString as $key => $val) {
                RouterHandler::removeQueryValue($key, $val);
            }
            
            if ($this->__bSearch === self::SEARCH_VISIBLE) {
                RouterHandler::removeQueryValue('paginator_search');
            }
            RouterHandler::removeQueryValue('paginator_order_attr');
            RouterHandler::removeQueryValue('paginator_order');
            RouterHandler::removeQueryValue('paginator_filter');
            RouterHandler::removeQueryValue('paginator_limit');
            RouterHandler::removeQueryValue('paginator_offset');
        }
    }
    
    
    
    /**
     * Build up alpha filter link list
     * @param html_taglib_list $oList 
     */
    protected function __buildUpAlphaFilter(html_taglib_list &$oList )
    {
        $eList = $oList->getListById('paginator_filter_alpha');
        foreach ($this->getAllFilters() as $k => $v) {
            if ((int)$k === (int)$this->__iFilterType) {
                $eList->addElement( '<span class="current_page">'.$v.'</span>' ); 
                continue;
            }
            
            RouterHandler::addQueryValue('paginator_offset', '0');
            RouterHandler::addQueryValue('paginator_limit', $this->__iLimit);
            RouterHandler::addQueryValue('paginator_filter', $k);
            RouterHandler::addQueryValue('paginator_order', $this->__iOrder);
            RouterHandler::addQueryValue('paginator_order_attr', $this->__sOrderAttr);
            if ($this->__bSearch === self::SEARCH_VISIBLE) {
                RouterHandler::addQueryValue('paginator_search', $this->__sSearch);
            }
            
            foreach($this->__aExtendedURLString as $key => $val) {
                RouterHandler::addQueryValue($key, $val);
            }
            
            $l = '<a href="'.RouterHandler::getManipulatedUrl().'" >'.$v.'</a>';
            $eList->addElement($l);
            
            foreach ($this->__aExtendedURLString as $key => $val) {
                RouterHandler::removeQueryValue($key, $val);
            }
            
            if ($this->__bSearch === self::SEARCH_VISIBLE) {
                RouterHandler::removeQueryValue('paginator_search');
            }
            
            RouterHandler::removeQueryValue('paginator_order_attr');
            RouterHandler::removeQueryValue('paginator_order');
            RouterHandler::removeQueryValue('paginator_filter');
            RouterHandler::removeQueryValue('paginator_limit');
            RouterHandler::removeQueryValue('paginator_offset');
        }
    }
    
    
    
    /**
     * Build up page link list
     * @param html_taglib_list $oList 
     */
    protected function __buildUpPager(html_taglib_list &$oList )
    {
        $iCurrentPage   = $this->getCurrentPage();
        $iMaxPage       = $this->getPageCount();
      
        $eList = $oList->getListById('paginator_filter_pages');
        for ($n = 0; $n < $iMaxPage; ++$n) {
            if ($n+1 === $iCurrentPage) {
                $eList->addElement('<span class="current_page">'.($n+1).'</span>'); 
                continue;
            }
            
            RouterHandler::addQueryValue('paginator_offset', $n*$this->__aLimit[$this->__iLimit]);
            RouterHandler::addQueryValue('paginator_limit', $this->__iLimit);
            RouterHandler::addQueryValue('paginator_filter', $this->__iFilterType);
            RouterHandler::addQueryValue('paginator_order', $this->__iOrder);
            RouterHandler::addQueryValue('paginator_order_attr', $this->__sOrderAttr);
            
            if ($this->__bSearch === self::SEARCH_VISIBLE) {
                RouterHandler::addQueryValue('paginator_search', $this->__sSearch);
            }
            
            foreach ($this->__aExtendedURLString as $key => $val) {
                RouterHandler::addQueryValue($key, $val);
            }
            
            $l = '<a href="'.RouterHandler::getManipulatedUrl().'" >'.($n+1).'</a>';
            $eList->addElement($l);
            
            foreach ($this->__aExtendedURLString as $key => $val) {
                RouterHandler::removeQueryValue($key, $val);
            }
            
            if ($this->__bSearch === self::SEARCH_VISIBLE) {
                RouterHandler::removeQueryValue('paginator_search');
            }
            
            RouterHandler::removeQueryValue('paginator_order_attr');
            RouterHandler::removeQueryValue('paginator_order');           
            RouterHandler::removeQueryValue('paginator_filter');
            RouterHandler::removeQueryValue('paginator_limit');
            RouterHandler::removeQueryValue('paginator_offset');
        }
    }
    
    
    
    /**
     * Build up search formular
     * @return string 
     */
    protected function __buildUpSearch()
    {
        $sExtendedURLStringElements = '';
        foreach ($this->__aExtendedURLString as $k => $v) {
            $sExtendedURLStringElements .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
        }
        
        $s = '<div class="paginator_search">
            <form name="fSearch" method="get" action="">
                <input type="text" name="paginator_search" id="paginator_search" value="'.RequestHandler::getValue('paginator_search').'" />
                &nbsp;
                <input type="hidden" name="page" value="'.RequestHandler::getValue('page').'" />
                <input type="hidden" name="mainview" value="'.RequestHandler::getValue('mainview').'" />
                <input type="hidden" name="paginator_offset" value="0" />
                <input type="hidden" name="paginator_limit" value="'.$this->__iLimit.'" />
                <input type="hidden" name="paginator_filter" value="'.$this->__iFilterType.'" />
                <input type="hidden" name="paginator_order" value="'.$this->__iOrder.'" />
                <input type="hidden" name="paginator_order_attr" value="'.$this->__sOrderAttr.'" />'.
                $sExtendedURLStringElements
                .'<input type="submit" value="Suchen" />
            </form>
        </div>';
        
        return $s;
    }
}
