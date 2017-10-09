<?php

namespace Aurora\Modules\AfterlogicDownloadsWebclient;

class Module extends \Aurora\System\Module\AbstractWebclientModule
{
	public $oApiDownloadsManager = null;

	public $SxGeo = null;
	
	public function init() 
	{
        include(__DIR__ ."/SxGeo.php");

        $this->SxGeo = new \SxGeo(__DIR__.'/SxGeoCityMax.dat');
		$this->oApiDownloadsManager = new Manager($this);	
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
		if (!empty($oUser) && ($oUser->Role === \Aurora\System\Enums\UserRole::NormalUser && $this->isEnabledForEntity($oUser) || $oUser->Role === \Aurora\System\Enums\UserRole::SuperAdmin))
		{
			return array(
				'ItemsPerPage' => $this->getConfig('ItemsPerPage', 20)
//				'Login' => $oUser->{$this->GetName().'::Login'},
//				'HasPassword' => (bool) $oUser->{$this->GetName().'::Password'},
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
//				$oUser->{$this->GetName().'::Login'} = $Login;
//				$oUser->{$this->GetName().'::Password'} = $Password;
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
				&& !empty($oUser) && $oUser->Role === \Aurora\System\Enums\UserRole::NormalUser)
		{
			return array(
				'Login' => $oUser->{$this->GetName().'::Login'},
				'Password' => $oUser->{$this->GetName().'::Password'},
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
		
		$oUser = null;
		$oCoreDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
		if ($oCoreDecorator)
		{
			$oUser = $oCoreDecorator->GetUser($UserId);
		}
		if ($oUser)
		{
			return array(
				'EnableModule' => $this->isEnabledForEntity($oUser),
				'Login' => $oUser->{$this->GetName().'::Login'},
				'HasPassword' => (bool) $oUser->{$this->GetName().'::Password'}
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
		
		$oUser = null;
		$oCoreDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
		if ($oCoreDecorator)
		{
			$oUser = $oCoreDecorator->GetUser($UserId);
		}
		if ($oUser)
		{
			$this->updateEnabledForEntity($oUser, $EnableModule);
			
			if (!empty($Login) && !empty($Password))
			{
				$oUser->{$this->GetName().'::Login'} = $Login;
				$oUser->{$this->GetName().'::Password'} = $Password;
				
				return $oCoreDecorator->UpdateUserObject($oUser);
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
		
		$iCount = $this->oApiDownloadsManager->getDownloadsCount($aFilters);
		$aList = $this->oApiDownloadsManager->getDownloads(array(), $SortField, $SortOrder, $Offset, $Limit, $aFilters);

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

		$aList = $this->oApiDownloadsManager->getDownloads($aFields, Enums\SortField::Date, \Aurora\System\Enums\SortOrder::DESC, 0, 0, $aFilters);



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
		
		return $this->oApiDownloadsManager->getDownload((string)$UUID);
	}
	
	public function CreateDownload($Data)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
//		$bResponse = false;
//		$sSecretKey = $this->getConfig('Secret', '');
		
//		if ($sSecretKey !== '' && $sSecretKey === $Secret)
//		{
			$oDownloadItem = new Classes\DownloadItem($this->GetName());
			$oDownloadItem->Populate($Data);

			$mResult = $this->oApiDownloadsManager->createDownload($oDownloadItem);
			
			$bResponse = $mResult && $oDownloadItem ? $oDownloadItem->id : false;
//		}
		
		return $bResponse;
	}
	
	public function DeleteItems($Ids)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		//file_put_contents("f:\web\domains\project8.dev\data\logs\log-2017-02-02.txt", json_encode($Ids));
		
		return $this->oApiDownloadsManager->deleteDownloads($Ids);
	}	
}
