<?php

namespace Drupal\commerce_shipping_pickup_extra\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'commerce_shipping_pickup_extra.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('commerce_shipping_pickup_extra.settings');

    $form['cron_interval'] = [
      '#type'          => 'select',
      '#title'         => $this->t('CRON interval'),
      '#options'       => [
        0     => $this->t('Disabled'),
        3600  => $this->t('Hourly'),
        86400 => $this->t('Daily'),
      ],
      '#size'          => 1,
      '#default_value' => $config->get('cron_interval'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('commerce_shipping_pickup_extra.settings')
      ->set('cron_interval', $form_state->getValue('cron_interval'))
      ->save();
  }

}
