<?php
///**
// * Created by PhpStorm.
// * User: ddiallo
// * Date: 9/9/19
// * Time: 4:10 PM
// */
//
//namespace Drupal\it_indicators_generator\Util;
//
//class ItIndicatorsGenUtility {
//
//  //Elements
//  public static function getElements() {
//    $elements = [
//      "coc" => [
//        'biginning' => 154,
//        'ending' => 155,
//        'dispensed' => 162,
//      ],
//      '1-rod' => [
//        'biginning' => 156,
//        'ending' => 157,
//        'dispensed' => 163,
//      ],
//      '2-rod' => [
//        'biginning' => 158,
//        'ending' => 159,
//        'dispensed' => 164,
//      ],
//      'injectables' => [
//        'biginning' => 160,
//        'ending' => 161,
//        'dispensed' => 160,
//      ],
//    ];
//
//    return $elements;
//  }
//  //Get the last three month from period from Util
//  public function getLastThreeMonths($period){
//
//    $previousMonths = [
//      '1' => '',
//      '2' => '',
//      '3' => ''
//    ];
//
//      //      $month_number = substr($tname, 4, 2);
//      $month_number = substr($period, 4, 2);
//      $year = substr($period, 0, 4);
//      $year = substr($year, 0, 4);
//      $month_number = intval($month_number);
//      $year = intval($year);
//      $month_3 = $month_number - 1;
//    $month_2 = $month_number - 2;
//    $month_1 = $month_number - 3;
//    $previousMonths['3'] = $this->getMonth($month_3, $year);
//    $previousMonths['2'] = $this->getMonth($month_2, $year);
//    $previousMonths['1'] = $this->getMonth($month_1, $year);
//
//  return $previousMonths;
//  }
//
//  public function getMonth($month_num, $year_int) {
//    $month = $month_num;
//    $year = $year_int;
//    if ($month == 0) {
//      $month = 12;
//      $year = $year - 1;
//    }
//    elseif ($month == -1) {
//      $month = 11;
//      $year = $year - 1;
//    }
//    elseif ($month == -2) {
//      $month = 10;
//      $year = $year - 1;
//    }
//
//    if ($month < 10) {
//       $period = $year . '0' . $month;
//    }
//    else {
//      $period = $year . $month;
//    }
//
//    return $period;
//  }
//
//  public function getTermName($tid) {
//
//    $query = \Drupal::database()->select('taxonomy_term_field_data', 'td');
//    $query->addField('td', 'name');
//    $query->condition('td.tid', $tid);
//    $term = $query->execute();
//    $tname = $term->fetchField();
//
//    return $tname;
//  }
//
//  //Get the elements and orgUnit from Util
//
//
//}