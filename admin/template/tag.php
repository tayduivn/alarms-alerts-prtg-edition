<?php include "header.php";?>

<?php if ($page1 == "s") { ?>
<div class="alert alert-success fade in">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  <?php echo $tl["general"]["g7"];?>
</div>
<?php } if ($page1 == "e" || $page1 == "ene") { ?>
<div class="alert alert-danger fade in">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  <?php echo ($page2 == "e" ? $tl["errorpage"]["sql"] : $tl["errorpage"]["not"]);?>
</div>
<?php } ?>

<?php if (isset($JAK_TAG_ALL) && is_array($JAK_TAG_ALL)) { ?>

<form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
<div class="box">
<div class="box-body no-padding">
<div class="table-responsive">
<table class="table table-striped table-hover">
<thead>
<tr>
<th>#</th>
<th><input type="checkbox" id="jak_delete_all" /></th>
<th><?php echo $tl["tag"]["t"];?> <a class="btn btn-warning btn-xs" href="index.php?p=tags&amp;sp=sort&amp;ssp=tag&amp;sssp=DESC"><i class="fa fa-arrow-up"></i></a> <a class="btn btn-success btn-xs" href="index.php?p=tags&amp;sp=sort&amp;ssp=tag&amp;sssp=ASC"><i class="fa fa-arrow-down"></i></a></th>
<th><?php echo $tl["tag"]["t1"];?></th>
<th><a href="javascript:void(0);" class="btn btn-default btn-xs"><i class="fa fa-lock"></i> <i class="fa fa-check"></i></a></th>
<th><button type="submit" name="delete" id="button_delete" class="btn btn-danger btn-xs" onclick="if(!confirm('<?php echo $tl["tag"]["al"];?>'))return false;"><i class="fa fa-trash-o"></i></button></th>
</tr>
</thead>
<?php foreach($JAK_TAG_ALL as $v) { ?>
<tr>
<td><?php echo $v["id"];?></td>
<td><input type="checkbox" name="jak_delete_tag[]" class="highlight" value="<?php echo $v["id"];?>" /></td>
<td><?php echo $v["tag"];?></td>
<td><a href="index.php?p=tags&amp;sp=sort&amp;ssp=pluginid&amp;sssp=<?php echo $v["pluginid"];?>"><?php echo $v["plugin"];?></a></td>
<td><a class="btn btn-default btn-xs" href="index.php?p=tags&amp;sp=lock&amp;ssp=<?php echo $v["id"];?>"><i class="fa fa-<?php if ($v["active"] == 0) { ?>lock<?php } else { ?>check<?php } ?>"></i></a></td>
<td><a class="btn btn-default btn-xs" href="index.php?p=tags&amp;sp=delete&amp;ssp=<?php echo $v["id"];?>" onclick="if(!confirm('<?php echo $tl["tag"]["al"];?>'))return false;"><i class="fa fa-trash-o"></i></a></td>
</tr>
<?php } ?>
</table>
</div>
</div>
</div>
</form>

<div class="icon_legend">
<h3><?php echo $tl["icons"]["i"];?></h3>
<i title="<?php echo $tl["icons"]["i1"];?>" class="fa fa-remove"></i>
</div>

<?php if ($JAK_PAGINATE) { echo $JAK_PAGINATE; } } else { ?>

<div class="alert alert-info">
 	<?php echo $tl["errorpage"]["data"];?>
</div>

<?php } ?>

<!-- JavaScript for select all -->
<script type="text/javascript">
		$(document).ready(function()
		{
			$("#jak_delete_all").click(function() {
				var checked_status = this.checked;
				$(".highlight").each(function()
				{
					this.checked = checked_status;
				});
			});					
		});
</script>
		
<?php include "footer.php";?>