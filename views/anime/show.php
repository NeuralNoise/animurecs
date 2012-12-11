<?php
  require_once($_SERVER['DOCUMENT_ROOT']."/global/includes.php");
  check_partial_include(__FILE__);
?>
     <div class='row-fluid'>
        <div class='span3 userProfileColumn leftColumn'>
          <ul class='thumbnails avatarContainer'>
            <li class='span12'>
              <div class='thumbnail profileAvatar'>
<?php
  if ($this->imagePath() != '') {
?>                <img src='<?php echo joinPaths(ROOT_URL,escape_output($this->imagePath())); ?>' class='img-rounded' alt=''>
<?php
  } else {
?>                <img src='/img/anime/blank.png' class='img-rounded' alt=''>
<?php
  }
?>          </div>
            </li>
          </ul>
        </div>
        <div class='span9 userProfileColumn rightColumn'>
          <div class='profileUserInfo'>
            <h1>
              <?php echo escape_output($this->title()); ?> 
              <?php echo $this->allow($currentUser, "edit") ? "<small>(".$this->link("edit", "edit").")</small>" : ""; ?>
            </h1>
            <p>
              <?php echo escape_output($this->description()); ?>
            </p>
<?php
  if ($currentUser->loggedIn()) {
?>            <ul class='thumbnails'>
              <li class='span4'>
<?php
    if (!isset($currentUser->animeList->uniqueList[$this->id]) || $currentUser->animeList->uniqueList[$this->id]['score'] == 0) {
      $userRating = $params['recsEngine']->predict($currentUser, $this)[$this->id];
?>                <p class='lead'>Predicted score:</p>
                <?php echo $this->scoreBar($userRating); ?>
<?php
    } else {
      $userRating = $currentUser->animeList->uniqueList[$this->id]['score'];
?>                <p class='lead'>You rated this:</p>
                <?php echo $this->scoreBar($userRating); ?>
<?php
    }
    if ($userRating != 0) {
?>(which is <?php echo abs(round($userRating - $currentUser->animeList->uniqueListAvg, 2))." points ".($userRating > $currentUser->animeList->uniqueListAvg ? "higher" : "lower")." than your average score and ".abs(round($userRating - $this->ratingAvg(), 2))." points ".($userRating > $this->ratingAvg() ? "higher" : "lower")." than this anime's average score)";
    }
  } else {
?>            <ul class='thumbnails'>
              <li class='span4'>
                <p class='lead'>Predicted score:</p>
                <p>Sign in to view your predicted score!</p>
<?php
  }
?>              </li>
              <li class='span8'>
                <p class='lead'>Tags:</p>
                <?php echo $this->tagCloud($currentUser); ?>
              </li>
            </ul>
          </div>
          <div id='userFeed'>
<?php
  if ($currentUser->loggedIn()) {
    $animeList = new AnimeList($this->dbConn, 0);
    $anime = new Anime($this->dbConn, 0);
    if (isset($currentUser->animeList->uniqueList[$this->id])) {
      $thisEntry = $currentUser->animeList->uniqueList[$this->id];
      $addText = "Update this anime in your list: ";
    } else {
      $thisEntry = [];
      $addText = "Add this anime to your list: ";
    }
?>              <div class='addListEntryForm'>
              <form class='form-inline' action='<?php echo $animeList->url("new", array('user_id' => intval($currentUser->id))); ?>' method='POST'>
                <input name='anime_list[user_id]' id='anime_list_user_id' type='hidden' value='<?php echo intval($currentUser->id); ?>' />
                <?php echo $addText; ?>
                <input name='anime_list[anime_id]' id='anime_list_anime_id' type='hidden' value='<?php echo intval($this->id); ?>' />
                <?php echo display_status_dropdown("anime_list[status]", "span3", $thisEntry['status']); ?>
                <div class='input-append'>
                  <input class='input-mini' name='anime_list[score]' id='anime_list_score' type='number' min='0' max='10' step='1' value='<?php echo intval($thisEntry['score']); ?>' />
                  <span class='add-on'>/10</span>
                </div>
                <div class='input-prepend'>
                  <span class='add-on'>Ep</span>
                  <input class='input-mini' name='anime_list[episode]' id='anime_list_episode' type='number' min='0' step='1' value='<?php echo intval($thisEntry['episode']); ?>' />
                </div>
                <input type='submit' class='btn btn-primary updateEntryButton' value='Update' />
              </form>
            </div>
<?php
  }
?>          <?php echo $this->animeFeed($currentUser); ?>
          </div>
        </div>
      </div>
      <?php echo $this->tagList($currentUser); ?>