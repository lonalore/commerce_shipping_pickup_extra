<?php

declare(strict_types=1);

namespace Drupal\commerce_shipping_pickup_extra\Plugin\Commerce\InlineForm;

use Drupal\commerce\Plugin\Commerce\InlineForm\EntityInlineFormBase;
use Drupal\commerce_shipping_pickup\PickupProfileMapperInterface;
use Drupal\commerce_shipping_pickup_extra\AlteredPickupProfileMapperInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\profile\Entity\ProfileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an inline form for managing a customer profile.
 *
 * Allows copying values to and from the customer's address book.
 *
 * Supports two modes, based on the profile type setting:
 * - Single: The customer can have only a single profile of this type.
 * - Multiple: The customer can have multiple profiles of this type.
 *
 * @CommerceInlineForm(
 *   id = "pickup_profile",
 *   label = @Translation("Customer profile"),
 * )
 */
final class AlteredPickupProfile extends EntityInlineFormBase {

  /**
   * The profile mapper.
   *
   * @var \Drupal\commerce_shipping_pickup\PickupProfileMapperInterface
   */
  public $pickupProfileMapper;

  /**
   * Constructs a new CustomerProfile object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_shipping_pickup_extra\AlteredPickupProfileMapperInterface $pickupProfileMapper
   *   The pickup profile mapper.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AlteredPickupProfileMapperInterface $pickupProfileMapper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->pickupProfileMapper = $pickupProfileMapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('commerce_shipping_prickup.default_profil_mapper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      // Unique. Passed along to field widgets. Examples: 'billing', 'shipping'.
      'profile_scope' => 'shipping',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildInlineForm(array $inline_form, FormStateInterface $form_state) {
    $inline_form = parent::buildInlineForm($inline_form, $form_state);

    assert($this->entity instanceof ProfileInterface);
    assert($this->pickupProfileMapper instanceof PickupProfileMapperInterface || $this->pickupProfileMapper instanceof AlteredPickupProfileMapperInterface);

    if ($this->pickupProfileMapper instanceof AlteredPickupProfileMapperInterface) {
      $shipping_method = isset($inline_form['#shipping_method']) ? $inline_form['#shipping_method'] : NULL;
      $address = $this->pickupProfileMapper->buildFormElement($this->entity, $shipping_method);
    }
    else {
      $address = $this->pickupProfileMapper->buildFormElement($this->entity);
    }

    $inline_form['pickup_dealer'] = $address;

    return $inline_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitInlineForm(array &$inline_form, FormStateInterface $form_state) {
    parent::submitInlineForm($inline_form, $form_state);
    assert($this->pickupProfileMapper instanceof PickupProfileMapperInterface || $this->pickupProfileMapper instanceof AlteredPickupProfileMapperInterface);
    $id = NestedArray::getValue($form_state->getValues(), $inline_form['#parents']);
    $this->entity->setData('pickup_location_id', $id['pickup_dealer']);
    // Do not save a dealer pickup address into the users address book.
    $this->entity->unsetData('address_book_profile_id');
    $this->entity->unsetData('copy_to_address_book');
    $this->pickupProfileMapper->populateProfile($this->entity);
    $this->entity->save();
  }

}
