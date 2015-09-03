<?php
class EarningsController extends Controller
{
	public function listAction()
	{
		if (!$this->is_root)
		{
			header("Location: /index/notfound");
			return;
		}

		$time = time();
		$labels = array();
		$expends = array();
		$incomes = array();
		for ($i=24; $i>=1; $i--)
		{
			$query_params = array();
			$beg_month = date('Y-m-01 00:00:00', $time - $i*3600*24*30);
			$end_month = date('Y-m-t 23:59:59', $time - $i*3600*24*30);
			$query_params['query']['bool']['must'][] =
				array('term' => array('recType' => 1));
			$query_params['query']['bool']['must'][] =
				array('range' => array('date' => array( 'gte' => $beg_month,
							'lte' => $end_month)));
			$query_params['aggs']['totalfee'] =
				array('sum' => array('field' => 'money'));
			$query_params['size'] = 0;
			$ret = ESRepository::getWacaiLedgersList($query_params);
			if ($ret === false || !isset($ret['aggregations']['totalfee']['value']))
			{
				header("Location: /index/notfound");
				return;
			}
			$expends[] = round($ret['aggregations']['totalfee']['value'], 2);

			$query_params['query']['bool']['must'][0]['term']['recType'] = 2;
			$ret = ESRepository::getWacaiLedgersList($query_params);
			if ($ret === false || !isset($ret['aggregations']['totalfee']['value']))
			{
				header("Location: /index/notfound");
				return;
			}
			$incomes[] = round($ret['aggregations']['totalfee']['value'], 2);

			$labels[] = date('Y-m', $time - $i*3600*24*30);
		}
		$params = array(
			'incomes' => json_encode($incomes),
			'expends' => json_encode($expends),
			'labels' => json_encode($labels),
		);

		$this->display(__METHOD__, $params);
	}
}
?>
