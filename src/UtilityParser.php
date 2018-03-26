<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 23/03/2018
 * Time: 14:55
 */

namespace Drupal\entity_parser;


class UtilityParser {

  public function is_field_ready($entity, $field) {
    $bool = FALSE;
    if (is_object($entity) && $entity->hasField($field)) {
      $field_value = $entity->get($field)->getValue();
      if (!empty($field_value)) {
        $bool = TRUE;
      }
    }
    return $bool;
  }

}