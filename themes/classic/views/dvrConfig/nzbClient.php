    <div class="client_config" id="nzbClient">
      <h2 class="dialog_heading">NZB Client</h2>
      <?php echo CHtml::dropDownList('dvrConfig[nzbClient]', $config->nzbClient, $availClients);
            foreach($availClients as $client => $title):
              echo CHtml::beginForm(array('/dvrConfig/nzbClient', 'id'=>$client), 'post', array('id'=>$client)); ?>
          <?php 
            $clientConfig = $config->$client;
            echo CHtml::errorSummary($clientConfig);
            foreach($clientConfig as $key => $value) {
              echo '<div>'.
                     CHtml::activeLabel($clientConfig, $key, array('class'=>'item')).': '.
                     CHtml::activeTextField($clientConfig, $key).
                   '</div>';
            }
          ?>
          <div class="buttonContainer">
            <a class="submitForm button" class="Save" href="#">Save</a>
            <a class='toggleDialog button' href='#'>Close</a>
          </div>
        <?php echo CHtml::endForm(); ?>
      <?php endforeach; ?>
    </div>
