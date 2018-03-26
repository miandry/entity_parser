# entity_parser
Drupal 8 Module for convert Entity object to Simple Array ,for examp 

## Demo 

## How to implement in your drupal project

- Create a Class for example EntityParserDemo.php extend  Drupal\entity_parser\EntityParser
<pre>
    namespace  Drupal\entity_parser_demo;
    use Drupal\entity_parser\EntityParser;

    class EntityParserDemo extend EntityParser{

       //custom field image type by ALIAS = front
       function front_image_file($entity, $field) {
         return parent::image_file($entity, $field, 'medium');
       }
       //custom field by name
       function changed($entity, $field) {
         $item =$this->string($entity, $field);
         $changed_date = date("F j, Y, g:i a", array_shift($item));
         return  $changed_date ;
       }

    }
</pre>    
## How to use 
<pre>
    $parser = new EntityParserDemo();
    $nid = 1;
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    //full node array
    $node_array = $parser->entity_parser_load($node);
    //get only nid and field_address
    $fields = ['nid','field_address'];
    $node_array_hook_alias = $parser->entity_parser_load($node,fields);
    // hook_alias and exclude fields_exclude
    $options =array(
         'hook_alias' => 'front'
         'fields_exclude'=>['field_adress','nid','uuid']
    );
    $node_array_hook_alias = $parser->entity_parser_load($node,array(),$options);
</pre> 
