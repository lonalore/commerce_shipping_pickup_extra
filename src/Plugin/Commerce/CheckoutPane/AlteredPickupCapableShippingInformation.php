<?php

declare(strict_types=1);

namespace Drupal\commerce_shipping_pickup_extra\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\Plugin\Commerce\CheckoutPane\ShippingInformation;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the shipping information pane.
 *
 * Collects the shipping profile, then the information for each shipment.
 * Assumes that all shipments share the same shipping profile.
 *
 * @CommerceCheckoutPane(
 *   id = "pickup_capable_shipping_information",
 *   label = @Translation("Shipping information"),
 *   wrapper_element = "fieldset",
 * )
 */
final class AlteredPickupCapableShippingInformation extends ShippingInformation {

  /**
   * Sadly this functions code had to be copy pasted with only few replacements.
   *
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $store = $this->order->getStore();

    $available_countries = [];
    foreach ($store->get('shipping_countries') as $country_item) {
      $available_countries[] = $country_item->value;
    }

    $pickup_method = NULL;

    // Pickup custom: select inline form.
    if (!self::isPickupSelected($pane_form, $form_state, $this->order)) {
      /** @var \Drupal\commerce\Plugin\Commerce\InlineForm\EntityInlineFormInterface $inline_form */
      $inline_form = $this->inlineFormManager->createInstance('customer_profile', [
        'profile_scope'       => 'shipping',
        'available_countries' => $available_countries,
        'address_book_uid'    => $this->order->getCustomerId(),
        // Don't copy the profile to address book until the order is placed.
        'copy_on_save'        => FALSE,
      ], $this->getShippingProfilePickup());
    }
    else {
      $inline_form = $this->inlineFormManager->createInstance('pickup_profile', [
        'profile_scope'       => 'shipping',
        'available_countries' => $available_countries,
      ], $this->getShippingProfilePickup(TRUE));

      $pickup_method = self::getShippingMethod($pane_form, $form_state, $this->order);

      if (strpos($pickup_method, '--') !== FALSE) {
        $pickup_method = explode('--', $pickup_method);
        $pickup_method = isset($pickup_method[1]) ? $pickup_method[1] : NULL;
      }
    }

    $pane_form['shipping_profile'] = [
      '#parents'         => array_merge($pane_form['#parents'], ['shipping_profile']),
      '#inline_form'     => $inline_form,
      '#shipping_method' => $pickup_method,
    ];

    $pane_form['shipping_profile'] = $inline_form->buildInlineForm($pane_form['shipping_profile'], $form_state);

    // The shipping_profile should always exist in form state (and not just
    // after "Recalculate shipping" is clicked).
    if (!$form_state->has('shipping_profile')) {
      $form_state->set('shipping_profile', $inline_form->getEntity());
    }

    $pane_form['removed_shipments'] = [
      '#type'  => 'value',
      '#value' => [],
    ];

    $pane_form['shipments'] = [
      '#type'   => 'container',
      // Pickup custom: place at top.
      '#weight' => -999,
    ];

    $shipping_profile = $form_state->get('shipping_profile');
    $shipments = $this->order->get('shipments')->referencedEntities();
    $recalculate_shipping = $form_state->get('recalculate_shipping');
    $force_packing = empty($shipments) && $this->canCalculateRates($shipping_profile);

    if ($recalculate_shipping || $force_packing) {
      // We're still relying on the packer manager for packing the order since
      // we don't want the shipments to be saved for performance reasons.
      // The shipments are saved on pane submission.
      [
        $shipments,
        $removed_shipments,
      ] = $this->packerManager->packToShipments($this->order, $shipping_profile, $shipments);

      // Store the IDs of removed shipments for submitPaneForm().
      $pane_form['removed_shipments']['#value'] = array_map(function ($shipment) {
        /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
        return $shipment->id();
      }, $removed_shipments);
    }

    $single_shipment = count($shipments) === 1;
    foreach ($shipments as $index => $shipment) {
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      $pane_form['shipments'][$index] = [
        '#parents'       => array_merge($pane_form['#parents'], [
          'shipments',
          $index,
        ]),
        '#array_parents' => array_merge($pane_form['#parents'], [
          'shipments',
          $index,
        ]),
        '#type'          => $single_shipment ? 'container' : 'fieldset',
        '#title'         => $shipment->getTitle(),
      ];

      $form_display = EntityFormDisplay::collectRenderDisplay($shipment, 'checkout');
      $form_display->removeComponent('shipping_profile');
      $form_display->buildForm($shipment, $pane_form['shipments'][$index], $form_state);
      $pane_form['shipments'][$index]['#shipment'] = $shipment;

      // Pickup custom: Add ajax.
      $widget = &$pane_form['shipments'][$index]['shipping_method']['widget'][0];

      $widget['#ajax'] = [
        'callback' => [self::class, 'ajaxRefreshForm'],
        'element'  => $widget['#field_parents'],
      ];

      $widget['#limit_validation_errors'] = [
        $widget['#field_parents'],
      ];
    }

    return $pane_form;
  }

  /**
   * Determines if a pickup shipping method is selected.
   *
   * This function is static, to not duplicate code.
   * Also used in ProfileFieldCopyWithoutPickup.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   */
  public static function isPickupSelected(array $form, FormStateInterface $form_state, OrderInterface $order): bool {
    $shipping_method = self::getShippingMethod($form, $form_state, $order);
    return strpos($shipping_method, 'pickup') !== FALSE;
  }

  /**
   * Selected shipping method.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return string
   */
  public static function getShippingMethod(array $form, FormStateInterface $form_state, OrderInterface $order): string {
    $shipping_method = NestedArray::getValue(
      $form_state->getUserInput(),
      array_merge($form['#parents'], ['shipments', 0, 'shipping_method', 0])
    );

    if (empty($shipping_method)) {
      $summary = \Drupal::service('commerce_shipping.order_shipment_summary')->build($order);
      if (!empty($summary[0])) {
        /** @var \Drupal\commerce_shipping\Entity\Shipment */
        $shipment = $summary[0]['shipment']['#commerce_shipment'];
        $shipping_method = $shipment->getShippingMethod();
        $shipping_method = $shipping_method ? $shipping_method->getPlugin()->getPluginId() : NULL;
      }
    }

    return !empty($shipping_method) ? $shipping_method : '';
  }

  /**
   * Gets the shipping profile and clears address data on switch.
   *
   * @param bool $isPickup
   *   Is pickup store profile.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\profile\Entity\ProfileInterface
   *   The retrived profile.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getShippingProfilePickup(bool $isPickup = FALSE) {
    $profile = $this->getShippingProfile();

    if (!$isPickup && $profile->getData('pickup_location_id', FALSE) !== FALSE) {
      $profile = $this->entityTypeManager->getStorage('profile')->create([
        'type' => $profile->bundle(),
        'uid'  => 0,
      ]);
    }
    elseif ($isPickup && $profile->getData('pickup_location_id', FALSE) === FALSE) {
      $profile = $this->entityTypeManager->getStorage('profile')->create([
        'type' => $profile->bundle(),
        'uid'  => 0,
      ]);
    }

    return $profile;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    $summary = [];
    if ($this->isVisible()) {
      $summary = $this->orderShipmentSummary->build($this->order, 'checkout');
    }
    return $summary;
  }

}
