<?php require(VIEW_PATH.'/base/base.php'); ?>
<input type="hidden" id="line_beg_month"/>
<input type="hidden" id="line_end_month"/>
<br /><br /><br />
<div class="container projects">
	<div class="row">
		<div id="line">
			<canvas id="out_in_lines" width="980px" height="300px"></canvas>
		</div>
		<div class='span12'><br /></div>
		<div class='span2'>
			<input type="text" id="income" class="form-control" readonly="readonly" value="收入：<?php echo $params['income'] ?>"/>
		</div>
		<div class='span2'>
			<input type="text" id="expend" class="form-control" readonly="readonly" value="支出：<?php echo $params['expend'] ?>"/>
		</div>
		<div class='span2'>
			<input type="text" id="avg" class="form-control" readonly="readonly" value="月均结余：<?php echo $params['avg'] ?>"/>
		</div>
		<div class='span1'></div>
		<div class='span1'>
				<input type="text" id="beg_month" class="form-control" placeholder="BEG_MONTH" value="<?php echo_ifset($params, 'beg_month') ?>"/>
		</div>
		<div class='span1'>
				<input type="text" id="end_month" class="form-control" placeholder="END_MONTH" value="<?php echo_ifset($params, 'end_month') ?>"/>
		</div>
		<div class='span1'>
				<button type="submit" onclick="reload_func()" class="btn btn-default">查&nbsp;&nbsp;询</button>&nbsp;&nbsp;
		</div>
		<div class='span12'><br /><br /></div>
		<div id="in_canvas">
			<canvas id="in_category_doughnut" width="300px" height="300px" class="span4"></canvas>
		</div>
		<div class="span2"></div>
		<div id="out_canvas">
			<canvas id="out_category_doughnut" width="300px" height="300px" class="span4"></canvas>
		</div>
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
			fillColor : "rgba(220,220,220,0.5)",
			strokeColor : "rgba(220,220,220,1)",
			pointColor : "rgba(220,220,220,1)",
			pointStrokeColor : "#fff",
			data : <?php echo $params['incomes'] ?>
		},
		{
			label : "expend",
			fillColor : "rgba(68,114,169,0.5)",
			strokeColor : "rgba(151,187,205,1)",
			pointColor : "rgba(151,187,205,1)",
			pointStrokeColor : "#fff",
			data : <?php echo $params['expends'] ?> 
		}
	]
}
var ctx = document.getElementById("out_in_lines").getContext("2d");
var myNewChart = new Chart(ctx).PolarArea(out_in_datas);
var myLineChart = new Chart(ctx).Line(out_in_datas, canvas_options);

doughnut_options['percentageInnerCutout'] = 30;
var in_categories_data = <?php echo $params['inCategories'] ?>;
var out_categories_data = <?php echo $params['outCategories'] ?>;
var ctx = document.getElementById("in_category_doughnut").getContext("2d");
var inDoughnutChart = new Chart(ctx).Doughnut(in_categories_data, doughnut_options);
var ctx = document.getElementById("out_category_doughnut").getContext("2d");
var outDoughnutChart = new Chart(ctx).Doughnut(out_categories_data, doughnut_options);

$('#out_in_lines').click(function(evt) { redraw_func(evt); } );

function redraw_func(evt)
{
	var activePoints = myLineChart.getPointsAtEvent(evt);
	console.log(activePoints[0]);
	if (typeof(activePoints[0]['label']) != 'undefined')
	{
		$.ajax(
			{
				'url' : '/earnings/redraw',
				'type' : 'post',
				'data' : {"month" : activePoints[0]['label']},
				'dataType' : 'json',
				'error' : function (jqXHR, textStatus, errorThrown) {
					var errMsg = errorThrown == 'Forbidden' ? '没权限' : '服务器忙';
					jAlert(errMsg, '提示');
				},
				'success' : function (data) {
					if (data['code'] != 0) {
						jAlert(data['msg'], '提示', function() { return false; });
					}
					$('#beg_month').val(data['beg_month']);
					$('#end_month').val(data['end_month']);
					$('#avg').val('月均结余：'+data['avg']);
					$('#expend').val('支出：'+data['expend']);
					$('#income').val('收入：'+data['income']);
					$('#in_canvas').html('<canvas id="in_category_doughnut" width="300px" height="300px" class="span4"></canvas>');
					$('#out_canvas').html('<canvas id="out_category_doughnut" width="300px" height="300px" class="span4"></canvas>');
					var ctx = document.getElementById("in_category_doughnut").getContext("2d");
					var inDoughnutChart = new Chart(ctx).Doughnut(data['inCategories'], doughnut_options);
					var ctx = document.getElementById("out_category_doughnut").getContext("2d");
					var outDoughnutChart = new Chart(ctx).Doughnut(data['outCategories'], doughnut_options);
				}
			}
		);
	}
}

function reload_func()
{
	var form = document.createElement("form");
	form.setAttribute("method", 'post');
	form.setAttribute("action", '/earnings');

	var hiddenField = document.createElement("input");
	hiddenField.setAttribute("type", "hidden");
	hiddenField.setAttribute("name", 'beg_month');
	hiddenField.setAttribute("value", $('#beg_month').val());
	form.appendChild(hiddenField);

	hiddenField = document.createElement("input");
	hiddenField.setAttribute("type", "hidden");
	hiddenField.setAttribute("name", 'end_month');
	hiddenField.setAttribute("value", $('#end_month').val());
	form.appendChild(hiddenField);

	document.body.appendChild(form);
	form.submit();
}
</script>
<?php require(VIEW_PATH.'/base/footer.php'); ?>
