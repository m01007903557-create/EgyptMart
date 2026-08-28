<?php
declare(strict_types=1);

// منع إعادة تعريف الكلاس
if (class_exists('Pagination')) {
    return;
}

class Pagination {
    
    private int $page = 1;
    private int $limit = 20;
    
    public function setpage($page = 1): void {
        $this->page = (int)$page;
    }
    
    public function setlimit($limit): void {
        $this->limit = (int)$limit;
    }
    
    public function getCurrentPage(): int {
        if ($this->page > 1) {
            return $this->page;
        }
        return isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 
               ? (int)$_GET['page'] 
               : 1;
    }
    
    public function getLimit(int $default = 20): int {
        if ($this->limit > 0) {
            return $this->limit;
        }
        return isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 
               ? (int)$_GET['limit'] 
               : $default;
    }
    
    public function getStart(int $page, int $limit, int $total): int {
        $start = ($page - 1) * $limit;
        if ($start >= $total) {
            $start = max(0, $total - $limit);
        }
        return max(0, $start);
    }
    
    /**
     * حساب عدد الصفحات بأمان (مع تحويل إلى int)
     */
    public function getTotalPages($totalRecords, $limit): int {
        $totalRecords = (int)$totalRecords;
        $limit = (int)$limit;
        return ($limit > 0) ? (int)ceil($totalRecords / $limit) : 1;
    }
    
    public function getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring): string {
        $page = (int)$page;
        $totalpages = ceil($totalitems / $limit);
        if ($totalpages <= 1) {
            return '';
        }
        
        $html = '<div class="dataTables_paginate paging_bootstrap">';
        $html .= '<ul class="pagination">';
        
        if ($page > 1) {
            $html .= '<li class="prev"><a href="' . $targetpage . $pagestring . ($page - 1) . '"><i class="icon-double-angle-left"></i></a></li>';
        } else {
            $html .= '<li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li>';
        }
        
        $start = max(1, $page - $adjacents);
        $end = min($totalpages, $page + $adjacents);
        
        for ($i = $start; $i <= $end; $i++) {
            $activeClass = ($i == $page) ? 'active' : '';
            $html .= '<li class="' . $activeClass . '"><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
        }
        
        if ($page < $totalpages) {
            $html .= '<li class="next"><a href="' . $targetpage . $pagestring . ($page + 1) . '"><i class="icon-double-angle-right"></i></a></li>';
        } else {
            $html .= '<li class="next disabled"><a href="#"><i class="icon-double-angle-right"></i></a></li>';
        }
        
        $html .= '</ul></div>';
        return $html;
    }
}
?>