<?php

class AfterlogicDownloadsWebclientModule extends AApiModule
{
	public $oApiDownloadsManager = null;
	
	public function init() 
	{
		$this->incClass('enum');
		$this->incClass('donwloadItem');
		
		$this->oApiDownloadsManager = $this->GetManager();
		
		// $this->extendObject('CUser', array(
				// 'Login' => array('string', ''),
				// 'Password' => array('string', '')
			// )
		// );
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
		\CApi::checkUserRoleIsAtLeast(\EUserRole::Anonymous);
		
		$oUser = \CApi::getAuthenticatedUser();
		if (!empty($oUser) && ($oUser->Role === \EUserRole::NormalUser && $this->isEnabledForEntity($oUser) || $oUser->Role === \EUserRole::SuperAdmin))
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
//			\CApi::checkUserRoleIsAtLeast(\EUserRole::SuperAdmin);
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
//			\CApi::checkUserRoleIsAtLeast(\EUserRole::NormalUser);
//			$oUser = \CApi::getAuthenticatedUser();
//			if ($oUser)
//			{
//				$oCoreDecorator = \CApi::GetModuleDecorator('Core');
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
		\CApi::checkUserRoleIsAtLeast(\EUserRole::NormalUser);
		
		$oUser = \CApi::getAuthenticatedUser();
		
		$iAuthMode = $this->getConfig('AuthMode', EIframeAppAuthMode::NoAuthentication);
				
		if (($iAuthMode === EIframeAppAuthMode::CustomCredentialsSetByUser || $iAuthMode === EIframeAppAuthMode::CustomCredentialsSetByAdmin)
				&& !empty($oUser) && $oUser->Role === \EUserRole::NormalUser)
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
		\CApi::checkUserRoleIsAtLeast(\EUserRole::SuperAdmin);
		
		$oUser = null;
		$oCoreDecorator = \CApi::GetModuleDecorator('Core');
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
		\CApi::checkUserRoleIsAtLeast(\EUserRole::SuperAdmin);
		
		$oUser = null;
		$oCoreDecorator = \CApi::GetModuleDecorator('Core');
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
	
	public function GetItems($Offset = 0, $Limit = 20, $SortField = EDownloadsSortField::Date, $SortOrder = ESortOrder::DESC, $Search = '', $Filters = array())
	{
		\CApi::checkUserRoleIsAtLeast(\EUserRole::NormalUser);

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
		
//		$aFilters = [];
//		$SortField = 'id';
		
		$iCount = $this->oApiDownloadsManager->getDownloadsCount($aFilters);
		$aList = $this->oApiDownloadsManager->getDownloads($SortField, $SortOrder, $Offset, $Limit, $aFilters);
		
//		$aList = array(
//			[
//				'id' => 1,
//				'date' => '01.02.2017'
//			],
//			[
//				'id' => 2,
//				'date' => '02.02.2017'
//			],
//			[
//				'id' => 3,
//				'date' => '03.02.2017'
//			]
//		);

		return array(
			'ItemsCount' => $iCount,
			'List' => \CApiResponseManager::GetResponseObject($aList)
		);
	}
	
	/**
	 * Returns contact with specified UUID.
	 * @param string $UUID UUID of contact to return.
	 * @return \CContact
	 */
	public function GetItem($UUID)
	{
		\CApi::checkUserRoleIsAtLeast(\EUserRole::NormalUser);
		
		return $this->oApiDownloadsManager->getDownload((string)$UUID);
	}
	
	public function CreateDownload($Data)
	{
//		\CApi::checkUserRoleIsAtLeast(\EUserRole::NormalUser);
		
//		$oUser = \CApi::getAuthenticatedUser();
		
//		if ($iUserId > 0 && $iUserId !== $oUser->iId)
//		{
//			\CApi::checkUserRoleIsAtLeast(\EUserRole::SuperAdmin);
//			
//			$oCoreDecorator = \CApi::GetModuleDecorator('Core');
//			if ($oCoreDecorator)
//			{
//				$oUser = $oCoreDecorator->GetUser($iUserId);
//			}
//		}
		
		$oDownloadItem = \CDownloadItem::createInstance();
		$oDownloadItem->Populate($Data);

		$mResult = $this->oApiDownloadsManager->createDownload($oDownloadItem);
		return $mResult && $oDownloadItem ? $oDownloadItem->id : false;
	}
	
	public function DeleteItems($Ids)
	{
		\CApi::checkUserRoleIsAtLeast(\EUserRole::NormalUser);
		
		file_put_contents("f:\web\domains\project8.dev\data\logs\log-2017-02-02.txt", json_encode($Ids));
		
		return $this->oApiDownloadsManager->deleteDownloads($Ids);
	}	
}
