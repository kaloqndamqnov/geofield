<?php

namespace Drupal\Tests\geofield\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests the geofield formatters functionality.
 *
 * @group geofield
 */
class GeofieldFormatterTest extends EntityKernelTestBase {

  /**
   * The entity type used in this test.
   *
   * @var string
   */
  protected $entityType = 'entity_test';

  /**
   * The bundle used in this test.
   *
   * @var string
   */
  protected $bundle = 'entity_test';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['geofield'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    FieldStorageConfig::create([
      'field_name' => 'geofield',
      'entity_type' => $this->entityType,
      'type' => 'geofield',
      'settings' => [
        'backend' => 'geofield_backend_default',
      ]])->save();

    FieldConfig::create([
      'entity_type' => $this->entityType,
      'bundle' => $this->bundle,
      'field_name' => 'geofield',
      'label' => 'GeoField',
    ])->save();
  }

  /**
   * Tests geofield field default formatter.
   */
  public function testFormatters() {
    // Create the entity to be referenced.
    $entity = EntityTest::create(['name' => $this->randomMachineName()]);
    $value = \Drupal::service('geofield.wkt_generator')->WktGenerateGeometry();
    $entity->geofield = [
      'value' => $value,
    ];
    $entity->save();

    // Verify the geofield field formatter's render array.
    $build = $entity->get('geofield')->view(['type' => 'geofield_default']);
    \Drupal::service('renderer')->renderRoot($build[0]);
    $this->assertEquals($build[0]['#markup'], $value);
  }

}
