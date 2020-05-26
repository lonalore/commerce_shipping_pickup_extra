<?php

/**
 * @file
 * Contains pickup_item.page.inc.
 *
 * Page callback for Pickup item entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Pickup item templates.
 *
 * Default template: pickup_item.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_pickup_item(array &$variables) {
  // Fetch PickupItem Entity Object.
  $pickup_item = $variables['elements']['#pickup_item'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
