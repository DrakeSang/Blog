<?php

namespace BlogBundle\Entity;

class Pagination
{
    // total articles in table
    private $all_articles;

    // limit of items per page
    private $limit;

    // total number of pages needed
    private $total_pages;

    public function setTotalRecords($allArticles) {
        $this->all_articles = $allArticles;
    }

    public function getTotalRecordsCount() {
        return count($this->all_articles);
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function getTotalPages() {
        // determines how many pages there will be
        if (!empty($this->all_articles)) {
            $this->total_pages = ceil($this->getTotalRecordsCount() / $this->getLimit());
        }

        return $this->total_pages;
    }

    public function getOffset($page) {
        $offset = ($page - 1) * 2;

        return $offset;
    }

    public function getCurrentPage() {
        if(isset($_GET['page'])) {
            $page = $_GET['page'];
        }else {
            $page = 1;
        }

        return $page;
    }
}