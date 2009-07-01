<h2>Update tvEpisode <?php echo $tvepisode->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('tvEpisode List',array('list')); ?>]
[<?php echo CHtml::link('New tvEpisode',array('create')); ?>]
[<?php echo CHtml::link('Manage tvEpisode',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
  'tvepisode'=>$tvepisode,
  'update'=>true,
)); ?>
