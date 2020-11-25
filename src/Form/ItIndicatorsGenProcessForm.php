<?php
/**
 * Created by PhpStorm.
 * User: ddiallo
 * Date: 2/12/20
 * Time: 1:44 PM
 */

namespace Drupal\it_indicators_generator\Form;

use \Drupal\Core\Form\ConfirmFormBase;
use \Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\it_indicators_generator\ItIndicatorsGenService;
use Drupal\it_indicators_generator\Util\ItIndicatorsGenUtility;


class ItIndicatorsGenProcessForm extends confirmFormBase {

  //todo get params
  public function getFormId() {
    return 'it_indicators_gen_process';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['header']['#markup'] = t('Ready? This will generate the IT indicators for this period.');
    return parent::buildForm($form, $form_state);
  }

  public function getQuestion() {

    return NULL;
    //return $this->t('Ready? This will test the connexion to the remote server.');
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $itIndicatorsGenService = new ItIndicatorsGenService();
    $teams = $itIndicatorsGenService->getItCodes();

    $data = $itIndicatorsGenService->getValues($teams);
    $itIndicatorsGenService->createIndicatorContent($data);

    $messenger = \Drupal::messenger();
    $messenger->addMessage("DHIS2 Indicators generated");

    $form_state->setRedirect('it_indicators_gen.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('it_indicators_gen.settings');
  }

}