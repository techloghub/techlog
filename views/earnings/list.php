<?php require(VIEW_PATH.'/base/base.php'); ?>
<br /><br /><br />
<div class="container projects">
	<div class="row">
		<canvas id="out_in_lines" width="980px" height="300px"></canvas>
		<div class='span12'><br /></div>
		<div class='span12'><br /><br /></div>
		<canvas id="category_doughnut" width="300px" height="300px" class="span4"></canvas>
		<div class="span1"></div>
		<canvas id="category_doughnut" width="300px" height="300px" class="span4"></canvas>
		<div class='span12'><br /><br /></div>
	</div>
</div>
<script src="/resource/Chart.js-master/Chart.min.js"></script>
<script src="/resource/zeyu_blog/js/chart.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" language="javascript">
var out_in_datas =
{
	labels : <?php echo $params['labels'] ?>,
	datasets : 
	[
		{
			label: "income",
			fillColor : "rgba(220,220,220,0.3)",
			strokeColor : "rgba(220,220,220,1)",
			pointColor : "rgba(220,220,220,1)",
			pointStrokeColor : "#fff",
			data : <?php echo $params['incomes'] ?>
		},
		{
			label : "expend",
			fillColor : "rgba(68,114,169,0.3)",
			strokeColor : "rgba(151,187,205,1)",
			pointColor : "rgba(151,187,205,1)",
			pointStrokeColor : "#fff",
			data : <?php echo $params['expends'] ?> 
		}
	]
}
var ctx = document.getElementById("out_in_lines").getContext("2d");
var myNewChart = new Chart(ctx).PolarArea(out_in_datas);
new Chart(ctx).Line(out_in_datas, canvas_options);
</script>
<?php require(VIEW_PATH.'/base/footer.php'); ?>
