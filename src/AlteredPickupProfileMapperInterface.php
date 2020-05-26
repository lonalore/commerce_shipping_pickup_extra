<?php

namespace Drupal\commerce_shipping_pickup_extra;

use Drupal\profile\Entity\ProfileInterface;

/**
 * Interface of the pickup mapper.
 */
interface AlteredPickupProfileMapperInterface {

  /**
   * Populates the profile.
   *
   * @param \Drupal\profile\Entity\ProfileInterface $profile
   *   The profile to populate.
   */
  public function populateProfile(ProfileInterface $profile): void;

  /**
   * Defines the type of input field the pane should provide.
   *
   * @param \Drupal\profile\Entity\ProfileInterface $profile
   *   The profile as context.
   * @param string $shipping_method
   *
   * @return array
   */
  public function buildFormElement(ProfileInterface $profile, $shipping_method): array;

}
