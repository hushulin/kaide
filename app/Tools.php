<?php

/**
 * 生成返回的数组
 */
function apiformat()
{
	$args = func_get_args();

	$arrReturns = [];

	foreach ($args as $arg) {
		if ( is_int($arg) ) {
			$arrReturns['code'] = $arg;
		}elseif ( is_string($arg) ) {
			$arrReturns['msg'] = $arg;
		}elseif ( is_array($arg) || is_object($arg) ) {
			$arrReturns['data'] = $arg;
		}else {
			// do
		}
	}

	if ( !isset($arrReturns['code']) ) {
		$arrReturns['code'] = 1;
	}

	if ( !isset($arrReturns['msg']) ) {
		$arrReturns['msg'] = '操作成功！';
	}

	if ( !isset($arrReturns['data']) ) {
		$arrReturns['data'] = object;
	}

	return $arrReturns;
}
