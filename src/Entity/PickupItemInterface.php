<?php

namespace Drupal\commerce_shipping_pickup_extra\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining Pickup item entities.
 *
 * @ingroup commerce_shipping_pickup_extra
 */
interface PickupItemInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Pickup item name.
   *
   * @return string
   *   Name of the Pickup item.
   */
  public function getName();

  /**
   * Sets the Pickup item name.
   *
   * @param string $name
   *   The Pickup item name.
   *
   * @return \Drupal\commerce_shipping_pickup_extra\Entity\PickupItemInterface
   *   The called Pickup item entity.
   */
  public function setName($name);

  /**
   * Gets the Pickup item name with address.
   *
   * @return string
   *   Name of the Pickup item with address.
   */
  public function getNameWithAddress();

  /**
   * Sets the Pickup item name with address.
   *
   * @param string $name
   *   The Pickup item name with address.
   *
   * @return \Drupal\commerce_shipping_pickup_extra\Entity\PickupItemInterface
   *   The called Pickup item entity.
   */
  public function setNameWithAddress($name);

  /**
   * Gets the Pickup item creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Pickup item.
   */
  public function getCreatedTime();

  /**
   * Sets the Pickup item creation timestamp.
   *
   * @param int $timestamp
   *   The Pickup item creation timestamp.
   *
   * @return \Drupal\commerce_shipping_pickup_extra\Entity\PickupItemInterface
   *   The called Pickup item entity.
   */
  public function setCreatedTime($timestamp);

}
