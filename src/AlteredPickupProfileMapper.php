<?php

declare(strict_types=1);

namespace Drupal\commerce_shipping_pickup_extra;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\profile\Entity\ProfileInterface;

/**
 * An example dealers implementation to override.
 */
class AlteredPickupProfileMapper implements AlteredPickupProfileMapperInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function populateProfile(ProfileInterface $profile): void {
    $dealers = $this->getDealers();
    $id = $profile->getData('pickup_location_id');

    if (isset($dealers[$id])) {
      $address = $dealers[$id];
      unset($address['shipping_method']);
    }
    else {
      $address = NULL;
    }

    $profile->set('address', $address);
  }

  /**
   * {@inheritdoc}
   */
  public function buildFormElement(ProfileInterface $profile, $shipping_method): array {
    $dealers = $this->getDealers(FALSE);

    $options = [];
    foreach ($dealers as $dealer_id => $dealer) {
      if (!empty($shipping_method) && $dealer['shipping_method'] != $shipping_method) {
        continue;
      }
      $options[$dealer_id] = $dealer['organization'];
    }

    return [
      '#type'          => 'select',
      '#title'         => $this->t('Select a pickup point:'),
      '#default_value' => $profile->getData('pickup_location_id'),
      '#options'       => $options,
    ];
  }

  public function getDealers($name_without_address = TRUE) {
    $dealers = [];

    $query = \Drupal::entityTypeManager()
      ->getStorage('pickup_item')
      ->getQuery();

    $query->condition('status', 1);
    $query->sort('name', 'ASC');
    $query->sort('address.locality', 'ASC');
    $query->sort('address.address_line1', 'ASC');

    $ids = $query->execute();

    /** @var \Drupal\commerce_shipping_pickup_extra\Entity\PickupItemInterface[] $entities */
    $entities = \Drupal::entityTypeManager()
      ->getStorage('pickup_item')
      ->loadMultiple($ids);

    foreach ($entities as $entity) {
      $method = $entity->get('method')->getValue();
      $method = reset($method);

      $address = $entity->get('address')->getValue();
      $address = reset($address);

      $dealers[$entity->id()] = [
        'shipping_method' => $method['value'],
        'country_code'    => $address['country_code'],
        'locality'        => $address['locality'],
        'postal_code'     => $address['postal_code'],
        'address_line1'   => $address['address_line1'],
        'organization'    => $name_without_address ? $entity->getName() : $entity->getNameWithAddress(),
      ];
    }

    return $dealers;
  }

}
