<?php
  require_once($_SERVER['DOCUMENT_ROOT']."/../includes.php");
  $this->app->check_partial_include(__FILE__);

  $params['chartDivID'] = isset($params['chartDivID']) ? $params['chartDivID'] : "animeCompletionChart_div";
  $params['intervals'] = (intval($params['intervals']) > 0) ? intval($params['intervals']) : 12;
  $params['title'] = isset($params['title']) ? $params['title'] : "Anime completion over time";
  $params['start'] = isset($params['start']) ? $params['start'] : 0;
  $params['end'] = isset($params['end']) ? $params['end'] : time();

  // first, get time range of this user's anime completions.
  $groupBySeconds = ceil(($params['end'] - $params['start'])/$params['intervals']);
  if ($groupBySeconds < 86400) {
    $groupBySeconds = 86400;
  }
  if ($groupBySeconds > 2592000) {
    $dateFormatString = 'n/y';
  } else {
    $dateFormatString = 'n/j/y';
  }

  // now bin this user's completions into intervals and output markup for these counts.
  $userAnimeTimeline = $this->app->dbConn->table(AnimeList::$TABLE)
    ->fields("ROUND(UNIX_TIMESTAMP(time)/".$groupBySeconds.")*".$groupBySeconds." AS groupedTime", "COUNT(*) AS count")
    ->where(['user_id' => $this->id, 'status' => 2])->group('groupedTime')->order('groupedTime ASC')->assoc();
  $lastTime = $userAnimeTimeline[0]['groupedTime'];
  $maxAnime = 0;
  $maxAnimeTime = 0;
  $finishedTimeline = [];
  foreach ($userAnimeTimeline as $timePoint) {
    if ($timePoint['count'] > $maxAnime) {
      $maxAnime = $timePoint['count'];
      $maxAnimeTime = date($dateFormatString, $timePoint['groupedTime']);
    }
    if ($timePoint['groupedTime'] - $lastTime > $groupBySeconds) {
      while ($lastTime < $timePoint['groupedTime'] - $groupBySeconds) {
        $lastTime += $groupBySeconds;
        $finishedTimeline[] = [date($dateFormatString, $lastTime), 0];
      }
    }
    $finishedTimeline[] = [date($dateFormatString, $timePoint['groupedTime']), intval($timePoint['count'])];
    $lastTime = $timePoint['groupedTime'];
  }
  $params['data'] = $finishedTimeline;
  echo $this->app->view('timeline', $params);
?>