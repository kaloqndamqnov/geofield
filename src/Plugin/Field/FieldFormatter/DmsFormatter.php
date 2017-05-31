<?php

namespace Drupal\geofield\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geofield\DmsConverter;

/**
 * Plugin implementation of the 'geofield_dms' formatter.
 *
 * @FieldFormatter(
 *   id = "geofield_dms",
 *   label = @Translation("DMS format"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class DmsFormatter extends FormatterBase {

  /**
   * @var \Drupal\geofield\GeoPHP\GeoPHPInterface
   *   The GeoPHP service.
   */
  protected $geophp;

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings) {
    parent::__construct($plugin_id, $plugin_definition,  $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->geophp = \Drupal::service('geofield.geophp');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'output_format' => 'dms'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['output_format'] = [
      '#title' => $this->t('Output Format'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('output_format'),
      '#options' => $this->formatOptions(),
      '#required' => TRUE,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Geospatial output format: @format', ['@format' => $this->formatOptions()[$this->getSetting('output_format')]]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $output = ['#markup' => ''];
      $geom = $this->geophp->load($item->value);
      if ($geom && $geom->getGeomType() == 'Point') {
        $dms_point = DmsConverter::decimalToDms($geom->x(), $geom->y());
        $components = [];
        foreach(['lat', 'lon'] as $component) {
          $item = $dms_point->get($component);
          if ($this->getSetting('output_format') == 'dm') {
            $item['minutes'] = number_format($item['minutes'] + ($item['seconds'] / 60), 5);
            $item['seconds'] = NULL;
          }
          $components[$component] = $item;
        }
        $output = [
          '#theme' => 'geofield_dms',
          '#components' => $components,
        ];
      }
      $elements[$delta] = $output;
    }

    return $elements;
  }

  /**
   * Helper function to get the formatter settings options.
   *
   * @return array
   *  The formatter settings options.
   */
  protected function formatOptions() {
    return [
      'dms' => $this->t('DMS Format (17° 46\'11")'),
      'dm' => $this->t('DM Format (17° 46.19214\')'),
    ];
  }
}
