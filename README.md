# entity_parser
Drupal 8 Module for convert Entity object to Simple Array ,for examp 

## Demo 

## how to user

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
