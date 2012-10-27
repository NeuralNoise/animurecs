<?php

class TagType {
  public $dbConn;
  public $id;
  public $name;
  public $description;
  public $tags;
  public $createdUser;
  public function __construct($database, $id=Null) {
    $this->dbConn = $database;
    if ($id === 0) {
      $this->id = 0;
      $this->name = $this->description = "";
      $this->tags = $this->createdUser = [];
    } else {
      $typeInfo = $this->dbConn->queryFirstRow("SELECT `id`, `name`, `description` FROM `tag_types` WHERE `id` = ".intval($id)." LIMIT 1");
      $this->id = intval($typeInfo['id']);
      $this->name = $typeInfo['name'];
      $this->description = $typeInfo['description'];
      $this->tags = $this->getTags();
      $this->createdUser = $this->getCreatedUser();
    }
  }
  public function allow($authingUser, $action) {
    // takes a user object and an action and returns a bool.
    switch($action) {
      // case 'approve':
      case 'new':
      case 'edit':
      case 'delete':
        if ($authingUser->isAdmin()) {
          return True;
        }
        return False;
        break;
      case 'show':
      case 'index':
        return True;
        break;
      default:
        return False;
        break;
    }
  }
  public function delete() {
    // delete this tag type from the database.
    // returns a boolean.
    $deleteType = $this->dbConn->stdQuery("DELETE FROM `tag_types` WHERE `id` = ".intval($this->id)." LIMIT 1");
    if (!$deleteType) {
      return False;
    }
    return True;
  }
  public function create_or_update($tag_type, $currentUser) {
    // creates or updates a tag type based on the parameters passed in $tag_type and this object's attributes.
    // returns False if failure, or the ID of the tag type if success.
    // make sure tag type name adheres to standards.
    $tag_type['name'] = str_replace(" ", "_", strtolower($tag_type['name']));
    $params = array();
    foreach ($tag_type as $parameter => $value) {
      if (!is_array($value)) {
        $params[] = "`".$this->dbConn->real_escape_string($parameter)."` = ".$this->dbConn->quoteSmart($value);
      }
    }
    //go ahead and create or update this tag type.
    if ($this->id != 0) {
      //update this tag_type.
      $updateTagType = $this->dbConn->stdQuery("UPDATE `tag_types` SET ".implode(", ", $params)." WHERE `id` = ".intval($this->id)." LIMIT 1");
      if (!$updateTagType) {
        return False;
      }
    } else {
      // add this tag type.
      $insertTagType = $this->dbConn->stdQuery("INSERT INTO `tag_types` SET ".implode(",", $params).", `created_user_id` = ".intval($currentUser->id));
      if (!$insertTagType) {
        return False;
      } else {
        $this->id = intval($this->dbConn->insert_id);
      }
    }
    return $this->id;
  }
  public function isApproved() {
    // Returns a bool reflecting whether or not the current anime is approved.
    // doesn't do anything for now. maybe use later.
    /* 
    if ($this->approvedOn === '' or !$this->approvedOn) {
      return False;
    }
    return True;
    */
  }
  public function getApprovedUser() {
    // retrieves an id,name array corresponding to the user who approved this anime.
    // return $this->dbConn->queryFirstRow("SELECT `users`.`id`, `users`.`name` FROM `anime` LEFT OUTER JOIN `users` ON `users`.`id` = `anime`.`approved_user_id` WHERE `anime`.`id` = ".intval($this->id));
  }
  public function getCreatedUser() {
    // retrieves an id,name array corresponding to the user who created this tag type.
    return $this->dbConn->queryFirstRow("SELECT `users`.`id`, `users`.`name` FROM `tag_types` LEFT OUTER JOIN `users` ON `users`.`id` = `tag_types`.`created_user_id` WHERE `tag_types`.`id` = ".intval($this->id));
  }
  public function getTags() {
    // retrieves a list of id arrays corresponding to tags belonging to this tag type
    return $this->dbConn->queryAssoc("SELECT `id` FROM `tags` WHERE `tag_type_id` = ".intval($this->id)." ORDER BY `name` ASC");
  }
  public function link($action="show", $text=Null, $raw=False) {
    // returns an HTML link to the current tag's profile, with text provided.
    if ($text === Null) {
      $text = $this->title ? $this->title : "Info";
    }
    return "<a href='/tag_type.php?action=".urlencode($action)."&id=".intval($this->id)."'>".($raw ? $text : escape_output($text))."</a>";
  }
  public function profile() {
    // displays a tag type's profile.
    return;
    $userObject = new User($database, $user_id);
    $facility = $database->queryFirstValue("SELECT `name` FROM `facilities` WHERE `id` = ".intval($userObject->facility_id)." LIMIT 1");
    $form_entries = $database->stdQuery("SELECT `form_entries`.*, `forms`.`name` AS `form_name`, `machines`.`name` AS `machine_name` FROM `form_entries` 
                                          LEFT OUTER JOIN `forms` ON `forms`.`id` = `form_entries`.`form_id`
                                          LEFT OUTER JOIN `machines` ON `machines`.`id` = `form_entries`.`machine_id`
                                          WHERE `user_id` = ".intval($user_id)." 
                                          ORDER BY `updated_at` DESC");
    echo "<dl class='dl-horizontal'>
      <dt>Email</dt>
      <dd>".escape_output($userObject->email)."</dd>
      <dt>Facility</dt>
      <dd><a href='facility.php?action=show&id=".intval($userObject->facility_id)."'>".escape_output($facility)."</a></dd>
      <dt>User Role</dt>
      <dd>".escape_output(convert_usermask_to_text($userObject->usermask))."</dd>
    </dl>\n";
    if (convert_usermask_to_text($userObject->usermask) == 'Physicist') {
      $form_approvals = $database->stdQuery("SELECT `form_entries`.`id`, `qa_month`, `qa_year`, `machine_id`, `machines`.`name` AS `machine_name`, `user_id`, `users`.`name` AS `user_name`, `approved_on` FROM `form_entries` LEFT OUTER JOIN `machines` ON `machines`.`id` = `form_entries`.`machine_id` LEFT OUTER JOIN `users` ON `users`.`id` = `form_entries`.`user_id` WHERE `approved_user_id` = ".intval($userObject->id)." ORDER BY `approved_on` DESC");
      echo "  <h3>Approvals</h3>
    <table class='table table-striped table-bordered dataTable'>
      <thead>
        <tr>
          <th>QA Date</th>
          <th>Machine</th>
          <th>Submitter</th>
          <th>Approval Date</th>
        </tr>
      </thead>
      <tbody>\n";
      while ($approval = mysqli_fetch_assoc($form_approvals)) {
        echo "      <tr>
          <td><a href='form_entry.php?action=edit&id=".intval($approval['id'])."'>".escape_output($approval['qa_year']."/".$approval['qa_month'])."</a></td>
          <td><a href='form.php?action=show&id=".intval($approval['machine_id'])."'>".escape_output($approval['machine_name'])."</a></td>
          <td><a href='user.php?action=show&id=".intval($approval['user_id'])."'>".escape_output($approval['user_name'])."</a></td>
          <td>".escape_output(format_mysql_timestamp($approval['approved_on']))."</td>
        </tr>\n";
      }
      echo "    </tbody>
    </table>\n";
    }
    echo "  <h3>Form Entries</h3>
    <table class='table table-striped table-bordered dataTable'>
      <thead>
        <tr>
          <th>Form</th>
          <th>Machine</th>
          <th>Comments</th>
          <th>QA Date</th>
          <th>Submitted on</th>
          <th></th>
        </tr>
      </thead>
      <tbody>\n";
    while ($form_entry = mysqli_fetch_assoc($form_entries)) {
      echo "    <tr>
        <td><a href='form.php?action=show&id=".intval($form_entry['form_id'])."'>".escape_output($form_entry['form_name'])."</a></td>
        <td><a href='form.php?action=show&id=".intval($form_entry['machine_id'])."'>".escape_output($form_entry['machine_name'])."</a></td>
        <td>".escape_output($form_entry['comments'])."</td>
        <td>".escape_output($form_entry['qa_year']."/".$form_entry['qa_month'])."</td>
        <td>".escape_output(format_mysql_timestamp($form_entry['created_at']))."</td>
        <td><a href='form_entry.php?action=edit&id=".intval($form_entry['id'])."'>View</a></td>
      </tr>\n";
    }
    echo "    </tbody>
    </table>\n";
  }
  public function form($currentUser) {
    $output = "<form action='tag_type.php".(($this->id === 0) ? "" : "?id=".intval($this->id))."' method='POST' class='form-horizontal'>\n".(($this->id === 0) ? "" : "<input type='hidden' name='tag_type[id]' value='".intval($this->id)."' />")."
      <fieldset>
        <div class='control-group'>
          <label class='control-label' for='tag_type[name]'>Name</label>
          <div class='controls'>
            <input name='tag_type[name]' type='text' class='input-xlarge' id='tag_type[name]'".(($this->id === 0) ? "" : " value='".escape_output($this->name)."'")." />
          </div>
        </div>
        <div class='control-group'>
          <label class='control-label' for='tag_type[description]'>Description</label>
          <div class='controls'>
            <textarea class='field span4' name='tag_type[description]' rows='3' id='tag_type[description]'>".(($this->id === 0) ? "" : escape_output($this->description))."</textarea>
          </div>
        </div>\n";
        /*
        if ($currentUser->isModerator || $currentUser->isAdmin()) {
          $output .= "        <div class='control-group'>
          <label class='control-label' for='tag_type[approved]'>Approved</label>
          <div class='controls'>
            <input name='tag_type[approved]' type='checkbox' value=1 ".($this->isApproved() ? "checked=checked" : "")."/>
          </div>
        </div>\n";
        }
        */
        $output .= "    <div class='form-actions'>
          <button type='submit' class='btn btn-primary'>".(($this->id === 0) ? "Create Tag Type" : "Save changes")."</button>
          <a href='#' onClick='window.location.replace(document.referrer);' class='btn'>".(($this->id === 0) ? "Go back" : "Discard changes")."</a>
        </div>
      </fieldset>\n</form>\n";
    return $output;
  }
}

?>