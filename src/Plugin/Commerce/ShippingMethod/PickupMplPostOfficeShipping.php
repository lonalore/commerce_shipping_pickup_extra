<?php

declare(strict_types=1);

namespace Drupal\commerce_shipping_pickup_extra\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Drupal\Core\Form\FormStateInterface;
use Drupal\state_machine\WorkflowManagerInterface;

/**
 * Provides the free shipping method.
 *
 * @CommerceShippingMethod(
 *   id = "pickup_mpl_post_office",
 *   label = @Translation("Pickup shipping - MPL Post Office"),
 * )
 */
final class PickupMplPostOfficeShipping extends ShippingMethodBase {

  /**
   * Constructs a new free shipping object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_shipping\PackageTypeManagerInterface $package_type_manager
   *   The package type manager.
   * @param \Drupal\state_machine\WorkflowManagerInterface $workflow_manager
   *   The workflow manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    PackageTypeManagerInterface $package_type_manager,
    WorkflowManagerInterface $workflow_manager
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $package_type_manager,
      $workflow_manager
    );

    $this->services['pickup_mpl_post_office'] = new ShippingService('pickup_mpl_post_office', $this->configuration['rate_label']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'rate_label'       => '',
        'rate_description' => '',
        'rate_amount'      => NULL,
        'services'         => ['pickup_mpl_post_office'],
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $amount = $this->configuration['rate_amount'];
    // A bug in the plugin_select form element causes $amount to be incomplete.
    if (isset($amount) && !isset($amount['number'], $amount['currency_code'])) {
      $amount = NULL;
    }

    $form['rate_label'] = [
      '#type'          => 'textfield',
      '#title'         => t('Rate label'),
      '#description'   => t('Shown to customers when selecting the rate.'),
      '#default_value' => $this->configuration['rate_label'],
      '#required'      => TRUE,
    ];
    $form['rate_description'] = [
      '#type'          => 'textfield',
      '#title'         => t('Rate description'),
      '#description'   => t('Provides additional details about the rate to the customer.'),
      '#default_value' => $this->configuration['rate_description'],
    ];
    $form['rate_amount'] = [
      '#type'          => 'commerce_price',
      '#title'         => t('Rate amount'),
      '#default_value' => $amount,
      '#required'      => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['rate_label'] = $values['rate_label'];
      $this->configuration['rate_description'] = $values['rate_description'];
      $this->configuration['rate_amount'] = $values['rate_amount'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateRates(ShipmentInterface $shipment) {
    $rates = [];
    $rates[] = new ShippingRate([
      'shipping_method_id' => $this->parentEntity->id(),
      'service'            => $this->services['pickup_mpl_post_office'],
      'amount'             => Price::fromArray($this->configuration['rate_amount']),
      'description'        => $this->configuration['rate_description'],
    ]);

    return $rates;
  }

}
