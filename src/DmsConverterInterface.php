<?php

namespace Drupal\geofield;

interface DmsConverterInterface {
  /**
   * Transforms a DMS point to a decimal one
   *
   * @param \Drupal\geofield\DmsPoint $point
   *   The DMS Point to transform.
   *
   * @return \Point
   *   The equivalent Decimal Point object.
   */
  public static function DmsToDecimal(DmsPoint $point);

  /**
   * @param \Point $point
   *   The Decimal Point to transform.
   *
   * @return \Drupal\geofield\DmsPoint
   *   The equivalent DMS Point object.
   */
  public static function DecimalToDms(\Point $point);
}