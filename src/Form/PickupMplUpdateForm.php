<?php

namespace Drupal\commerce_shipping_pickup_extra\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PickupMplUpdateForm.
 */
class PickupMplUpdateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pickup_mpl_update_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Update'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $msg = \Drupal::messenger();

    _commerce_shipping_pickup_extra_update_mpl_post_point();
    _commerce_shipping_pickup_extra_update_mpl_post_office();
    _commerce_shipping_pickup_extra_update_mpl_pickup_point();

    $msg->addMessage($this->t('Update was successfully.'), 'status', TRUE);
  }

}
