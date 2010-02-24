<?php

$sql=file_get_contents(dirname(__FILE__)."/../../data/schema-test.populate");
foreach(explode(';', $sql) as $line)
  Yii::app()->db->createCommand($line)->execute();
