<?php
if ($DEBUG_MODE && !isset($paparazzi)){
	require_once(dirname(__FILE__) . '/paparazzi/paparazzi.class.php');
	$paparazzi = new Paparazzi();

	//shot
	//コメントとカテゴリを指定して、ログに新しい行を追加します。
	//引数
	//$comment : ログに追加するメッセージの本体を指定します。
	//$category : ログに追加するデータの分類名を指定します。この引数は省略可能です。
	//	指定しなかった場合、規定値として'general'が設定されます。
	function shot($comment,$category='general'){
		global $paparazzi;
		return $paparazzi->shot($comment, $category);
	}
	
	//insertBenchResult
	//テスト対象を起動してからこの関数が呼ばれるまでの時間を計測し、結果を挿入します。
	//引数
	//$label : 測定時間にラベルを付けます。この引数は省略可能です。指定しなかった場合、ラベルは表示されません。
	function insertBenchResult($label=false){
		global $paparazzi;
		$paparazzi->insertBenchResult($label);
	}

	//insertLog
	//トレースログを挿入します。
	function insertLog(){
		global $paparazzi;
		$paparazzi->insertLog();
	}

	//saveLog
	//トレースログをデータベースに書き込みます。
	function saveLog($room_no, $uname, $action){
		global $paparazzi;
		$paparazzi->save($room_no, $uname, $action);
	}
}
else { 
	//デバッグモードでない場合、空の関数定義が提供されます。
	function shot($comment,$category='general'){return $comment;}
	function insertBenchResult(){}
	function insertLog(){}
	function save(){}
	return;
}
?>