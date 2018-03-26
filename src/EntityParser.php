<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 23/03/2018
 * Time: 14:49
 */

namespace Drupal\entity_parser;

class EntityParser extends AbstractEntityParser {

  public function node_parser($item, $field = [], $options = []) {
    $node = NULL;
    if (is_object($item)) {
      $node = $item;
    }
    else {
      if (is_numeric($item)) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($item);
      }
    }
    return $this->entity_parser_load($node, $field, $options);
  }

  public function taxonomy_term_parser($item, $field = [], $options = []) {
    $term = NULL;
    if (is_object($item)) {
      $term = $item;
    }
    else {
      if (is_numeric($item)) {
        $term = \Drupal::entityTypeManager()
          ->getStorage('taxonomy_term')
          ->load($item);
      }
    }
    return $this->entity_parser_load($term, $field, $options);
  }

  public function user_parser($item, $field = [], $options = []) {
    $term = NULL;
    if (is_object($item)) {
      $term = $item;
    }
    else {
      if (is_numeric($item)) {
        $term = \Drupal::entityTypeManager()->getStorage('user')->load($item);
      }
    }
    return $this->entity_parser_load($term, $field, $options);
  }
  //**
  // List of Hook Fields By name
  //*//
  public function title($entity, $field) {
    $item =$this->string($entity, $field);
    return array_shift($item);
  }

  public function tid($entity, $field) {
    $item =$this->string($entity, $field);
    return array_shift($item);
  }
  public function vid($entity, $field) {
    $vid = $entity->get($field)->getValue()[0];
    if(isset($vid['target_id'])){
    return $vid['target_id'] ;
    }else{
      return $vid ;
    }
  }
  public function nid($entity, $field) {
    $item =$this->string($entity, $field);
    return array_shift($item);
  }

  public function uuid($entity, $field) {
    $item =$this->string($entity, $field);
    return array_shift($item);
  }

  public function created($entity, $field) {
    $item =$this->string($entity, $field);
    return array_shift($item);
  }

  public function changed($entity, $field) {
    $item =$this->string($entity, $field);
    return array_shift($item);
  }

  public function sticky($entity, $field) {
    $item =$this->string($entity, $field);
    return array_shift($item);
  }


}