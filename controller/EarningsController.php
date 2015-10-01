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

		list($begMonth, $endMonth) = $this->getBegEndMonth($_REQUEST);
		$categories = $this->getCategories();
		$avg = $this->getAvg($begMonth, $endMonth, false);
		if ($avg === false)
		{
			header("Location: /index/notfound");
			return;
		}
		list($labels, $expends, $incomes) =
			$this->getInOutDatas($begMonth, $endMonth, false);
		if ($labels === false)
		{
			header("Location: /index/notfound");
			return;
		}
		list($inCategories, $outCategories) =
			$this->getCategoryDatas($begMonth, $endMonth, false);
		if ($inCategories === false)
		{
			header("Location: /index/notfound");
			return;
		}

		$params = array(
			'title' => '龙泉财报',
			'beg_month' => $begMonth,
			'end_month' => $endMonth,
			'incomes' => json_encode($incomes),
			'expends' => json_encode($expends),
			'labels' => json_encode($labels),
			'inCategories' => json_encode($inCategories),
			'outCategories' => json_encode($outCategories),
			'avg' => $avg,
			'income' => $this->income,
			'expend' => $this->expend,
			'categories' => $categories
		);

		$this->display(__METHOD__, $params);
	}

	public function redrawActionAjax()
	{
		$begMonth = $endMonth = $_REQUEST['month'];
		if (strtotime($begMonth) == false)
		{
			return array('code' => -1, 'msg' => 'PARAMS ERROR');
		}
		$avg = $this->getAvg($begMonth, $endMonth, false);
		if ($avg === false)
		{
			return array('code' => -1, 'msg' => 'getAvg ERROR');
		}
		list($inCategories, $outCategories) =
			$this->getCategoryDatas($begMonth, $endMonth, false);
		if ($inCategories === false)
		{
			return array('code' => -1, 'msg' => 'getCategoryDatas ERROR');
		}
		return array(
			'code' => 0,
			'beg_month' => $begMonth,
			'end_month' => $begMonth,
			'inCategories' => $inCategories,
			'outCategories' => $outCategories,
			'avg' => $avg,
			'income' => $this->income,
			'expend' => $this->expend,
		);
	}

	public function reloadActionAjax()
	{
		list($begMonth, $endMonth) = $this->getBegEndMonth($_REQUEST);
		list($labels, $expends, $incomes) =
			$this->getInOutDatas(
				$begMonth, $endMonth, false, $_REQUEST['category']);
		return array(
			'code' => 0,
			'labels' => $labels,
			'expends' => $expends,
			'incomes' => $incomes
		);
	}

	private function getCategories()
	{
		$query_params = array(
			'size' => 0,
			'aggs' => array(
				'categories' => array(
					'terms' => array(
						'field' => 'category',
						'size' => 0
					)
				)
			)
		);
		$ret = ESRepository::getWacaiLedgersList($query_params);
		if ($ret === false
			|| !isset($ret['aggregations']['categories']['buckets']))
		{
			return array();
		}
		$categories = array();
		foreach ($ret['aggregations']['categories']['buckets'] as $infos)
		{
			$categories[] = $infos['key'];
		}
		return $categories;
	}

	private function getBegEndMonth($request)
	{
		$begMonth = date('Y-m', time()-24*3600*30*24);
		$endMonth = date('Y-m',
			time()-24*3600*intval(date('t', strtotime('-1 month'))));
		if (isset($request['beg_month']))
		{
			$begMonth = $request['beg_month'];
		}
		if (isset($request['end_month']))
		{
			$endMonth = $request['end_month'];
		}
		if (strtotime($begMonth) == false || strtotime($endMonth) == false
			|| strtotime($begMonth) > strtotime($endMonth) - 3600*24*30
			|| strtotime($endMonth) < strtotime('2013-10')
		)
		{
			$begMonth = date('Y-m', time()-24*3600*30*24);
			$endMonth = date('Y-m',
				time()-24*3600*intval(date('t', strtotime('-1 month'))));
		}
		if (strtotime($endMonth) >
			time()-24*3600*intval(date('t', strtotime('-1 month'))))
		{
			$endMonth = date('Y-m',
				time()-24*3600*intval(date('t', strtotime('-1 month'))));
		}
		if (strtotime($begMonth) < strtotime('2013-09'))
		{
			$begMonth = '2013-09';
		}
		return array($begMonth, $endMonth);
	}

	private function getCategoryDatas($begMonth, $endMonth, $useHouseFund)
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
			'aggs' => array(
				'totalfee' => array('sum' => array('field' => 'money')))
		);
		$ret = ESRepository::getWacaiLedgersList($query_params);
		if ($ret === false || !isset($ret['aggregations']['uniq']['buckets']))
		{
			return array(false, false);
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
			return array(false, false);
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

	private function getAvg($begMonth, $endMonth, $useHouseFund)
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
		$query_params['aggs']['totalfee'] =
			array('sum' => array('field' => 'money'));
		$query_params['size'] = 0;
		$ret = ESRepository::getWacaiLedgersList($query_params);
		if ($ret === false || !isset($ret['aggregations']['totalfee']['value']))
		{
			return false;
		}
		$this->income = round($ret['aggregations']['totalfee']['value'], 2);
		$query_params['query']['bool']['must'][0]['term']['recType'] = 1;
		$ret = ESRepository::getWacaiLedgersList($query_params);
		if ($ret === false || !isset($ret['aggregations']['totalfee']['value']))
		{
			return false;
		}
		$this->expend = round($ret['aggregations']['totalfee']['value'], 2);
		$avg = $this->income - $this->expend;
		$avg = round($avg/intval((strtotime($endMonth) - strtotime($begMonth))/(3600*24*30) + 1), 2);
		return $avg;
	}

	public function getInOutDatas($begMonth, $endMonth, $useHouseFund, $category = null)
	{
		$time = time();
		$labels = array();
		$expends = array();
		$incomes = array();

		if (strtotime($endMonth) - strtotime($begMonth) > 24*3600*30*24)
			$begMonth = date('Y-m', strtotime($endMonth) - 24*3600*30*24);
		else
			$begMonth = $begMonth;
		$begtime = strtotime($begMonth);
		$endtime = strtotime($endMonth);

		while (1)
		{
			$query_params = array();
			$beg_month = date('Y-m-01 00:00:00', $begtime);
			$end_month = date('Y-m-t 23:59:59', $begtime);
			$query_params['query']['bool']['must'][] =
				array('term' => array('recType' => 1));
			if (!empty($category))
			{
				$query_params['query']['bool']['must'][] =
					array('term' => array('category' => $category));
			}
			if (!$useHouseFund)
			{
				$query_params['query']['bool']['must_not'][] = array('term' =>
					array('fromAcc' => '0b45af0bd5e741dbbe8e796f13943736'));
			}
			if ($category == '公积金')
			{
				$query_params = array();
				$query_params['query']['bool']['must'][] =
					array('term' => array('recType' => 1));
				$query_params['query']['bool']['must'][] = array('term' =>
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
				$expends[] = 0;
			}
			else
			{
				$expends[] = round($ret['aggregations']['totalfee']['value'], 2);
			}

			$query_params['query']['bool']['must'][0]['term']['recType'] = 2;
			$ret = ESRepository::getWacaiLedgersList($query_params);
			if ($ret === false
				|| !isset($ret['aggregations']['totalfee']['value']))
			{
				$incomes[] = 0;
			}
			else
			{
				$incomes[] = round($ret['aggregations']['totalfee']['value'], 2);
			}

			$labels[] = date('Y-m', $begtime);
			$begtime += 24*3600*intval(date('t', $begtime));
			if ($endtime - $begtime < 0)
				break;
		}
		return array($labels, $expends, $incomes);
	}
}
?>
