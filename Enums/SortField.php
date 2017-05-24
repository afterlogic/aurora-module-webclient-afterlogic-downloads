<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Aurora\Modules\AfterlogicDownloadsWebclient\Enums;

class SortField extends \Aurora\System\Enums\AbstractEnumeration
{
	const Date = 1;
	const ProductId = 2;

	/**
	 * @var array
	 */
	protected $aConsts = array(
		'Date' => self::Date,
		'ProductId' => self::ProductId
	);
}