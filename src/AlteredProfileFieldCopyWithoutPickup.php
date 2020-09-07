<?php

declare(strict_types=1);

namespace Drupal\commerce_shipping_pickup_extra;

use Drupal\commerce_shipping\ProfileFieldCopy;
use Drupal\commerce_shipping_pickup_extra\Plugin\Commerce\CheckoutPane\AlteredPickupCapableShippingInformation;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * If the a pickup method is selected, disable billing same as shipping.
 */
final class AlteredProfileFieldCopyWithoutPickup extends ProfileFieldCopy {

  /**
   * {@inheritdoc}
   */
  public function supportsForm(array &$inline_form, FormStateInterface $form_state) {
    $parent = parent::supportsForm($inline_form, $form_state);
    $order = self::getOrder($form_state);

    if ($order) {
      $summary = \Drupal::service('commerce_shipping.order_shipment_summary')->build($order);
      if (!empty($summary[0])) {
        /** @var \Drupal\commerce_shipping\Entity\Shipment */
        $shipment = $summary[0]['shipment']['#commerce_shipment'];
        $shipping_method = $shipment->getShippingMethod();
        $shipping_method = $shipping_method ? $shipping_method->getPlugin()->getPluginId() : NULL;
      }

      if (!empty($shipping_method) && strpos($shipping_method, 'pickup') !== FALSE) {
        return FALSE;
      }
    }

    if (empty($form_state->getCompleteForm()['pickup_capable_shipping_information'])) {
      return $parent;
    }

    $form = $form_state->getCompleteForm()['pickup_capable_shipping_information'];

    if (AlteredPickupCapableShippingInformation::isPickupSelected($form, $form_state, $order)) {
      return FALSE;
    }

    return $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$inline_form, FormStateInterface $form_state) {
    parent::alterForm($inline_form, $form_state);

    if (isset($inline_form['copy_fields']['enable'])) {
      $inline_form['copy_fields']['enable']['#prefix'] = '<div class="field--label">' . $this->t('Billing information') . '</div>';
    }
  }

}
