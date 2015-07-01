<?php require(VIEW_PATH.'/base/base.php'); ?>
<script language="javascript">
function get_earnings()
{
	var det_beg_month = $('#beg_month').val();
	var det_end_month = $('#end_month').val();
	var drw_beg_month = $('#drw_beg_month').val();
	var drw_end_month = $('#drw_end_month').val();
	window.location.href = 'earnings.php?beg='+det_beg_month+'&'+'end='+det_end_month+'&drw_beg='+drw_beg_month+'&drw_end='+drw_end_month;
}
function drw_earnings()
{
	var det_beg_month = $('#det_beg_month').val();
	var det_end_month = $('#det_end_month').val();
	var drw_beg_month = $('#beg_month').val();
	var drw_end_month = $('#end_month').val();
	window.location.href = 'earnings.php?beg='+det_beg_month+'&'+'end='+det_end_month+'&drw_beg='+drw_beg_month+'&drw_end='+drw_end_month;
}
</script>
<br /><br /><br />
<input type="hidden" id="det_beg_month" class="form-control" value="<?php echo_ifset($params, 'det_beg_month') ?>"/>
<input type="hidden" id="det_end_month" class="form-control" value="<?php echo_ifset($params, 'det_end_month') ?>"/>
<input type="hidden" id="drw_beg_month" class="form-control" value="<?php echo_ifset($params, 'drw_beg_month') ?>"/>
<input type="hidden" id="drw_end_month" class="form-control" value="<?php echo_ifset($params, 'drw_end_month') ?>"/>
<div class="container projects">
	<div class="row">
		<?php if ($params['category_id'] == 1): ?>
			<canvas id="myChart" width="980px" height="300px" class="span12"></canvas>
			<div class='span12'><br /></div>
			<div class='span2'>
				<input type="text" id="beg_month" class="form-control" placeholder="BEG_MONTH" value="<?php echo_ifset($params, 'beg_month') ?>"/>
			</div>
			<div class='span2'>
				<input type="text" id="end_month" class="form-control" placeholder="END_MONTH" value="<?php echo_ifset($params, 'end_month') ?>"/>
			</div>
			<div class='span2'>
				<button type="submit" onclick="get_earnings()" class="btn btn-default">详&nbsp;&nbsp;情</button>&nbsp;&nbsp;
				<button type="submit" onclick="drw_earnings()" class="btn btn-default">绘&nbsp;&nbsp;图</button>
			</div>
			<div class='span2'>
			</div>
			<div class='span2'>
				<input type="text" class="form-control" placeholder="Text input" readonly='readonly' value='平均月结余：<?php echo $params['average'] ?>'\>
			</div>
			<div class='span12'>
				<br /><br />
			</div>
		<?php endif ?>
		<?php foreach ($params['infos'] as $info) { ?>
		<div class="col-sm-6 col-md-4 ">
			<div class="thumbnail">
			<a href="<?php echo $info['idx_href'] ?>" target="_blank"><img class="lazy" src="<?php echo $info['image_path'] ?>" <?php if ($params['category_id'] != 1): ?>style="height:396px;"<?php endif ?> data-src="<?php echo $info['image_path'] ?>"></a>
				<div class="caption">
					<h3>
						<a href="<?php echo $info['idx_href'] ?>"><?php echo $info['title'] ?><br><small><?php echo $info['descs'] ?></small></a>
					</h3>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<?php if ($params['category_id'] == 1): ?>
<script src="/resource/Chart.js-master/Chart.min.js"></script>
<script src="/resource/zeyu_blog/js/chart.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" language="javascript">
var data =
{
	labels : <?php echo $params['labels'] ?>,
	datasets :
	[
		{
			fillColor : "rgba(220,220,220,0.8)",
			strokeColor : "rgba(220,220,220,1)",
			pointColor : "rgba(220,220,220,1)",
			pointStrokeColor : "#fff",
			data : <?php echo $params['income'] ?>
		},
		{
			fillColor : "rgba(68,114,169,0.4)",
			strokeColor : "rgba(151,187,205,1)",
			pointColor : "rgba(151,187,205,1)",
			pointStrokeColor : "#fff",
			data : <?php echo $params['expend'] ?>
		}
	]
}
var ctx = document.getElementById("myChart").getContext("2d");
var myNewChart = new Chart(ctx).PolarArea(data);
new Chart(ctx).Line(data, canvas_options);
</script>
<?php endif ?>
<?php require(VIEW_PATH.'/base/footer.php'); ?>
