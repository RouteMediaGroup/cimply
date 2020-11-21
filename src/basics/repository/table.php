<?php

/*
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2016 RouteMedia. All rights reserved.
 */

namespace Cimply\Basics\Repository;

/**
 * Description of Cim_ViewModel_Table
 *
 * @author MikeCorner
 */
class Table {
    public function BuildTableFromArray($array){
        // start table
        $html = '<table>';
        // header row
        $html .= '<tr>';
        foreach($array[0] as $key=>$value){
            $html .= '<th>' . $key . '</th>';
        }
        $html .= '</tr>';
        // data rows
        foreach( $array as $key=>$value){
            $html .= '<tr>';
            foreach($value as $key2=>$value2){
                $html .= '<td>' . $value2 . '</td>';
            }
            $html .= '</tr>';
        }
        // finish table and return it
        $html .= '</table>';
        return $html;
    }
}
