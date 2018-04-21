<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 23/03/2018
 * Time: 14:50
 */

namespace Drupal\entity_parser;

use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

class AbstractEntityParser {

  protected $helper;

  public function __construct() {
    $this->helper = new UtilityParser();
  }
  //*@param
  // $entity = is_object node , taxonomy_term or user
  // $fields = list of fields you want to get such as nid , uid ,title
  // $options =   array('fields_exclude' => array( 'uid' , 'created' ) ,'hook_alias'=>'json');
  //*
  public function entity_parser_load($entity, $fields = [], $options = []) {
    $item = [];
    if (is_object($entity)) {
      // default get all fields
      if (empty($fields)) {
        $fields = array_keys($entity->getFields(TRUE)); // get fields
      }
      // exculde fields
      if (isset($options["fields_exclude"])) {
        $fields = array_diff($fields, $options["fields_exclude"]);
      }
      $item = $this->entity_parser_load_default($entity, $fields,$options);


    }
    return $item;
  }


  protected function entity_parser_load_default($entity, $fields,$options) {
    $item = [];
    foreach ($fields as $key => $field) {

      if ($entity->hasField($field) && !$entity->get($field)->isEmpty()) {
        $field_type = $entity->get($field)->getFieldDefinition()->getType();
        $setting_field = $entity->get($field)
          ->getFieldDefinition()
          ->getSettings();
        $bool = TRUE;
        //hook by field type
        if (isset($field_type)) {
          $type_fun = $field_type;
          if (method_exists($this, $type_fun)) {
            $field_value = $this->{$type_fun}($entity, $field);
            $bool = FALSE;
          }
          if(isset($options['hook_alias'])){
            $alias = $options['hook_alias'];
            $type_fun = $alias . "_" . $field_type;
            if (method_exists($this, $type_fun)) {
              $field_value = $this->{$type_fun}($entity, $field);
              $bool = FALSE;
            }
          }

        }
        // hook by type and target_type
        if (isset($setting_field['target_type'])) {
          $field_target_type = $setting_field['target_type'];
          /// custom field structure
          $type_fun = $field_type . "_" . $field_target_type;
          if (method_exists($this, $type_fun)) {
            $field_value = $this->{$type_fun}($entity, $field);
            $bool = FALSE;
          }
          if(isset($options['hook_alias'])){
            $alias = $options['hook_alias'];
            $field_target_type = $setting_field['target_type'];
            $type_fun = $alias . "_" . $field_type . "_" . $field_target_type;
            if (method_exists($this, $type_fun)) {
              $field_value = $this->{$type_fun}($entity, $field);
              $bool = FALSE;
            }
          }
        }
        // hook by custom field name_machine
        $field_fun = $field;
        if (method_exists($this, $field_fun)) {
          $field_value = $this->{$field_fun}($entity, $field);
          $bool = FALSE;

        }
        if(isset($options['hook_alias'])){
          $alias = $options['hook_alias'] ;
          if (method_exists($this, $field_fun)) {
            $type_fun = $alias . "_" . $field;
            if (method_exists($this, $type_fun)) {
              $field_value = $this->{$type_fun}($entity, $field);
              $bool = FALSE;
            }
          }
        }
        if ($bool) {
          $field_value = "please create a formatter like : function " . $field_type . "(entity, field) or " . $field . "(entity, field)";
        }
        $item[$field] = ($field_value);
      }
    }
    return $item;
  }


  //**
  // List of Hook Fields Type
  //*//

  public function list_string($entity, $field) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $field_value = NULL;
    if ($bool) {
      $field_value = array_column($entity->get($field)->getValue(), "value");
    }
    return $field_value;
  }

  public function decimal($entity, $field) {
    return $this->string($entity, $field);
  }

  public function string($entity, $field) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $result = [];
    if ($bool) {
      $items = $entity->get($field)->getValue();

      foreach ($items as $key => $item) {
        $result[] = $item['value'];
      }
    }
    return $result;

  }


  public function entity_reference_revisions($entity, $field) {

    $bool = $this->helper->is_field_ready($entity, $field);

    $item = [];
    if ($bool) {
      $fields = $entity->get($field)->getValue();
      if (!empty($fields)) {
        foreach ($fields as $field) {
          $paragraph = \Drupal::entityTypeManager()
            ->getStorage('paragraph')
            ->load($field['target_id']);
          if (is_object($paragraph)) {
            $item[$field['target_id']] = $this->entity_parser_load($paragraph);
          }

        }
      }
    }

    return $item;
  }



  public function integer($entity, $field) {
    return $this->string($entity, $field);
  }

  public function uuid($entity, $field) {
    return $this->string($entity, $field);
  }

  public function language($entity, $field) {
    return $this->string($entity, $field);
  }

  public function entity_reference_taxonomy_term($node, $field) {
    $bool = $this->helper->is_field_ready($node, $field);

    $result = [];
    if ($bool) {
      $terms = $node->get($field)->getValue();
      $entity_type = \Drupal::entityTypeManager();

      foreach ($terms as $key => $value) {
        $term = $entity_type->getStorage('taxonomy_term')
          ->load($value['target_id']);
        if (is_object($term)) {
          $result[$value['target_id']] = [
            "term" => $term,
            "title" => $term->label(),
            "tid" => $value['target_id'],
          ];
        }
      }
      if (count($result) == 1) {
        return array_shift($result);
      }
    }

    return $result;
  }

  public function type($entity, $field) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $field_value = NULL;
    if ($bool) {
      $field_value = $entity->get($field)->getValue();
      if (count($field_value) == 1) {
        $field_value = array_shift($field_value)["target_id"];
      }
    }
    return $field_value;
  }

  public function link($entity, $field) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $field_value = NULL;
    if ($bool) {
      $field_value = $entity->get($field)->getValue();
      if (count($field_value) == 1) {
        $field_value = (array_shift($field_value));
      }
    }
    return $field_value;
  }

  public function image_file($entity, $field, $style = NULL) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $img_result = [];
    if ($bool) {
      $images = $entity->get($field)->getValue();
      foreach ($images as $key => $image) {
        $file = File::load($image['target_id']);
        if (is_object($file)) {
          $img = $image;
          if ($style) {
            $img['image'] = ImageStyle::load($style)
              ->buildUrl($file->getFileUri());
          }
          $img['uri'] = $file->getFileUri();
          $img_result[]= $img ;
        }

      }
    }
    return $img_result;

  }

  public function file($entity, $field) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $result = [];
    if ($bool) {
      $files = $entity->get($field)->getValue();
      foreach ($files as $key => $file) {
        $file = File::load($file['target_id']);
        if (is_object($file)) {
          $result = $file;
          $result['file'] = URl::fromUri(file_create_url($file->getFileUri()))
            ->toString();
          $result['uri'] = $file->getFileUri();
        }
      }
    }
    return $result;
  }

  public function path($entity, $field) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $result = NULL;
    if ($bool) {
      $result = $entity->get($field)->getValue();
    }
    return $result;
  }

  public function comment($entity, $field) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $result = NULL;
    if ($bool) {
      $result = $entity->get($field)->getValue();
    }
    return $result;
  }


  public function text_long($entity, $field) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $field_value = NULL;
    if ($bool) {
      $field_value = array_column($entity->get($field)->getValue(), "value");
    }
    return $field_value;
  }

  public function entity_reference_user($entity, $field) {
    $result = [];
    $bool = $this->helper->is_field_ready($entity, $field);
    if ($bool) {
      $users = $entity->get($field)->getValue();
      foreach ($users as $key => $value) {
        $item_user = \Drupal\user\Entity\User::load($value['target_id']);
        if (is_object($item_user)) {
          $result[$value['target_id']] = [
            "user" => $item_user,
            "name" => $item_user->getUsername(),
            "uid" => $value['target_id'],
          ];
        }
      }
    }
    return $result;
  }

  public function entity_reference_node($entity, $field) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $result = [];
    if ($bool) {
      $nodes = $entity->get($field)->getValue();
      $entity_type = \Drupal::entityTypeManager();
      foreach ($nodes as $key => $value) {
        $node = $entity_type->getStorage('node')->load($value['target_id']);
        if (is_object($entity)) {
          $result[$value['target_id']] = [
            "node" => $entity,
            "title" => $node->label(),
            "nid" => $value['target_id'],
          ];
        }
      }
    }
    return $result;
  }

  public function float($entity, $field) {
    return $this->string($entity, $field);
  }

  public function coordinates($node, $field) {
    $bool = $this->helper->is_field_ready($node, $field);
    $result = [];
    if ($bool) {
      $values = $node->get($field)->getValue();
      foreach ($values as $value) {
        $result[] = [
          "lat" => $value['lat'],
          "lng" => $value['lng'],
        ];
      }
      if (count($result) == 1) {
        return array_shift($result);
      }
    }
    return $result;
  }

  public function string_long($entity, $field) {
    return $this->string($entity, $field);
  }

  public function email($entity, $field) {
    return $this->string($entity, $field);
  }

  public function boolean($entity, $field) {
    return $this->string($entity, $field);
  }

  public function changed($entity, $field) {
    return $this->string($entity, $field);
  }

  public function created($entity, $field) {
    return $this->string($entity, $field);
  }

  public function timestamp($entity, $field) {
    return $this->string($entity, $field);
  }

  public function text_with_summary($entity, $field) {
    $bool = $this->helper->is_field_ready($entity, $field);
    $result = [];
    if ($bool) {
      $result = $entity->get($field)->getValue();
    }
    return $result;
  }
  public function password($entity, $field){
    return  $entity->get($field)->getValue();
  }


}
