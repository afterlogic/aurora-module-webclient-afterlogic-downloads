<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\AfterlogicDownloadsWebclient\Classes;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 */
class DownloadItem extends \Aurora\System\EAV\Entity
{
	public function __construct($sModule)
	{
		$this->aStaticMap = array(
			'Date'				=> array('datetime', date('Y-m-d H:i:s'), true),
			'Email'				=> array('string', ''),
			'Referer'			=> array('text', ''),
			'Ip'				=> array('string', ''),
			'ProductId'			=> array('int', 0, true),
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
