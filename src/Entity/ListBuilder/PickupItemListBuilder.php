<?php

namespace Drupal\commerce_shipping_pickup_extra\Entity\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Pickup item entities.
 *
 * @ingroup commerce_shipping_pickup_extra
 */
class PickupItemListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Pickup item ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\commerce_shipping_pickup_extra\Entity\PickupItem $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.pickup_item.edit_form',
      ['pickup_item' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
