<?php

namespace Drupal\Tests\geofield\Functional;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Tests\FieldTestBase;

/**
 * Tests the Geofield widgets.
 *
 * @group geofield
 */
class GeofieldWidgetTest extends FieldTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['geofield', 'entity_test'];

  /**
   * A field storage with cardinality 1 to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * A Field to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  protected function setUp() {
    parent::setUp();

    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => 'geofield_field',
      'entity_type' => 'entity_test',
      'type' => 'geofield',
      'settings' => [
        'backend' => 'geofield_backend_default',
      ]
    ]);
    $this->fieldStorage->save();

    $this->field = FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'bundle' => 'entity_test',
      'description' => 'Description for geofield_field',
      'required' => TRUE,
    ]);
    $this->field->save();

    // Create a web user.
    $this->drupalLogin($this->drupalCreateUser(['view test entity', 'administer entity_test content']));
  }

  /**
   * Tests the Default widget.
   */
  public function testDefaultWidget() {
    EntityFormDisplay::load('entity_test.entity_test.default')
      ->setComponent($this->fieldStorage->getName(), [
        'type' => 'geofield_default',
      ])
      ->save();

    // Create an entity.
    $entity = EntityTest::create([
      'user_id' => 1,
      'name' => $this->randomMachineName(),
    ]);
    $entity->save();

    // With no field data, no buttons are checked.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');
    $this->assertText('geofield_field');

    $edit = [
      'name[0][value]' => 'Arnedo',
      'geofield_field[0][value]' => 'POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertFieldValues($entity, 'geofield_field', ['POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))']);

    // Add invalid values.
    $random = $this->randomMachineName();
    $edit = [
      'name[0][value]' => 'Invalid',
      'geofield_field[0][value]' => $random,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('"' . $random . '" is not a valid geospatial content.');
  }

  /**
   * Tests the Lat Lon widget.
   */
  public function testLatLonWidget() {
    EntityFormDisplay::load('entity_test.entity_test.default')
      ->setComponent($this->fieldStorage->getName(), [
        'type' => 'geofield_latlon',
      ])
      ->save();

    // Create an entity.
    $entity = EntityTest::create([
      'user_id' => 1,
      'name' => $this->randomMachineName(),
    ]);
    $entity->save();

    // Check basic data.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');
    $this->assertText('geofield_field');
    $this->assertText('Latitude');
    $this->assertText('Longitude');

    // Add a valid point.
    $edit = [
      'name[0][value]' => 'Arnedo',
      'geofield_field[0][value][lat]' => 42.2257,
      'geofield_field[0][value][lon]' => -2.1021,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertFieldValues($entity, 'geofield_field', ['POINT (-2.1021 42.2257)']);

    // Add values out of range.
    $edit = [
      'name[0][value]' => 'Out of bounds',
      'geofield_field[0][value][lat]' => 92.2257,
      'geofield_field[0][value][lon]' => -200.1021,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->assertText('geofield_field: Latitude is out of bounds.');
    $this->assertText('geofield_field: Longitude is out of bounds.');

    // Add non numeric values.
    $edit = [
      'name[0][value]' => 'Not numeric',
      'geofield_field[0][value][lat]' => 'Not',
      'geofield_field[0][value][lon]' => 'Numeric',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->assertText('geofield_field: Latitude is not numeric.');
    $this->assertText('geofield_field: Longitude is not numeric.');
  }

  /**
   * Tests the bounds widget.
   */
  public function testBoundsWidget() {
    EntityFormDisplay::load('entity_test.entity_test.default')
      ->setComponent($this->fieldStorage->getName(), [
        'type' => 'geofield_bounds',
      ])
      ->save();

    // Create an entity.
    $entity = EntityTest::create([
      'user_id' => 1,
      'name' => $this->randomMachineName(),
    ]);
    $entity->save();

    // Check basic data.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');
    $this->assertText('geofield_field');
    $this->assertText('Top');
    $this->assertText('Right');
    $this->assertText('Bottom');
    $this->assertText('Left');

    // Add valid bounds.
    $edit = [
      'name[0][value]' => 'Arnedo - Valladolid',
      'geofield_field[0][value][top]' => 42.2257,
      'geofield_field[0][value][right]' => -2.1021,
      'geofield_field[0][value][bottom]' => 41.6523,
      'geofield_field[0][value][left]' => -4.7245,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertFieldValues($entity, 'geofield_field', ['POLYGON ((-2.1021 42.2257, -2.1021 41.6523, -4.7245 41.6523, -4.7245 42.2257, -2.1021 42.2257))']);

    // Add invalid bounds.
    $edit = [
      'name[0][value]' => 'Invalid',
      'geofield_field[0][value][top]' => 42.2257,
      'geofield_field[0][value][right]' => 'non numeric',
      'geofield_field[0][value][bottom]' => 45.2257,
      'geofield_field[0][value][left]' => 750,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('geofield_field: Right is not numeric.');
    $this->assertText('geofield_field: Left is out of bounds.');
    $this->assertText('geofield_field: Top must be greater than Bottom.');
  }

  /**
   * Tests the DMS widget.
   */
  public function testDmsWidget() {
    EntityFormDisplay::load('entity_test.entity_test.default')
      ->setComponent($this->fieldStorage->getName(), [
        'type' => 'geofield_dms',
      ])
      ->save();

    // Create an entity.
    $entity = EntityTest::create([
      'user_id' => 1,
      'name' => $this->randomMachineName(),
    ]);
    $entity->save();

    // Check basic data.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');
    $this->assertText('geofield_field');

    // Add valid data.
    $edit = [
      'name[0][value]' => 'Arnedo',
      'geofield_field[0][value][lat][orientation]' => 'N',
      'geofield_field[0][value][lat][degrees]' => 42,
      'geofield_field[0][value][lat][minutes]' => 13,
      'geofield_field[0][value][lat][seconds]' => 32,
      'geofield_field[0][value][lon][orientation]' => 'W',
      'geofield_field[0][value][lon][degrees]' => 2,
      'geofield_field[0][value][lon][minutes]' => 6,
      'geofield_field[0][value][lon][seconds]' => 7,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertFieldValues($entity, 'geofield_field', ['POINT (-2.1019444444444 42.225555555556)']);

    // Add invalid data.
    $edit = [
      'name[0][value]' => 'Arnedo',
      'geofield_field[0][value][lat][orientation]' => 'N',
      'geofield_field[0][value][lat][degrees]' => 72,
      'geofield_field[0][value][lat][minutes]' => 555,
      'geofield_field[0][value][lat][seconds]' => 32.5,
      'geofield_field[0][value][lon][orientation]' => 'W',
      'geofield_field[0][value][lon][degrees]' => 2,
      'geofield_field[0][value][lon][minutes]' => 'non numeric',
      'geofield_field[0][value][lon][seconds]' => 7,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->assertText('geofield_field must be lower than or equal to 59.');
    $this->assertText('geofield_field is not a valid number.');
    $this->assertText('geofield_field must be a number.');

  }

}