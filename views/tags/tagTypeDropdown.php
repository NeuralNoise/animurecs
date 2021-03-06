<?php
  require_once($_SERVER['DOCUMENT_ROOT']."/../includes.php");
  $this->app->check_partial_include(__FILE__);

  if (!isset($params['select_id'])) {
    $params['select_id'] = 'tag[tag_type_id]';
  }
  if (!isset($params['selected'])) {
    if ($this->type) {
      $params['selected'] = intval($this->type->id);
    } else {
      $params['selected'] = 0;
    }
  }
  $allTypes = $this->app->dbConn->table(TagType::$TABLE)->fields('id', 'name')->order('name ASC')->query();
?>
<select id='<?php echo escape_output($params['select_id']); ?>' name='<?php echo escape_output($params['select_id']); ?>'>
<?php
  while ($type = $allTypes->fetch()) {
?>
  <option value='<?php echo intval($type['id']); ?>'<?php echo (($params['selected'] == intval($type['id'])) ? " selected='selected'" : ""); ?>><?php echo escape_output($type['name']); ?></option>
<?php
  }
?>
</select>