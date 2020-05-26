<?php

declare(strict_types=1);

namespace Drupal\commerce_shipping_pickup_extra;

use Drupal\commerce_shipping\ProfileFieldCopy;
use Drupal\commerce_shipping_pickup_extra\Plugin\Commerce\CheckoutPane\AlteredPickupCapableShippingInformation;
use Drupal\Core\Form\FormStateInterface;

/**
 * If the a pickup method is selected, disable billing same as shipping.
 */
final class AlteredProfileFieldCopyWithoutPickup extends ProfileFieldCopy {

  /**
   * {@inheritdoc}
   */
  public function supportsForm(array &$inline_form, FormStateInterface $form_state) {
    $parent = parent::supportsForm($inline_form, $form_state);

    if (empty($form_state->getCompleteForm()['pickup_capable_shipping_information'])) {
      return $parent;
    }

    $order = self::getOrder($form_state);

    $form = $form_state->getCompleteForm()['pickup_capable_shipping_information'];

    if (AlteredPickupCapableShippingInformation::isPickupSelected($form, $form_state, $order)) {
      return FALSE;
    }

    return $parent;
  }

}
