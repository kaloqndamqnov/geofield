<?php

/**
 * @file
 * Contains \Drupal\geofield\Plugin\Field\FieldWidget\GeofieldDefaultWidget.
 */

namespace Drupal\geofield\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Widget implementation of the 'geofield_default' widget.
 *
 * @FieldWidget(
 *   id = "geofield_widget_default",
 *   label = @Translation("Geofield"),
 *   field_types = {
 *     "geofield"
 *   },
 *   settings = {}
 * )
 */
class GeofieldDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldInterface $items, $delta, array $element, $langcode, array &$form, array &$form_state) {
    $element += array(
      '#type' => 'textarea',
      '#default_value' => $items[$delta]->value ?: NULL,
    );
    return array('value' => $element);
  }
}
