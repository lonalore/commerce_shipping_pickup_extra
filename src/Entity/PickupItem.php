<?php

namespace Drupal\commerce_shipping_pickup_extra\Entity;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Pickup item entity.
 *
 * @ingroup commerce_shipping_pickup_extra
 *
 * @ContentEntityType(
 *   id = "pickup_item",
 *   label = @Translation("Pickup item"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" =
 *   "Drupal\commerce_shipping_pickup_extra\Entity\ListBuilder\PickupItemListBuilder",
 *     "views_data" =
 *   "Drupal\commerce_shipping_pickup_extra\Entity\Views\PickupItemViewsData",
 *     "form" = {
 *       "default" =
 *   "Drupal\commerce_shipping_pickup_extra\Entity\Form\PickupItemForm",
 *       "add" =
 *   "Drupal\commerce_shipping_pickup_extra\Entity\Form\PickupItemForm",
 *       "edit" =
 *   "Drupal\commerce_shipping_pickup_extra\Entity\Form\PickupItemForm",
 *       "delete" =
 *   "Drupal\commerce_shipping_pickup_extra\Entity\Form\PickupItemDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" =
 *   "Drupal\commerce_shipping_pickup_extra\Entity\Routing\PickupItemHtmlRouteProvider",
 *     },
 *     "access" =
 *   "Drupal\commerce_shipping_pickup_extra\Entity\Access\PickupItemAccessControlHandler",
 *   },
 *   base_table = "pickup_item",
 *   translatable = FALSE,
 *   admin_permission = "administer pickup item entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/pickup-item/{pickup_item}",
 *     "add-form" = "/admin/commerce/pickup-item/add",
 *     "edit-form" = "/admin/commerce/pickup-item/{pickup_item}/edit",
 *     "delete-form" = "/admin/commerce/pickup-item/{pickup_item}/delete",
 *     "collection" = "/admin/commerce/pickup-item",
 *   },
 *   field_ui_base_route = "pickup_item.settings"
 * )
 */
class PickupItem extends ContentEntityBase implements PickupItemInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setSettings([
        'max_length'      => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label'  => 'above',
        'type'   => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['method'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Method'))
      ->setSettings([
        'max_length'      => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label'  => 'above',
        'type'   => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['address'] = BaseFieldDefinition::create('address')
      ->setLabel(t('Address'))
      ->setSettings([
        'default_value'   => [],
        'field_overrides' => [
          AddressField::FAMILY_NAME => [
            'override' => FieldOverride::HIDDEN,
          ],
          AddressField::GIVEN_NAME  => [
            'override' => FieldOverride::HIDDEN,
          ],
        ],
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type'  => 'address_default',
      ])
      ->setDisplayOptions('form', [
        'type' => 'address_default',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Pickup item is published.'))
      ->setDisplayOptions('form', [
        'type'   => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
