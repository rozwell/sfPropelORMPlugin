<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormActivable represents link capable of adding a widget schema to a form
 *
 * @package    symfony
 * @subpackage widget
 * @author     Francois Zaninotto
 */
class sfWidgetFormSchemaOptional extends sfWidgetFormSchemaDecoratorEscaped
{

  /**
   * Constructor.
   *
   * @param sfWidgetFormSchema $widget     A sfWidgetFormSchema instance
   * @param string             $decorator  A decorator string
   *
   * @see sfWidgetFormSchema
   */
  public function __construct(sfWidgetFormSchema $widget, $decorator, $options = array())
  {
    parent::__construct($widget, $decorator);
    $this->addOption('add_link', 'Add new');
    $this->addOption('max_additions', 0);
    $this->options = array_merge($this->options, $options);

    // FIXME saving fresh nested forms fails so we remove adding them for now
    // NOTE updated javascript seems to be working fine
    foreach($this->getWidget()->getFields() as $name => $field)
    {
      if($field instanceof sfWidgetFormSchemaOptional)
      {
        $this->getWidget()->offsetUnset($name);
      }
    }
  }
  
  protected function getDecorator($name)
  {
    $strippedName = substr($name, strrpos($name, '[') + 1, strrpos($name, ']') - strrpos($name, '[') - 1);
    // we need unique id to make nested forms working
    $id = str_replace(array('][', '[', ']'), array('_', '_', ''), $name);

    $decorator = $this->escape($this->decorator);
    $decorator = "
<script type=\"text/javascript\">
var added{$id} = 0;
function add{$id}Widget()
{
  added{$id} += 1;
  var content = \"{$decorator}\";
  var spanTag = document.createElement(\"span\");
  spanTag.innerHTML = content.replace(/([_\[]){$strippedName}([_\]])/g, '\$1{$strippedName}' +  + added{$id} + '\$2');
  document.getElementById('add_{$id}').appendChild(spanTag);
  document.getElementById('add_{$id}').style.display='block';";
    if($this->getOption('max_additions') > 0)
    {
      $decorator .= "
  if (added{$id} == {$this->getOption('max_additions')})
  {
    document.getElementById('add_{$id}_link').style.display='none';
  }";
    }
    $decorator .= "
}
</script>
<div id=\"add_{$id}\" style=\"display:none\">
</div>
<a href=\"#\" id = \"add_{$id}_link\" onclick=\"add{$id}Widget();return false;\">
  {$this->getOption('add_link')}
</a>";

    return $decorator;
  }
}
