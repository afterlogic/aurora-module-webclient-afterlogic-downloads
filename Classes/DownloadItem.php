<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 * 
 * @package Modules
 */

namespace Aurora\Modules\AfterlogicDownloadsWebclient\Classes;

/**
 * @property int $IdUser
 *
 * @ignore
 * @package Contactsmain
 * @subpackage Classes
 */
class DownloadItem extends \Aurora\System\EAV\Entity
{
	public function __construct($sModule)
	{
		$this->aStaticMap = array(
			'Date'				=> array('datetime', date('Y-m-d H:i:s')),
			'Email'				=> array('string', ''),
			'Referer'			=> array('text', ''),
			'Ip'				=> array('string', ''),
			'ProductId'			=> array('int', 0),
			'ExternalProductId'	=> array('int', 0),
			'ProductName'		=> array('string', ''),
			'ProductVersion'	=> array('string', ''),
			'LicenseKey'		=> array('text', ''),
			'ProductCommercial'	=> array('bool', true),
			'PackageId'			=> array('int', 0),
			'PackageName'		=> array('string', '')
		);
		parent::__construct($sModule);
	}
	
	/**
	 * @param string $sKey
	 * @param mixed $mValue
	 */
	public function __set($sKey, $mValue)
	{
		if (is_string($mValue))
		{
	        $mValue = str_replace(array("\r","\n\n"), array('\n','\n'), $mValue);
		}

		parent::__set($sKey, $mValue);
	}
	
	/**
	 * Populate download item with specified data.
	 * @param array $aData List of download data.
	 * @param \Aurora\Modules\Core\Classes\User $oUser User.
	 */
	public function Populate($aData, $oUser = null)
	{
//		if (isset($oUser))
//		{
//			$this->IdUser = $oUser->iId;
//			$this->IdTenant = $oUser->IdTenant;
//		}
		
		if (isset($aData['Date']))
		{
			$this->Date = date("Y-m-d H:i:s", $aData['Date']);
		}
		if (isset($aData['Email']))
		{
			$this->Email = (string)$aData['Email'];
		}
		if (isset($aData['Referer']))
		{
			$this->Referer = (string)$aData['Referer'];
		}
		if (isset($aData['Ip']))
		{
			$this->Ip = (string)$aData['Ip'];
		}
		if (isset($aData['ProductId']))
		{
			$this->ProductId = (int)$aData['ProductId'];
		}
		if (isset($aData['ProductName']))
		{
			$this->ProductName = (string)$aData['ProductName'];
		}
		if (isset($aData['ProductVersion']))
		{
			$this->ProductVersion = (string)$aData['ProductVersion'];
		}
		if (isset($aData['LicenseKey']))
		{
			$this->LicenseKey = (string)$aData['LicenseKey'];
		}
		if (isset($aData['ProductCommercial']))
		{
			$this->ProductCommercial = (bool)$aData['ProductCommercial'];
		}
		if (isset($aData['PackageId']))
		{
			$this->PackageId = (int)$aData['PackageId'];
		}
		if (isset($aData['PackageName']))
		{
			$this->PackageName = (string)$aData['PackageName'];
		}
		
		return true;
	}
}
