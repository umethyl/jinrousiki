<?php require_once(dirname(__FILE__) . '/../include/setting.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" href="../css/src.css">
<title>��ȯ�ǥ��������������</title>
</head>
<body>
<a href="../index.php">�����</a><br>
<?php include_once(dirname(__FILE__) . '/download.php'); ?>

<form method="POST" action="upload.php" enctype="multipart/form-data">
<table id="upload">
<tr>
  <td><label>�ե���������</label></td>
  <td><input type="file" name="file" size="60"></td>
</tr>
<tr>
  <td><label>�ե�����̾</label></td>
  <td><input type="text" name="name" maxlength="20" size="20"></td>
</tr>
<tr>
  <td><label>�ե����������</label></td>
  <td><input type="text" name="caption" maxlength="80" size="80"></td>
</tr>
<tr>
  <td><label>������̾</label></td>
  <td><input type="text" name="user" maxlength="20" size="20"></td>
</tr>
<tr>
  <td><label>�ѥ����</label></td>
  <td><input type="password" name="password" maxlength="20" size="20"></td>
</tr>
<tr>
  <td><input type="submit" value="���åץ���"></td>
  <td><label>�б���ĥ�Ҥ� zip, lzh �Τ�</label></td>
</tr>
</table>
</form>
</body></html>
