<?php

namespace Drupal\commerce_shipping_pickup_extra\Entity\Views;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Pickup item entities.
 */
class PickupItemViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
