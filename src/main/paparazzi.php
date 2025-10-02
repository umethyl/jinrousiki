<?php
if ($DEBUG_MODE && !isset($paparazzi)){
	require_once(dirname(__FILE__) . '/paparazzi/paparazzi.class.php');
	$paparazzi = new Paparazzi();

	//shot
	//�����Ȥȥ��ƥ������ꤷ�ơ����˿������Ԥ��ɲä��ޤ���
	//����
	//$comment : �����ɲä����å����������Τ���ꤷ�ޤ���
	//$category : �����ɲä���ǡ�����ʬ��̾����ꤷ�ޤ������ΰ����Ͼ�ά��ǽ�Ǥ���
	//	���ꤷ�ʤ��ä���硢�����ͤȤ���'general'�����ꤵ��ޤ���
	function shot($comment,$category='general'){
		global $paparazzi;
		return $paparazzi->shot($comment, $category);
	}
	
	//insertBenchResult
	//�ƥ����оݤ�ư���Ƥ��餳�δؿ����ƤФ��ޤǤλ��֤��¬������̤��������ޤ���
	//����
	//$label : ¬����֤˥�٥���դ��ޤ������ΰ����Ͼ�ά��ǽ�Ǥ������ꤷ�ʤ��ä���硢��٥��ɽ������ޤ���
	function insertBenchResult($label=false){
		global $paparazzi;
		$paparazzi->insertBenchResult($label);
	}

	//insertLog
	//�ȥ졼�������������ޤ���
	function insertLog(){
		global $paparazzi;
		$paparazzi->insertLog();
	}

	//saveLog
	//�ȥ졼������ǡ����١����˽񤭹��ߤޤ���
	function saveLog($room_no, $uname, $action){
		global $paparazzi;
		$paparazzi->save($room_no, $uname, $action);
	}
}
else { 
	//�ǥХå��⡼�ɤǤʤ���硢���δؿ�������󶡤���ޤ���
	function shot($comment,$category='general'){return $comment;}
	function insertBenchResult(){}
	function insertLog(){}
	function save(){}
	return;
}
?>