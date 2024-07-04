<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */


class Pager
{

    protected $_itemsOnPage = 20;

    protected $_deltaPages = 3;

    protected $_totalItems = 0;

    protected $_totalPages = 0;

    protected $_currentPage = 1;

    protected $_urlTemplate = '';

    public function __construct($currentPage, $totalItems, $urlTemplate, $itemsOnPage = 20, $deltaPages = 2)
    {
        $this->_itemsOnPage = $itemsOnPage;
        $this->_deltaPages  = $deltaPages;
        $this->_totalItems  = $totalItems;
        $this->_urlTemplate = $urlTemplate;
        $this->_totalPages  = ceil($totalItems / $itemsOnPage);

        $this->_currentPage = $currentPage;
        if ($this->_currentPage <= 0) {
            $this->_currentPage = 1;
        } elseif ($this->_currentPage > $this->_totalPages) {
            $this->_currentPage = $this->_totalPages;
        }
    }

    public function getTotalPage()
    {
        return $this->_totalPages;
    }

    public function getCurrentPage()
    {
        return $this->_currentPage;
    }

    public function getPrev()
    {
        if ($this->_currentPage > 1) {
            return $this->getPage($this->_currentPage - 1, '<-');
        }
    }

    public function getNext()
    {
        if ($this->_currentPage < $this->_totalPages) {
            return $this->getPage($this->_currentPage + 1, '->');
        }
    }

    public function getFirst()
    {
        if (($this->_currentPage - $this->_deltaPages) > 1) {
            return $this->getPage(1, '1');
        }
    }

    public function getLast()
    {
        if (($this->_currentPage + $this->_deltaPages) < $this->_totalPages) {
            return $this->getPage($this->_totalPages, $this->_totalPages);
        }
    }

    public function getPages()
    {
        $startPage = $this->_currentPage - $this->_deltaPages;
        if ($startPage <= 0) $startPage = 1;

        $stopPage = $this->_currentPage + $this->_deltaPages;
        if ($stopPage > $this->_totalPages) $stopPage = $this->_totalPages;

        $r = array();
        for ($page = $startPage; $page <= $stopPage; $page++) {
            $r[$page] = $this->getPage($page);
        }
        return $r;
    }

    public function getPage($page, $name = null)
    {
        if ($name == null) {
            $name = $page;
        } else {
            $name = sprintf($name, $page);
        }

        $r = array();
        $r['url'] = sprintf($this->_urlTemplate, $page);
        $r['name'] = $name;
        if ($page == $this->_currentPage) {
            $r['active'] = true;
        } else {
            $r['active'] = false;
        }
        return $r;
    }

    public function getLiPages()
    {
        $r = '';
        /*
            <li><a href="#">Prev</a></li>
            <li>|&nbsp;&nbsp;</li>
            <li class="active">1</li>
            <li><a href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#">4</a></li>
            <li>&nbsp;&nbsp;|</li>
            <li><a href="#">Next</a></li>
        */
        if (count($this->getPages()) > 1) {
            if ($this->getPrev()) {
                $item = $this->getPrev();
                $r .= '<li><a href="' . $item['url'] . '">' . l('Prev') . '</a></li> <li>|&nbsp;&nbsp;</li> ';
            } else {
                $r .= '<li>' . l('Prev') . '</li> <li>|&nbsp;&nbsp;</li> ';
            }

            if ($this->getFirst()) {
                $item = $this->getFirst();
                $r .= '<li><a href="' . $item['url'] . '">1</a></li> <li>..</li> ';
            }

            $pages = $this->getPages();
            foreach ($pages as $item) {
                if (!$item['active']) {
                    $r .= '<li><a href="' . $item['url'] . '">' . $item['name'] . '</a></li> ';
                } else {
                    $r .= '<li class="active">' . $item['name'] . '</li> ';
                }
            }

            if ($this->getLast()) {
                $item = $this->getLast();
                $r .= '<li>.. ' . l('of') . '</li> <li><a href="' . $item['url'] . '">' . $item['name'] . '</a></li> ';
            }

            if ($this->getNext()) {
                $item = $this->getNext();
                $r .= '<li>&nbsp;&nbsp;|</li> <li><a href="' . $item['url'] . '">' . l('Next') . '</a></li> ';
            } else {
                $r .= '<li>&nbsp;&nbsp;|</li> <li>' . l('Next') . '</li> ';
            }
        }
        return $r;
    }

	public function getLiPagesModern()
    {
        $r = '';

        if (count($this->getPages()) > 1) {
            if ($this->getPrev()) {
                $item = $this->getPrev();
                $r .= '<li class="page-item prev"><a class="page-link" href="' . $item['url'] . '">' . l('Prev') . '</a></li>';
            } else {
                $r .= '<li class="page-item prev disabled"><a class="page-link" href="" onclick="return false;">' . l('Prev') . '</a></li>';
            }

            if ($this->getFirst()) {
                $item = $this->getFirst();
                $r .= '<li class="page-item first"><a class="page-link" href="' . $item['url'] . '">1</a></li>';
            }

            $pages = $this->getPages();
            foreach ($pages as $item) {
                if (!$item['active']) {
                    $r .= '<li class="page-item"><a class="page-link" href="' . $item['url'] . '">' . $item['name'] . '</a></li> ';
                } else {
                    $r .= '<li class="page-item active"><a  class="page-link" href="" onclick="return false;">' . $item['name'] . '</a></li> ';
                }
            }

            if ($this->getLast()) {
                $item = $this->getLast();
                $r .= '<li class="page-item last"><a class="page-link" href="' . $item['url'] . '">' . $item['name'] . '</a></li> ';
            }

            if ($this->getNext()) {
                $item = $this->getNext();
                $r .= '<li class="page-item next"><a class="page-link" href="' . $item['url'] . '">' . l('Next') . '</a></li> ';
            } else {
                $r .= '<li  class="page-item next disabled"><a class="page-link" href=""  onclick="return false;">' . l('Next') . '</a></li> ';
            }
        }
        return $r;
    }

    public function getAbPages()
    {
        $r = '';
        if (count($this->getPages()) > 1) {
            if ($this->getPrev()) {
                $item = $this->getPrev();
                $r .= '<a href="' . $item['url'] . '">' . l('Prev') . '</a> |&nbsp;&nbsp; ';
            } else {
                $r .= '' . l('Prev') . ' |&nbsp;&nbsp; ';
            }

            if ($this->getFirst()) {
                $item = $this->getFirst();
                $r .= '<a href="' . $item['url'] . '">1</a> .. ';
            }

            $pages = $this->getPages();
            foreach ($pages as $item) {
                if (!$item['active']) {
                    $r .= '<a href="' . $item['url'] . '">' . $item['name'] . '</a> ';
                } else {
                    $r .= '<b>' . $item['name'] . '</b> ';
                }
            }

            if ($this->getLast()) {
                $item = $this->getLast();
                $r .= '.. ' . l('of') . ' <a href="' . $item['url'] . '">' . $item['name'] . '</a> ';
            }

            if ($this->getNext()) {
                $item = $this->getNext();
                $r .= '&nbsp;&nbsp;| <a href="' . $item['url'] . '">' . l('Next') . '</a> ';
            } else {
                $r .= '&nbsp;&nbsp;| ' . l('Next') . ' ';
            }
        }
        return $r;
    }
}

