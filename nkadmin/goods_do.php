<?php	require(dirname(__FILE__).'/inc/config.inc.php');IsModelPriv('goods');

/*
**************************
(C)2010-2013 phpMyWind.com
update: 2013-1-8 15:58:25
person: Feng
**************************
*/


//初始化未传递参数
$action  = isset($action) ? $action : '';
$keyword = isset($keyword) ? $keyword : '';
$styleal = '';
$stylec1 = '';
$stylec0 = '';
$styleah = '';


//删除单条记录
if($action == 'del')
{
	//栏目权限验证
	$r = $dosql->GetOne("SELECT `classid` FROM `#@__$tbname` WHERE `id`=$id");
	IsCategoryPriv($r['classid'],'del',1);

	$deltime = time();
	$dosql->ExecNoneQuery("UPDATE `#@__$tbname` SET delstate='true', deltime='$deltime' WHERE id=$id");
}


//删除选中记录
if($action == 'delall')
{
	if($ids != '')
	{
		//解析id,验证是否有删除权限
		$ids = explode(',',$ids);
		$idstr = '';
		foreach($ids as $id)
		{
			$r = $dosql->GetOne("SELECT `classid` FROM `#@__$tbname` WHERE `id`=$id");
			if(IsCategoryPriv($r['classid'],'del',1))
			{
				$idstr .= $id.',';
			}
		}
		$idstr .= trim($idstr,',');

		if($idstr != '')
		{
			$deltime = time();
			$dosql->ExecNoneQuery("UPDATE `#@__$tbname` SET delstate='true', deltime='$deltime' WHERE `id` IN ($idstr)");
		}
	}
}


//设置属性样式及查询语句
switch($flag)
{
	case 'all':
		$flagquery = 'id<>0';
		$styleal = 'onflag';
		break;  
	case 'checkinfo2':
		$flagquery  = "checkinfo='false'";
		$stylec1 = 'onflag';
		break;
	case 'checkinfo':
		$flagquery  = "checkinfo='true'";	
		$stylec0 = 'onflag';
		break;
	case 'author':
		$flagquery  = "author='".$_SESSION['admin']."'";
		$styleah = 'on_author';
		break;
	default:
		$dosql->Execute("SELECT `flag` FROM `#@__goodsflag`");
		while($row = $dosql->GetArray())
		{
			if($row['flag'] == $flag)
			{
				$flagquery = "flag LIKE '%$flag%'";
			}
		}
}

//Ajax输出数据
?>

<div class="mgr_divt">
	<ul class="flag">
		<li>属性：</li>
		<li class="<?php echo $styleal; ?>"><a href="javascript:;" onclick="GetFlag('all')">全部</a></li>
		<li><span>|</span></li>
		<li class="<?php echo $stylec1; ?>"><a href="javascript:;" onclick="GetFlag('checkinfo2');">未审</a></li>
		<li><span>|</span></li>
		<li class="<?php echo $stylec0; ?>"><a href="javascript:;" onclick="GetFlag('checkinfo')">已审</a></li>
		<li><span>|</span></li>
		<?php
		$dosql->Execute("SELECT * FROM `#@__goodsflag` ORDER BY orderid ASC");
		while($row = $dosql->GetArray())
		{
			echo '<li class="';

			if($row['flag'] == $flag)
			{
				echo 'onflag';
			}

			echo '"><a href="javascript:;" onclick="GetFlag(\''.$row['flag'].'\',\''.@$cid.'\')">'.$row['flagname'].'</a></li>';
			echo '<li><span>|</span></li>';
		}
		?>
		<li class="<?php echo $styleah; ?>"><a href="javascript:;" onclick="GetFlag('author')">我发布的文档</a></li>
		<li><span>|</span></li>
		<li><a href="javascript:;" onclick="ShowRecycle();">内容回收站</a></li>
	</ul>
	<div id="search" class="search"> <span class="s">
		<input name="keyword" id="keyword" type="text" title="输入商品名进行搜索" value="<?php echo $keyword; ?>" />
		</span> <span class="b"><a href="javascript:;" onclick="GetSearch();"><img src="templates/images/search_btn.png" title="搜索" /></a></span></div>
</div>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="mgr_table" id="ajaxlist">
	<tr class="thead2" align="center">
		<td width="5%" height="25"><input type="checkbox" name="checkid" id="checkid" onclick="CheckAll(this.checked);"></td>
		<td width="10%">缩略图</td>
		<td width="5%">ID</td>
		<td width="15%">商品名称</td>
		<td width="15%">更新时间</td>
		<td width="12%">商品类型</td>
		<td width="10%">点击</td>
		<td width="10%">库存</td>
		<td width="18%" class="noborder">操作</td>
	</tr>
	<?php

	//检查全局分页数
	if(empty($cfg_pagenum))  $cfg_pagenum = 20;


	//权限验证
	if($_SESSION['adminlevel'] != 1)
	{
		//初始化参数
		$catgoryListPriv   = '';
		$catgoryUpdatePriv = array();
		$catgoryDelPriv    = array();

		$dosql->Execute("SELECT * FROM `#@__adminprivacy` WHERE `groupid`=".$_SESSION['adminlevel']." AND `model`='category' AND `action`<>'add'");
		while($row = $dosql->GetArray())
		{
			//查看权限
			if($row['action'] == 'list')
				$catgoryListPriv .= $row['classid'].',';

			//修改权限
			if($row['action'] == 'update')
				$catgoryUpdatePriv[] = $row['classid'];

			//删除权限
			if($row['action'] == 'del')
				$catgoryDelPriv[]    = $row['classid'];
			
		}

		$catgoryListPriv = trim($catgoryListPriv,',');
	}


	//设置sql
	$sql = "SELECT * FROM `#@__$tbname` WHERE `delstate`=''";	

	if(!empty($catgoryListPriv)) $sql .= " AND classid IN ($catgoryListPriv)";
	if(!empty($cid))     $sql .= " AND (classid=$cid OR parentstr LIKE '%,$cid,%')";
	if(!empty($tid))     $sql .= " AND (typeid=$tid OR typepstr LIKE '%,$tid,%')";	
	if(!empty($flag))    $sql .= " AND $flagquery";
	if(!empty($keyword)) $sql .= " AND title LIKE '%$keyword%'";

	$dopage->GetPage($sql);
	while($row = $dosql->GetArray())
	{

		//获取商品名称
		$title = '<span style="color:'.$row['colorval'].';font-weight:'.$row['boldval'].'">'.$row['title'].'</span>';
		$title .= '<span class="titflag">';


		//获取商品品牌
		$r = $dosql->GetOne('SELECT `classname` FROM `#@__goodsbrand` WHERE `id`='.$row['brandid']);
		if(isset($r['classname']))
		{
			$title .= '['.$r['classname'].'] ';
		}


		//获取商品属性
		$flagarr = explode(',',$row['flag']);
		$flagnum = count($flagarr);
		for($i=0; $i<$flagnum; $i++)
		{
			$r = $dosql->GetOne("SELECT `flagname` FROM `#@__goodsflag` WHERE flag='".$flagarr[$i]."'");

			if(isset($r['flagname']))
			{
				$title .= $r['flagname'].' ';
			}
		}

		$title .= '</span>';


		//商品类型名称
		$r = $dosql->GetOne("SELECT `classname` FROM `#@__goodstype` WHERE `id`=".$row['typeid']);
		if(isset($r['classname']))
			$classname = $r['classname'].' ['.$row['classid'].']';
		else
			$classname = '<span class="red">分类已删 ['.$row['classid'].']</span>';


		//获取库存数量
		$housenum = $row['housenum'];
		if($row['housewarn'] == 'true')
		{
			if($housenum <= $row['warnnum'])
			{
				$housenum .= '<strong style="color:red;margin-left:6px;">库存不足</strong>';
			}
		}


		//获取审核状态
		switch($row['checkinfo'])
		{
			case 'true':
				$checkinfo = '已审';
				break;  
			case 'false':
				$checkinfo = '未审';
				break;
			default:
				$checkinfo = '没有获取到参数';
		}
		

		//修改权限
		if($_SESSION['adminlevel'] != 1)
		{
			if(in_array($row['classid'], $catgoryUpdatePriv))
				$updateStr = '<a href="goods_update.php?cid='.$cid.'&id='.$row['id'].'">修改</a>';
			else
				$updateStr = '修改';
		}
		else
		{
			$updateStr = '<a href="goods_update.php?cid='.$cid.'&id='.$row['id'].'">修改</a>';
		}


		//删除权限
		if($_SESSION['adminlevel'] != 1)
		{
			if(in_array($row['classid'], $catgoryDelPriv))
				$delStr = '<a href="javascript:;" onclick="ClearInfo('.$row['id'].')">删除</a>';
			else
				$delStr = '删除';
		}
		else
		{
			$delStr = '<a href="javascript:;" onclick="ClearInfo('.$row['id'].')">删除</a>';
		}
		
		
		//审核权限
		if($_SESSION['adminlevel'] != 1)
		{
			if(in_array($row['classid'], $catgoryUpdatePriv))
				$checkStr = '<a href="javascript:;" title="点击进行审核与未审操作" onclick="CheckInfo('.$row['id'].',\''.$checkinfo.'\')">'.$checkinfo.'</a>';
			else
				$checkStr = $checkinfo;
		}
		else
		{
			$checkStr = '<a href="javascript:;" title="点击进行审核与未审操作" onclick="CheckInfo('.$row['id'].',\''.$checkinfo.'\')">'.$checkinfo.'</a>';
		}
	?>
	<tr align="center" class="mgr_tr" onmouseover="this.className='mgr_tr_on'" onmouseout="this.className='mgr_tr'">
		<td height="70" align="center"><input type="checkbox" name="checkid[]" id="checkid[]" value="<?php echo $row['id']; ?>" /></td>
		<td><span class="thumbs"><?php echo GetMgrThumbs($row['picurl']); ?></span></td>
		<td><?php echo $row['id']; ?></td>
		<td align="left"><span class="titles"><?php echo $title; ?></span></td>
		<td class="number"><?php echo GetDateTime($row['posttime']); ?></td>
		<td><?php echo $classname; ?></td>
		<td><?php echo $row['hits']; ?></td>
		<td><?php echo $housenum; ?></td>
		<td class="action"><span id="check<?php echo $row['id']; ?>">[<?php echo $checkStr; ?>]</span><span>[<?php echo $updateStr; ?>]</span><span>[<?php echo $delStr; ?>]</span></td>
	</tr>
	<?php
	}	
	?>
</table>
<?php
if($dosql->GetTotalRow() == 0)
{
	echo '<div class="mgr_nlist">暂时没有相关的记录</div>';
}
?>
<div class="mgr_divb">
	<div class="selall"><span>选择：</span> <a href="javascript:CheckAll(true);">全部</a> - <a href="javascript:CheckAll(false);">无</a> - <a href="javascript:;" onclick="AjaxClearAll();">删除</a></div>
	<span class="mgr_btn"><a href="goods_add.php">添加商品信息</a></span> </div>
<div class="page_area"> <?php echo $dopage->AjaxPage(); ?> </div>
<script>
$(function(){
    $(".thumbs img").LoadImage();
});
</script>