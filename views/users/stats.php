<?php
  require_once($_SERVER['DOCUMENT_ROOT']."/../includes.php");
  $this->app->check_partial_include(__FILE__);
?>
<div class='row'>
  <div class='col-md-4'>
    <?php echo $this->view('animeRatingDist'); ?>
  </div>
  <div class='col-md-8'>
    <?php echo $this->view('animeStatusDist'); ?>
  </div>
</div>
<div class='row'>
  <div class='col-md-12'>
    <?php echo $this->view('favouriteTags', $params); ?>
  </div>
</div>
<div class='row'>
  <div class='col-md-6'>
    <?php echo $this->view('animeCompletionTimeline', $params); ?>
  </div>
  <div class='col-md-6'>
    <?php echo $this->view('averageRatingTimeline', $params); ?>
  </div>
</div>