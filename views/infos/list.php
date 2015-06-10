<?php require(VIEW_PATH.'/base/base.php'); ?>
<br /><br /><br />
<div class="container projects">
	<div class="row">
		<canvas id="myChart" width="980px" height="300px"></canvas>
		<div class='span12'><br /></div>
		<div class='span2'>
			<input type="text" class="form-control" readonly="readonly" value="今日页面浏览量：<?php echo $params['today_pv'] ?>"/>
		</div>
		<div class='span2'>
			<input type="text" class="form-control" readonly="readonly" value="今日浏览用户数：<?php echo $params['today_uv'] ?>"/>
		</div>
		<div class='span2'></div>
		<div class='span2'>
			<input type="text" class="form-control" readonly="readonly" value="总页面浏览量：<?php echo $params['all_pv'] ?>"/>
		</div>
		<div class='span2'>
			<input type="text" class="form-control" readonly="readonly" value="总浏览用户数：<?php echo $params['all_uv'] ?>"/>
		</div>
		<div class='span12'><br /><br /></div>
		<canvas id="category_doughnut" width="300px" height="300px" class="span4"></canvas>
		<div class="span1"></div>
		<div class="span6">
			<table class="stdtable" border="1" style="background-color:rgba(255, 255, 255, 0.5); font-size:18;overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
				<caption style='font-weight:bold;
					font-family:"PT Serif","Georgia","Helvetica Neue",Arial,sans-serif;
					background-color:#D2E1F0;
					height:30px;'>分类信息</caption>
					<thead>
					<tr style="background-color:#C5C5C5;">
						<td width="2%">分类名称</td>
						<td width="8%">最新文章</td>
						<td width="2%">创建时间</td>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($params['category_infos'] as $infos) { ?>
						<tr>
							<td>
							<?php echo $infos['category'] ?>
							</td>
							<td>
							<a href="article.php?id=<?php echo $infos['article_id'] ?>" target="_blank"><?php echo $infos['title'] ?></a>
							</td>
							<td>
							<?php echo $infos['inserttime'] ?>
							</td>
						</tr>
					<?php } ?>
					</tbody>
			</table>
		</div>
		<div class='span12'><br /><br /></div>
	</div>
</div>
<script src="../resource/Chart.js-master/Chart.min.js"></script>
<script src="../resource/zeyu_blog/js/chart.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" language="javascript">
var category_data = <?php echo $params['category_data'] ?>;

var pv_data =
{
	labels : <?php echo $params['labels'] ?>,
	datasets : 
	[
		{
			label: "pv",
			fillColor : "rgba(220,220,220,0.3)",
			strokeColor : "rgba(220,220,220,1)",
			pointColor : "rgba(220,220,220,1)",
			pointStrokeColor : "#fff",
			data : <?php echo $params['pv'] ?>
		},
		{
			label : "uv",
			fillColor : "rgba(68,114,169,0.3)",
			strokeColor : "rgba(151,187,205,1)",
			pointColor : "rgba(151,187,205,1)",
			pointStrokeColor : "#fff",
			data : <?php echo $params['uv'] ?> 
		}
	]
}
var ctx = document.getElementById("myChart").getContext("2d");
var myNewChart = new Chart(ctx).PolarArea(pv_data);
new Chart(ctx).Line(pv_data, canvas_options);

var ctx = document.getElementById("category_doughnut").getContext("2d");
var myDoughnutChart = new Chart(ctx).Doughnut(category_data, doughnut_options);
$('#category_doughnut').click(function(evt)
{
    var activePoints = myDoughnutChart.getSegmentsAtEvent(evt);
	var category_ids = <?php echo $params['category_ids'] ?>;
	if (typeof(category_ids[activePoints[0]['label']]) != 'undefined')
	{
		window.open(
			'debin.php?category='
			+ category_ids[activePoints[0]['label']]
		);
	}
});
</script>
<?php require(VIEW_PATH.'/base/footer.php'); ?>
