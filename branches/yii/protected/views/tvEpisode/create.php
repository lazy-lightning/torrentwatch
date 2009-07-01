<h2>New tvEpisode</h2>

<div class="actionBar">
[<?php echo CHtml::link('tvEpisode List',array('list')); ?>]
[<?php echo CHtml::link('Manage tvEpisode',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
  'tvepisode'=>$tvepisode,
  'update'=>false,
)); ?>
