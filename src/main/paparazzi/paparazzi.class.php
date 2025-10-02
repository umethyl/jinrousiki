<?php
class Paparazzi {
	var $version = 'paparazzi v1 beta / build 03';
	var $dateTime;
	var $startTime;
	var $log;

	function Paparazzi(){
		$this->dateTime = date('Y-m-d H:i:s');
		$this->startTime = microtime();
		$this->log = array();
	}

	function getTimeElapsed(){
		return microtime() - $this->startTime;
	}

	function shot($comment,$category='general'){
		$this->log[] = array(
			'time' => $this->getTimeElapsed(), 
			'category' => $category, 
			'comment' => $comment
		);
		return $comment;
	}

	function insertBenchResult($label=false){
		echo ($label ? $label.':' : '').sprintf('%f[s]', $this->getTimeElapsed());
	}

	function insertLog(){
		if ($this->written) return;
		echo '<dl>';
		foreach ($this->log as $item){
			extract($item, EXTR_PREFIX_ALL, 'unsafe');
			$category = htmlspecialchars($unsafe_category);
			$comment = htmlspecialchars($unsafe_comment);
			echo "<dt>($unsafe_time)</dt><dd>$category : $comment</dd>";
		}
		echo '</dl>';
		$this->written = true;
	}

	function save($room_no, $uname, $action){
		if ($this->serialized) return;
		Paparazzi::modifySchema();
		//シーンの登録
		mysql_query(shot("INSERT INTO pp_articles (room_no, reported_time, uname, action) VALUES ($room_no,'{$this->dateTime}','$uname','$action')"));
		$article_id = mysql_insert_id();
		//ログの記録
		$records = array();
		foreach ($this->log as $i => $item){
			extract($item, EXTR_PREFIX_ALL, 'unsafe');
			$category = mysql_real_escape_string($unsafe_category);
			$comment = mysql_real_escape_string($unsafe_comment);
			$records[] = "($article_id,$i,$unsafe_time,'$category','$comment')";
		}
		mysql_query(shot('INSERT INTO pp_album (article_id, step_no, elapsed_time, category, note) VALUES '.join(',', $records).''));
		$this->serialized = true;
	}

	function modifySchema(){
		mysql_query('CREATE TABLE IF NOT EXISTS pp_articles (
			article_id INT AUTO_INCREMENT PRIMARY KEY,
			room_no INT NOT NULL,
			reported_time DATETIME NOT NULL,
			uname TEXT NOT NULL,
			action TINYTEXT NOT NULL,
			INDEX room (room_no)
		) TYPE = MYISAM');
		mysql_query('CREATE TABLE IF NOT EXISTS pp_album (
			article_id INT NOT NULL,
			step_no INT NOT NULL,
			elapsed_time DOUBLE NOT NULL,
			category TINYTEXT NOT NULL,
			note TEXT NOT NULL,
			PRIMARY KEY(article_id, step_no)
		) TYPE = MYISAM');
	}
}
?>
