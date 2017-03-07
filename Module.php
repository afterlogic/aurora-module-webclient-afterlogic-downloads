<?php

namespace Aurora\Modules\AfterlogicDownloadsWebclient;

use \Aurora\Modules\Enums;

class Module extends \Aurora\System\Module\AbstractWebclientModule
{
	public $oApiDownloadsManager = null;
	
	public function init() 
	{
		$this->incClasses(array(
				'enum',
				'donwloadItem'
			)
		);
		
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
		\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::Anonymous);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
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
//			\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::SuperAdmin);
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
//			\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::NormalUser);
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
		\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::NormalUser);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		
		$iAuthMode = $this->getConfig('AuthMode', \EIframeAppAuthMode::NoAuthentication);
				
		if (($iAuthMode === \EIframeAppAuthMode::CustomCredentialsSetByUser || $iAuthMode === \EIframeAppAuthMode::CustomCredentialsSetByAdmin)
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
		\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::SuperAdmin);
		
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
		\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::SuperAdmin);
		
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
	
	public function GetItems($Offset = 0, $Limit = 20, $SortField = Enums\DownloadsSortField::Date, $SortOrder = \ESortOrder::DESC, $Search = '', $Filters = array())
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::NormalUser);

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
			'List' => \Aurora\System\Managers\Response::GetResponseObject($aList)
		);
	}
	
	/**
	 * Returns contact with specified UUID.
	 * @param string $UUID UUID of contact to return.
	 * @return \CContact
	 */
	public function GetItem($UUID)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::NormalUser);
		
		return $this->oApiDownloadsManager->getDownload((string)$UUID);
	}
	
	public function CreateDownload($Data)
	{
//		\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::NormalUser);
		
//		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		
//		if ($iUserId > 0 && $iUserId !== $oUser->iId)
//		{
//			\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::SuperAdmin);
//			
//			$oCoreDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
//			if ($oCoreDecorator)
//			{
//				$oUser = $oCoreDecorator->GetUser($iUserId);
//			}
//		}
		
		$oDownloadItem = new \CDownloadItem($this->GetName());
		$oDownloadItem->Populate($Data);

		$mResult = $this->oApiDownloadsManager->createDownload($oDownloadItem);
		return $mResult && $oDownloadItem ? $oDownloadItem->id : false;
	}
	
	public function DeleteItems($Ids)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\EUserRole::NormalUser);
		
		file_put_contents("f:\web\domains\project8.dev\data\logs\log-2017-02-02.txt", json_encode($Ids));
		
		return $this->oApiDownloadsManager->deleteDownloads($Ids);
	}	
}
