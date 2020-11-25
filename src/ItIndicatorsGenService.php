<?php
/**
 * Created by PhpStorm.
 * User: ddiallo
 * Date: 2/12/20
 * Time: 1:00 PM
 */

namespace Drupal\it_indicators_generator;

use Drupal\Core\Config\ConfigFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\ConnectException;
use Drupal\Core\Entity;
use Drupal\it_indicators_generator\Util\ItIndicatorsGenUtility;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class ItDataPullingService
 *
 * @package Drupal\it_data_pulling
 */
class ItIndicatorsGenService implements ItIndicatorsGenServiceInterface {

  private $period;

  private $elements;

  private $lastThreeMonths;

  private $teams;


  /**
   * Constructs a new ItDataPullingService object.
   */
  public function __construct() {

    $config = \Drupal::service('config.factory')
      ->getEditable('it_indicators_gen.settings');

    $this->period = $config->get('period');
    $this->elements = $this->getElements();
    $this->teams = $this->getItCodes();
    $this->lastThreeMonths = $this->getLastThreeMonths($this->period);
  }

  /**
   *
   */
  public function getValues($team) {
    $data = [];
    foreach ($this->teams as $key => $value) {
      $data[$key] = $this->getIndicators($value, $this->elements, $this->lastThreeMonths);
    }
    return $data;
  }

  public function getIndicators($team, $elements, $lastMonths) {

    //Stock: Ending(3)/Dispensed(1+2+3)
    //apd: Ending(2)-Beginning(3)/Ending(2)
    $data = [];
    $indicators = [
      'stock' => 0,
      'apd' => 0,
    ];

    //Getting value
    $entity = \Drupal::entityTypeManager()
      ->getStorage('node');


    foreach ($elements as $key => $value) {


      $ending_3_title = $this->getContentTitle($value['ending']) . "-" . $this->getContentTitle($team) . "-" . $lastMonths['3'];
      $my_data = $entity->loadByProperties(['title' => $ending_3_title]);
      if (!empty($my_data)) {
        $my_node = $entity->load(key($my_data));
        $ending_3 = $my_node->get('field_data')->getValue()[0]['value'];
      }
      else {
        $ending_3 = 0;
      }


      $dispensed_3_title = $this->getContentTitle($value['dispensed']) . "-" . $this->getContentTitle($team) . "-" . $lastMonths['3'];
      $my_data = $entity->loadByProperties(['title' => $dispensed_3_title]);
      if (!empty($my_data)) {
        $my_node = $entity->load(key($my_data));
        $dispensed_3 = $my_node->get('field_data')->getValue()[0]['value'];
      }
      else {
        $dispensed_3 = 0;
      }


      $dispensed_2_title = $this->getContentTitle($value['dispensed']) . "-" . $this->getContentTitle($team) . "-" . $lastMonths['2'];
      $my_data = $entity->loadByProperties(['title' => $dispensed_2_title]);
      if (!empty($my_data)) {
        $my_node = $entity->load(key($my_data));
        $dispensed_2 = $my_node->get('field_data')->getValue()[0]['value'];
      }
      else {
        $dispensed_2 = 0;
      }

      $dispensed_1_title = $this->getContentTitle($value['dispensed']) . "-" . $this->getContentTitle($team) . "-" . $lastMonths['1'];
      $my_data = $entity->loadByProperties(['title' => $dispensed_1_title]);
      if (!empty($my_data)) {
        $my_node = $entity->load(key($my_data));
        $dispensed_1 = $my_node->get('field_data')->getValue()[0]['value'];
      }
      else {
        $dispensed_1 = 0;
      }

      $ending_2_title = $this->getContentTitle($value['ending']) . "-" . $this->getContentTitle($team) . "-" . $lastMonths['2'];
      $my_data = $entity->loadByProperties(['title' => $ending_2_title]);
      if (!empty($my_data)) {
        $my_node = $entity->load(key($my_data));
        $ending_2 = $my_node->get('field_data')->getValue()[0]['value'];
      }
      else {
        $ending_2 = 0;
      }

      $beginning_3_title = $this->getContentTitle($value['beginning']) . "-" . $this->getContentTitle($team) . "-" . $lastMonths['3'];
      $my_data = $entity->loadByProperties(['title' => $beginning_3_title]);
      if (!empty($my_data)) {
        $my_node = $entity->load(key($my_data));
        $beginning_3 = $my_node->get('field_data')->getValue()[0]['value'];
      }
      else {
        $beginning_3 = 0;
      }

      if (($dispensed_3 + $dispensed_2 + $dispensed_1) !== 0) {
        $stock = ($ending_3 / ($dispensed_3 + $dispensed_2 + $dispensed_1));
        $indicators['stock'] = round($stock, 2);
      }
      else {
        $indicators['stock'] = 0;
      }

      if ($ending_2 !== 0) {
        $apd = (($ending_2 - $beginning_3) / $ending_2) * 100;
        $indicators['apd'] = round($apd, 2);
      }
      else {
        $indicators['apd'] = 0;
      }

      $data[$key] = $indicators;
    }

    return $data;

  }

  /**
   * Create IT indicators content
   */
  public function createIndicatorContent($data) {
    $entity = \Drupal::entityTypeManager()
      ->getStorage('basic_data');

    //drupal_set_message(json_encode($data) . " data");

    foreach ($data as $key => $value) {

      foreach ($value as $k => $v) {

        $title = "Indicators: " . $k . "-" . $this->getContentTitle($key) . "-" . $this->lastThreeMonths['3'];

        $my_ind = $entity->loadByProperties(['name' => $title]);

        if (!empty($my_ind)) {
          $entity->delete($my_ind);
        }
        $entity_definition = \Drupal::entityTypeManager()
          ->getDefinition('basic_data');
        $values = [
          $entity_definition->getKey('bundle') => 'dhis2_indicators',
          'name' => $title,
          'data' => 'Some data',
          'field_team' => $key,
          'field_period' => $this->getTermId($this->lastThreeMonths['3']),
          'field_type' => $this->getTermId($k),
          'field_stock' => $v['stock'],
          'field_apd' => $v['apd'],
        ];
        $entity->create($values)->save();
      }
    }
  }

  //Elements
  public function getElements() {
    $elements = [
      "COC" => [
        'beginning' => 154,
        'ending' => 155,
        'dispensed' => 162,
      ],
      '1-Rod' => [
        'beginning' => 156,
        'ending' => 157,
        'dispensed' => 163,
      ],
      '2-Rod' => [
        'beginning' => 158,
        'ending' => 159,
        'dispensed' => 164,
      ],
      'Injectables' => [
        'beginning' => 160,
        'ending' => 161,
        'dispensed' => 160,
      ],
    ];

    return $elements;
  }

  public function getItCodes() {
    return [
      '87' => 87,
      '88' => 88,
      '89' => 89,
      '90' => 90,
      '91' => 91,
      '92' => 92,
      '93' => 93,
      '94' => 94,
      '95' => 95,
      '96' => 96,
      '97' => 97,
      '98' => 98,
      '99' => 99,
      '100' => 100,
      '101' => 101,
      '102' => 102,
      '103' => 103,
      '104' => 104,
      '105' => 105,
      '106' => 106,
      '107' => 107,
      '108' => 108,
      '109' => 109,
      '110' => 110,
      '111' => 111,
      '112' => 112,
      '113' => 113,
      '114' => 114,
      '115' => 115,
      '116' => 116,
      '117' => 117,
      '118' => 118,
      '119' => 119,
      '120' => 120,
      '121' => 121,
      '122' => 122,
      '123' => 123,
      '124' => 124,
      '125' => 125,
      '126' => 126,
      '127' => 127,
      '128' => 128,
      '129' => 129,
      '130' => 130,
      '131' => 131,
      '132' => 132,
      '133' => 133,
      '134' => 134,
      '135' => 135,
      '136' => 136,
      '137' => 137,
      '138' => 138,
      '139' => 139,
      '140' => 140,
      '141' => 141,
      '142' => 142,
      '143' => 143,
      '144' => 144,
      '145' => 145,
      '146' => 146,
      '147' => 147,
      '148' => 148,
      '149' => 149,
      '150' => 150,
      '151' => 151,
      '152' => 152,
      '153' => 153,
    ];
  }


  //Get the last three month from period from Util
  public function getLastThreeMonths($period) {

    $previousMonths = [
      '1' => '',
      '2' => '',
      '3' => '',
    ];

    //      $month_number = substr($tname, 4, 2);
    $month_number = substr($period, 4, 2);
    $year = substr($period, 0, 4);
    $month_number = intval($month_number);
    $year = intval($year);
    $month_3 = $month_number;
    $month_2 = $month_number - 1;
    $month_1 = $month_number - 2;
    $previousMonths['3'] = $this->getMonth($month_3, $year);
    $previousMonths['2'] = $this->getMonth($month_2, $year);
    $previousMonths['1'] = $this->getMonth($month_1, $year);

    return $previousMonths;
  }

  public function getMonth($month_num, $year_int) {
    $month = $month_num;
    $year = $year_int;
    if ($month == 0) {
      $month = 12;
      $year = $year - 1;
    }
    elseif ($month == -1) {
      $month = 11;
      $year = $year - 1;
    }

    if ($month < 10) {
      $period = $year . '0' . $month;
    }
    else {
      $period = $year . $month;
    }

    return $period;
  }

  public function getTermName($tid) {

    $query = \Drupal::database()->select('taxonomy_term_field_data', 'td');
    $query->addField('td', 'name');
    $query->condition('td.tid', $tid);
    $term = $query->execute();
    $tname = $term->fetchField();

    return $tname;
  }

  public function getTermId($tname) {

    $query = \Drupal::database()->select('taxonomy_term_field_data', 'td');
    $query->addField('td', 'tid');
    $query->condition('td.name', $tname);
    $term = $query->execute();
    $tid = $term->fetchField();

    return $tid;
  }

  public function getContentTitle($nid) {

    $query = \Drupal::database()->select('node_field_data', 'td');
    $query->addField('td', 'title');
    $query->condition('td.nid', $nid);
    $result = $query->execute();
    $tname = $result->fetchField();

    return $tname;
  }

}