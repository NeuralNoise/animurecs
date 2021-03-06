<?php
  require_once($_SERVER['DOCUMENT_ROOT']."/../includes.php");
  $this->app->check_partial_include(__FILE__);
  $firstAnime = Anime::Get($this->app);

  $params['anime'] = isset($params['anime']) ? $params['anime'] : [];
  $params['perPage'] = isset($params['perPage']) ? intval($params['perPage']) : 8;
  $params['page'] = isset($params['page']) && intval($params['page']) > 0 ? intval($params['page']) : 1;
?>

<div id='related-content'>
  <?php echo paginate($this->url('related', Null, ['page' => '']), $params['page'], Null, '#related-content'); ?>
  <?php 
    if ($params['anime']->length() > 0) {
      echo $firstAnime->view('grid', ['group' => $params['anime']]); 
    } else {
      echo "<p class='center-horizontal'>No related anime could be found. Maybe there aren't enough ratings yet?</p>";
    }
  ?>
  <?php echo paginate($this->url('related', Null, ['page' => '']), $params['page'], Null, '#related-content'); ?>
</div>