<?php
namespace Gy\Tools;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

/**
 * class Pagination 
 */
class Pagination 
{
    
    public static $namePage = 'page'; // можно поменять на своё, параметр урл страки показывающий текущию страницу
    
    // < 1 2 3 4 (5) 6 7 8 9 ... >
    // < 2 3 4 5 (6) 7 8 9 10 ... > 1        13            9                 4
    public static function showOtherPages($n, $maxPagesThis, $maxPages = 9, $countLeftRightPages = 4)
    {
        $result = []; 

        if (($n - $countLeftRightPages) <= 1){
            $tmin = 1;
            if ($maxPages <= $maxPagesThis) {
                $tmax = $maxPages;
            } else {
                $tmax = $maxPagesThis; 
            }
        } elseif (($n + $countLeftRightPages) >= $maxPagesThis) {
            $tmin = ($maxPagesThis - $maxPages) +1;
            $tmax = $maxPagesThis;
        } else {
            $tmin = $n - $countLeftRightPages;
            $tmax = $n + $countLeftRightPages;
        }

        for ( $i= $tmin; $i <= $tmax; $i++){
            $result[] = $i;
        }

        return $result;
    }
    
    public static function getHtmlPaginationType1($allPages, $thisPage )
    {
        $htmlCodePagination = '';
        for ($i =1; $i <= $allPages; $i++){
            $active1 = '<a href="?'.self::$namePage.'='.$i.'">';
            $active2 = '</a>';
            if ($thisPage == $i) {
                $active1 = '(';
                $active2 = ')';
            }
            $htmlCodePagination .= $active1.$i.$active2.' ';
        }
        return $htmlCodePagination;
    }
    
    public static function getPaginationType2($data, $countNewsIn1Page )
    {
        
        $pagasAllData = array_chunk($data, $countNewsIn1Page, true);
        $allPages = count($pagasAllData);
                
        $thisPage = self::getNumberThisPagePagination();
        $arrayPages = self::showOtherPages($thisPage, $allPages, 9) ;
        $show3PointRigth = !in_array($allPages, $arrayPages) ;
        $show3PointLeft = !in_array(1, $arrayPages) ;
        $showLeft = ($thisPage > 1) ;
        $showRigth = ($allPages > $thisPage) ;
        
        $htmlCodePagination = '';
        if ($showLeft){
            $htmlCodePagination .= ' <a href="?'.self::$namePage.'='.($thisPage - 1).'"> < </a> ';
        }
        if ($show3PointLeft){
            $htmlCodePagination .= ' ... ';
        }
        foreach ($arrayPages as $val) {
            $active1 = '<a href="?'.self::$namePage.'='.$val.'">';
            $active2 = '</a>';
            if ($thisPage == $val) {
                $active1 = '(';
                $active2 = ')';
            }
            $htmlCodePagination .= $active1.$val.$active2.' ';
        }
        if ($show3PointRigth){
            $htmlCodePagination .= ' ... ';
        }
        if ($showRigth){
            $htmlCodePagination .= ' <a href="?'.self::$namePage.'='.($thisPage + 1).'"> > </a> ';
        }
        return $htmlCodePagination;
    }
    
    public static function getNumberThisPagePagination()
    {
        global $_REQUEST;
        $thisNumberPage = $_REQUEST[self::$namePage];
        if ($thisNumberPage <= 0){
            $thisNumberPage = 1;
        }
        return $thisNumberPage;
    }
    
    public static function getPaginationType1($data, $countNewsIn1Page)
    {
        return self::getHtmlPaginationType1(
            \ceil(\count($data) / $countNewsIn1Page),
            self::getNumberThisPagePagination()
        );
    }
    
    public static function getDataFrom1Page($data, $countNewsIn1Page){
        return array_slice($data, ((self::getNumberThisPagePagination() * $countNewsIn1Page) - $countNewsIn1Page), $countNewsIn1Page);
    }
}

