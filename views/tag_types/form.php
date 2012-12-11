<?php
  if ($_SERVER['DOCUMENT_URI'] === $_SERVER['REQUEST_URI']) {
    echo "This partial cannot be viewed on its own.";
    exit;
  }
?>
    <form action='<?php echo ($this->id === 0) ? $this->url("new") : $this->url("edit"); ?>' method='POST' class='form-horizontal'>
      <?php echo ($this->id === 0) ? "" : "<input type='hidden' name='tag_type[id]' value='".intval($this->id)."' />"; ?>
      <input name='tag_type[created_user_id]' type='hidden' value='<?php echo $this->id === 0 ? intval($currentUser->id) : $this->createdUser()->id; ?>' />
      <fieldset>
        <div class='control-group'>
          <label class='control-label' for='tag_type[name]'>Name</label>
          <div class='controls'>
            <input name='tag_type[name]' type='text' class='input-xlarge' id='tag_type[name]'<?php echo ($this->id === 0) ? "" : " value='".escape_output($this->name)."'"; ?> />
          </div>
        </div>
        <div class='control-group'>
          <label class='control-label' for='tag_type[description]'>Description</label>
          <div class='controls'>
            <textarea class='field span4' name='tag_type[description]' rows='3' id='tag_type[description]'><?php echo ($this->id === 0) ? "" : escape_output($this->description); ?></textarea>
          </div>
        </div>
        <div class='form-actions'>
          <button type='submit' class='btn btn-primary'><?php echo ($this->id === 0) ? "Create Tag Type" : "Save changes"; ?></button>
          <a href='#' onClick='window.location.replace(document.referrer);' class='btn'><?php echo ($this->id === 0) ? "Go back" : "Discard changes"; ?></a>
        </div>
      </fieldset>
    </form>