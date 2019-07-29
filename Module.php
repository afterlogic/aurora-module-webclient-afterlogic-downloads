<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\AfterlogicDownloadsWebclient;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractWebclientModule
{
	public $oManager = null;

	public $SxGeo = null;
	
	public function getManager()
	{
		if ($this->oManager === null)
		{
			$this->oManager = new Manager($this);
		}

		return $this->oManager;
	}

	public function __construct($sPath, $sVersion = '1.0')
	{
		parent::__construct($sPath, $sVersion);
		if (!class_exists("SxGeo"))
		{
			include_once(__DIR__ ."/SxGeo.php");
		}

        $this->SxGeo = new \SxGeo(__DIR__.'/SxGeoCityMax.dat');
	}

	private function prepareFilters($aRawFilters)
	{
		$aFilters = [];
		
		if (is_array($aRawFilters))
		{
			$iAndIndex = 1;
			$iOrIndex = 1;
			foreach ($aRawFilters as $aSubFilters)
			{
				if (is_array($aSubFilters))
				{
					foreach ($aSubFilters as $sKey => $a2ndSubFilters)
					{
						if (is_array($a2ndSubFilters))
						{
							$sNewKey = $sKey;
							if ($sKey === '$AND')
							{
								$sNewKey = $iAndIndex.'$AND';
								$iAndIndex++;
							}
							if ($sKey === '$OR')
							{
								$sNewKey = $iOrIndex.'$OR';
								$iOrIndex++;
							}
							$aFilters[$sNewKey] = $a2ndSubFilters;
						}
					}
				}
			}
		}
		
		return $aFilters;
	}
	
	/**
	 * Obtains list of module settings for authenticated user.
	 * 
	 * @return array
	 */
	public function GetSettings()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		if (!empty($oUser) && ($oUser->isNormalOrTenant() && $this->isEnabledForEntity($oUser) || $oUser->Role === \Aurora\System\Enums\UserRole::SuperAdmin))
		{
			return array(
				'ItemsPerPage' => $this->getConfig('ItemsPerPage', 20)
//				'Login' => $oUser->{self::GetName().'::Login'},
//				'HasPassword' => (bool) $oUser->{self::GetName().'::Password'},
//				'EIframeAppAuthMode' => (new \EIframeAppAuthMode)->getMap(),
//				'EIframeAppTokenMode' => (new \EIframeAppTokenMode)->getMap(),
//				'ItemsPerPage' => $this->getConfig('AuthMode', EIframeAppAuthMode::NoAuthentication),
			);
		}
		
		return null;
	}
	
	/**
	 * Updates module settings.
	 * 
	 * @param int $AuthMode
	 * @param string $Login
	 * @param string $Password
	 * @return bool
	 */
//	public function UpdateSettings($AppName = null, $AuthMode = null, $TokenMode = null,  $Url = null, $Login = '', $Password = '')
//	{
//		if (is_numeric($AuthMode) && is_numeric($TokenMode) && $Url)
//		{
//			\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
//			
//			$this->setConfig('AppName', $AppName);
//			$this->setConfig('AuthMode', $AuthMode);
//			$this->setConfig('TokenMode', $TokenMode);
//			$this->setConfig('Url', $Url);
//
//			return $this->saveModuleConfig();
//		}
//		
//		if (!empty($Login) && !empty($Password))
//		{
//			\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
//			$oUser = \Aurora\System\Api::getAuthenticatedUser();
//			if ($oUser)
//			{
//				$oCoreDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
//				$oUser->{self::GetName().'::Login'} = $Login;
//				$oUser->{self::GetName().'::Password'} = $Password;
//				return $oCoreDecorator->UpdateUserObject($oUser);
//			}
//		}
//		
//		return false;
//	}
	
	/**
	 * Obtains user credentials.
	 * @return array
	 */
	public function GetCredentials()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		
		$iAuthMode = $this->getConfig('AuthMode', \EIframeAppAuthMode::NoAuthentication);
				
		if (($iAuthMode === \EIframeAppAuthMode::CustomCredentialsSetByUser || $iAuthMode === \EIframeAppAuthMode::CustomCredentialsSetByAdmin)
				&& !empty($oUser) && $oUser->isNormalOrTenant())
		{
			return array(
				'Login' => $oUser->{self::GetName().'::Login'},
				'Password' => $oUser->{self::GetName().'::Password'},
			);
		}
		
		return null;
	}
	
	/**
	 * Obtains per user settings for superadmin.
	 * @param int $UserId
	 * @return array
	 */
	public function GetPerUserSettings($UserId)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		$oUser = \Aurora\Modules\Core\Module::Decorator()->GetUserUnchecked($UserId);
		if ($oUser)
		{
			return array(
				'EnableModule' => $this->isEnabledForEntity($oUser),
				'Login' => $oUser->{self::GetName().'::Login'},
				'HasPassword' => (bool) $oUser->{self::GetName().'::Password'}
			);
		}
		
		return null;
	}
	
	/**
	 * Updaters per user settings for superadmin.
	 * 
	 * @param int $UserId
	 * @param bool $EnableModule
	 * @return bool
	 */
	public function UpdatePerUserSettings($UserId, $EnableModule, $Login = '', $Password = '')
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		$oUser = \Aurora\Modules\Core\Module::Decorator()->GetUserUnchecked($UserId);
		if ($oUser)
		{
			$this->updateEnabledForEntity($oUser, $EnableModule);
			
			if (!empty($Login) && !empty($Password))
			{
				$oUser->{self::GetName().'::Login'} = $Login;
				$oUser->{self::GetName().'::Password'} = $Password;
				
				return \Aurora\Modules\Core\Module::Decorator()->UpdateUserObject($oUser);
			}
			
			return true;
		}
		
		return false;
	}
	
	public function GetItems($Offset = 0, $Limit = 20, $SortField = Enums\SortField::Date, $SortOrder = \Aurora\System\Enums\SortOrder::DESC, $Search = '', $Filters = array())
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);

		$aFilters = $this->prepareFilters($Filters);
//		
		if (!empty($Search))
		{
			$aSearchFilters = [
				'ProductName' => ['%'.$Search.'%', 'LIKE'],
				'PackageName' => ['%'.$Search.'%', 'LIKE'],
				'LicenseKey' => $Search,
				'Email' => ['%'.$Search.'%', 'LIKE']
			];
			if (count($aFilters) > 0)
			{
				$aFilters = [
					'$AND' => [
						'1$OR' => $aFilters, 
						'2$OR' => $aSearchFilters
					]
				];
			}
			else
			{
				$aFilters = [
					'$OR' => $aSearchFilters
				];
			}
		}
		elseif (count($aFilters) > 1)
		{
			$aFilters = ['$OR' => $aFilters];
		}
		
		$iCount = $this->getManager()->getDownloadsCount($aFilters);
		$aList = $this->getManager()->getDownloads(array(), $SortField, $SortOrder, $Offset, $Limit, $aFilters);
		
		$aList = array_reverse($aList);
		
        foreach ($aList as $oItem)
        {
            $city = $this->SxGeo->getCityFull($oItem->Ip);
            $bGa = strpos($oItem->Referer, 'gclid') !== false;
            $oItem->Ga = $bGa ? 1 : 0;
            $oItem->City = $city["city"]["name_en"];
            $oItem->Country = $city["country"]["name_en"];
        }

		return array(
			'ItemsCount' => $iCount,
			'List' => \Aurora\System\Managers\Response::GetResponseObject($aList)
//            'List' => $aClearList
		);
	}
	
	public function GetItemsForChart($Search = '', $FromDate = '', $TillDate = '')
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);

//		$aFilters = $this->prepareFilters($Filters);
		
//		$FromDate = '2017-04-24';
//		$TillDate = '2017-04-25';
		
		if ($FromDate && $TillDate)
		{
			$aFilters = [
				'1@Date' => [
					(string)$FromDate,
					'>'
				],
				'2@Date' => [
					(string)$TillDate,
					'<'
				]
			];
		}
		
		if (!empty($Search))
		{
			$aSearchFilters = [
				'ProductName' => ['%'.$Search.'%', 'LIKE'],
				'PackageName' => ['%'.$Search.'%', 'LIKE'],
				'LicenseKey' => $Search,
				'Email' => ['%'.$Search.'%', 'LIKE']
			];
			
			if ($FromDate && $TillDate)
			{
				$aFilters = [
					'$AND' => [
						'$AND' => $aFilters, 
						'$OR' => $aSearchFilters
					]
				];
			}
			else
			{
				$aFilters = [
					'$OR' => $aSearchFilters
				];
			}
		}
		elseif (count($aFilters) > 1)
		{
			$aFilters = ['$AND' => $aFilters];
		}

		//$aFields = array('ProductName', 'Date');
		$aFields = array('Date', 'Referer');

		$aList = $this->getManager()->getDownloads($aFields, Enums\SortField::Date, \Aurora\System\Enums\SortOrder::DESC, 0, 0, $aFilters);



		$aSortedFields = array();
		
		foreach ($aList as $oItem)
		{
            $bGa = strpos($oItem->Referer, 'gclid') !== false;

            $aSortedFields[] = array(
                'Date' => $oItem->Date,
                'Ga' => $bGa ? 1 : 0
				//'ProductName' => $oItem->ProductName
			);
		}

		return array(
			'List' => \Aurora\System\Managers\Response::GetResponseObject($aSortedFields)
		);
	}
	
	/**
	 * Returns contact with specified UUID.
	 * @param string $UUID UUID of contact to return.
	 * @return \Aurora\Modules\Contacts\Classes\Contact
	 */
	public function GetItem($UUID)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		return $this->getManager()->getDownload((string)$UUID);
	}
	
	public function CreateDownload($Data)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
//		$bResponse = false;
//		$sSecretKey = $this->getConfig('Secret', '');
		
//		if ($sSecretKey !== '' && $sSecretKey === $Secret)
//		{
			$oDownloadItem = new Classes\DownloadItem(self::GetName());
			$oDownloadItem->Populate($Data);

			$mResult = $this->getManager()->createDownload($oDownloadItem);
			
			$bResponse = $mResult && $oDownloadItem ? $oDownloadItem->id : false;
//		}
		
		return $bResponse;
	}
	
	public function DeleteItems($Ids)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		//file_put_contents("f:\web\domains\project8.dev\data\logs\log-2017-02-02.txt", json_encode($Ids));
		
		return $this->getManager()->deleteDownloads($Ids);
	}	
}
