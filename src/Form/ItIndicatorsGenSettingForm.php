<?php
/**
 * Created by PhpStorm.
 * User: ddiallo
 * Date: 2/12/20
 * Time: 1:28 PM
 */

namespace Drupal\it_indicators_generator\Form;

use \Drupal\Core\Form\ConfigFormBase;
use \Drupal\Core\Form\FormStateInterface;
use Drupal\it_indicators_generator\ItIndicatorsGenService;

class ItIndicatorsGenSettingForm extends configFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'it_indicators_gen_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['it_indicators_gen.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('it_indicators_gen.settings');

    $form['period'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The indicators period'),
      '#size' => 10,
      '#maxlength' => 10,
      '#default_value' => $config->get('period'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);

  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = \Drupal::service('config.factory')
      ->getEditable('it_indicators_gen.settings');
    $config->set('period', $form_state->getValue('period'));
    $config->save();

    parent::submitForm($form, $form_state);
  }
}