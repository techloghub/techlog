<?php
class EarningsController extends Controller
{
	private $colors = array(
		array('rgba(0, 255, 255, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(17, 177, 255, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(128, 128, 255, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(255, 128, 192, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(104, 180, 255, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(255, 255, 128, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(128, 255, 128, 0.7)', 'rgba(220, 220, 220, 0.3)'),
	);
	private $income;
	private $expend;

	public function listAction()
	{
		if (!$this->is_root)
		{
			header("Location: /index/notfound");
			return;
		}

		$avg = $this->getAvg(false);
		list($labels, $expends, $incomes) = $this->getInOutDatas(24, false);
		list($inCategories, $outCategories) = $this->getCategories('', '', false);

		$params = array(
			'incomes' => json_encode($incomes),
			'expends' => json_encode($expends),
			'labels' => json_encode($labels),
			'inCategories' => json_encode($inCategories),
			'outCategories' => json_encode($outCategories),
			'avg' => $avg,
			'income' => $this->income,
			'expend' => $this->expend,
		);

		$this->display(__METHOD__, $params);
	}

	private function getCategories($begMonth, $endMonth, $useHouseFund)
	{
		$query_params = array();
		if (!$useHouseFund)
		{
			$query_params['query']['bool']['must_not'][] = array('term' =>
				array('fromAcc' => '0b45af0bd5e741dbbe8e796f13943736'));
		}
		$query_params['query']['bool']['must'][] =
			array('term' => array('recType' => 2));
		if (!empty($begMonth))
		{
			$query_params['query']['bool']['must'][] = array('range' =>
				array('date' => array('gte' => $begMonth.'-01 00:00:00')));
		}
		if (!empty($endMonth))
		{
			$enddate = date('Y-m-t 23:59:59', strtotime($endMonth));
			$query_params['query']['bool']['must'][] = array('range' =>
				array('date' => array('lte' => $enddate)));
		}
		$query_params['size'] = 0;
		$query_params['aggs']['uniq'] = array(
			'terms' => array('field' => 'category', 'size' => 0),
			'aggs' => array('totalfee' => array('sum' => array('field' => 'money')))
		);
		$ret = ESRepository::getWacaiLedgersList($query_params);
		if ($ret === false || !isset($ret['aggregations']['uniq']['buckets']))
		{
			header("Location: /index/notfound");
			exit;
		}
		$inCategories = array();
		$outCategories = array();
		$i = 0;
		foreach ($ret['aggregations']['uniq']['buckets'] as $infos)
		{
			$inCategories[] = array(
				'label' => $infos['key'].' ('
					.round($infos['totalfee']['value']*100/$this->income, 2).'%)',
				'value' => round($infos['totalfee']['value'], 2),
				'color' => $this->colors[$i%count($this->colors)][0],
				'highlight' => $this->colors[$i%count($this->colors)][1],
			);
			$i++;
		}

		$query_params['query']['bool']['must'][0]['term']['recType'] = 1;
		$ret = ESRepository::getWacaiLedgersList($query_params);
		if ($ret === false || !isset($ret['aggregations']['uniq']['buckets']))
		{
			header("Location: /index/notfound");
			exit;
		}
		$i = 0;
		foreach ($ret['aggregations']['uniq']['buckets'] as $infos)
		{
			$outCategories[] = array(
				'label' => $infos['key'].' ('
					.round($infos['totalfee']['value']*100/$this->expend, 2).'%)',
				'value' => round($infos['totalfee']['value'], 2),
				'color' => $this->colors[$i%count($this->colors)][0],
				'highlight' => $this->colors[$i%count($this->colors)][1],
			);
			$i++;
		}

		return array($inCategories, $outCategories);
	}

	private function getAvg($useHouseFund)
	{
		$time = time();
		$query_params = array();
		if (!$useHouseFund)
		{
			$query_params['query']['bool']['must_not'][] = array('term' =>
				array('fromAcc' => '0b45af0bd5e741dbbe8e796f13943736'));
		}
		$query_params['query']['bool']['must'][] =
			array('term' => array('recType' => 2));
		$query_params['aggs']['totalfee'] =
			array('sum' => array('field' => 'money'));
		$query_params['size'] = 0;
		$ret = ESRepository::getWacaiLedgersList($query_params);
		if ($ret === false || !isset($ret['aggregations']['totalfee']['value']))
		{
			header("Location: /index/notfound");
			exit;
		}
		$this->income = round($ret['aggregations']['totalfee']['value'], 2);
		$query_params['query']['bool']['must'][0]['term']['recType'] = 1;
		$ret = ESRepository::getWacaiLedgersList($query_params);
		if ($ret === false || !isset($ret['aggregations']['totalfee']['value']))
		{
			header("Location: /index/notfound");
			exit;
		}
		$this->expend = round($ret['aggregations']['totalfee']['value']);
		$avg = $this->income - $this->expend;
		$avg = round($avg/(($time - 1377964800)/(3600*24*30)), 2);
		return $avg;
	}

	private function getInOutDatas($monthCount, $useHouseFund)
	{
		$time = time();
		$labels = array();
		$expends = array();
		$incomes = array();
		for ($i=$monthCount; $i>=1; $i--)
		{
			$query_params = array();
			$beg_month = date('Y-m-01 00:00:00', $time - $i*3600*24*30);
			$end_month = date('Y-m-t 23:59:59', $time - $i*3600*24*30);
			$query_params['query']['bool']['must'][] =
				array('term' => array('recType' => 1));
			if (!$useHouseFund)
			{
				$query_params['query']['bool']['must_not'][] = array('term' =>
					array('fromAcc' => '0b45af0bd5e741dbbe8e796f13943736'));
			}
			$query_params['query']['bool']['must'][] =
				array('range' => array('date' => array( 'gte' => $beg_month,
							'lte' => $end_month)));
			$query_params['aggs']['totalfee'] =
				array('sum' => array('field' => 'money'));
			$query_params['size'] = 0;
			$ret = ESRepository::getWacaiLedgersList($query_params);
			if ($ret === false
				|| !isset($ret['aggregations']['totalfee']['value']))
			{
				exit;
				header("Location: /index/notfound");
				exit;
			}
			$expends[] = round($ret['aggregations']['totalfee']['value'], 2);

			$query_params['query']['bool']['must'][0]['term']['recType'] = 2;
			$ret = ESRepository::getWacaiLedgersList($query_params);
			if ($ret === false
				|| !isset($ret['aggregations']['totalfee']['value']))
			{
				header("Location: /index/notfound");
				exit;
			}
			$incomes[] = round($ret['aggregations']['totalfee']['value'], 2);

			$labels[] = date('Y-m', $time - $i*3600*24*30);
		}
		return array($labels, $expends, $incomes);
	}
}
?>
