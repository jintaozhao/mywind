<?php require(dirname(__FILE__).'/inc/config.inc.php');IsModelPriv('site'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>修改站点</title>
<link href="templates/style/admin.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="templates/js/jquery.min.js"></script>
<script type="text/javascript" src="templates/js/checkf.func.js"></script>
</head>
<body>
<?php
$row = $dosql->GetOne("SELECT * FROM `#@__site` WHERE `id`=$id");
?>
<div class="gray_header"> <span class="title">修改站点</span> <span class="reload"><a href="javascript:location.reload();">刷新</a></span></div>
<form name="form" id="form" method="post" action="site_save.php" onsubmit="return cfm_site();">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="form_table">
		<tr class="nb">
			<td width="25%" height="35" align="right">站点名称：</td>
			<td width="75%"><input type="text" name="site_name" id="site_name" class="class_input" value="<?php echo $row['sitename']; ?>" />
			<span class="maroon">*</span><span class="cnote">站点切换名称，例如：英文站</span></td>
		</tr>
		<tr class="nb">
			<td height="35" align="right">&nbsp;</td>
			<td>更多站点基本信息可通过网站信息配置进行设置</td>
		</tr>
	</table>
	<div class="subbtn_area">
		<input type="submit" class="blue_submit_btn" value="" />
		<input type="button" class="blue_back_btn" value="" onclick="history.go(-1)"  />
		<input type="hidden" name="action" id="action" value="update" />
		<input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
	</div>
</form>
</body>
</html>