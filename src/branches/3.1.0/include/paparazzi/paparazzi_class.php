<?php
//-- ◆文字化け抑制◆ --//
class Paparazzi {
  private $date;
  private $time;
  private $memory;
  private $log;
  private $written;

  public function __construct() {
    $this->date    = Time::GetDateTime(Time::Get());
    $this->time    = microtime();
    $this->memory  = memory_get_usage();
    $this->log     = array();
    $this->written = false;
  }

  public function shot($comment, $category = 'general') {
    $this->log[] = array(
	'time'     => $this->GetTime(),
	'memory'   => memory_get_usage() - $this->memory,
	'category' => $category,
	'comment'  => $comment
    );
    return $comment;
  }

  public function GetTime() {
    return microtime() - $this->time;
  }

  public function Output($force = false) {
    echo $this->Collect($force);
  }

  public function OutputBench($label = null) {
    echo (is_null($label) ? '' : $label . ':') . sprintf('%f[s]', $this->GetTime());
  }

  public function Collect($force = false) {
    if (! $force && $this->written) return;
    $this->written |= ! $force;

    $output = '<dl>' . '<dt>' .  $this->date . '</dt>';
    foreach ($this->log as $item) {
      extract($item, EXTR_PREFIX_ALL, 'unsafe');
      $category = Text::Escape($unsafe_category);
      $comment  = Text::Escape($unsafe_comment);
      $output .= "<dt>($unsafe_time) : $unsafe_memory</dt><dd>$category : $comment</dd>";
    }
    return $output . '</dl>';
  }

}
