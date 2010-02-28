<?

class html extends CHtml {
  public static function activeTextField($model,$attribute,$htmlOptions=array())
  {
    self::resolveNameID($model,$attribute,$htmlOptions);
    self::clientChange('change',$htmlOptions);
    return self::activeInputField('text',$model,$attribute,$htmlOptions);
  }

  protected static function activeInputField($type,$model,$attribute,$htmlOptions)
  {
    $htmlOptions['type']=$type;
    if($type==='file')
      unset($htmlOptions['value']);
    else if(!isset($htmlOptions['value']))
      $htmlOptions['value']=$model->$attribute;
    if(isset($htmlOptions['gray']) && empty($htmlOptions['value']))
    {
      if(empty($htmlOptions['class']))
        $htmlOptions['class'] = 'gray';
      else
        $htmlOptions['class'] .= ' gray';
      $htmlOptions['value'] = $htmlOptions['gray'];
    } 
    unset($htmlOptions['gray']);
    if($model->hasErrors($attribute))
      self::addErrorCss($htmlOptions);
    return self::tag('input',$htmlOptions); 
  }
}
