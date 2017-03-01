<?php
/**
 * @copyright Copyright (c) 2016, Afterlogic Corp.
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

/**
 * CApiContactsManager class summary
 * 
 * @package ContactsMain
 */
class CApiAfterlogicDownloadsWebclientManager extends AApiManager
{
	private $oEavManager = null;

	/**
	 * @param CApiGlobalManager &$oManager
	 */
	public function __construct(CApiGlobalManager &$oManager, $sForcedStorage = 'db', AApiModule $oModule = null)
	{
		parent::__construct('', $oManager, $oModule);

		if ($oModule instanceof AApiModule)
		{
			$this->oEavManager = \CApi::GetSystemManager('eav', 'db');
		}
	}
	
	/**
	 * 
	 * @param string $sUUID
	 * @return \CContact
	 */
	public function getDownload($sUUID)
	{
		$oItem = $this->oEavManager->getEntity($sUUID);

		return $oItem;
	}
	
	/**
	 * Returns list of contacts which match the specified criteria 
	 * 
	 * @param int $iUserId User ID 
	 * @param string $sSearch Search pattern. Default value is empty string.
	 * @param string $sFirstCharacter If specified, will only return contacts with names starting from the specified character. Default value is empty string.
	 * @param string $sGroupUUID. Default value is **''**.
	 * @param int $iTenantId Group ID. Default value is null.
	 * @param bool $bAll Default value is null
	 * 
	 * @return int
	 */
	public function getDownloadsCount($aFilters = [])
	{
		return $this->oEavManager->getEntitiesCount(
			'CDownloadItem', 
			$aFilters
		);
	}

	/**
	 * Returns list of contacts within specified range, sorted according to specified requirements. 
	 * 
	 * @param int $iSortField Sort field. Accepted values:
	 *
	 *		EContactSortField::Name
	 *		EContactSortField::Email
	 *		EContactSortField::Frequency
	 *
	 * Default value is **EContactSortField::Email**.
	 * @param int $iSortOrder Sorting order. Accepted values:
	 *
	 *		ESortOrder::ASC
	 *		ESortOrder::DESC,
	 *
	 * for ascending and descending respectively. Default value is **ESortOrder::ASC**.
	 * @param int $iOffset Ordinal number of the contact item the list stars with. Default value is **0**.
	 * @param int $iRequestLimit The upper limit for total number of contacts returned. Default value is **20**.
	 * @param array $aFilters
	 * @param string $sGroupUUID
	 * @param array $aContactUUIDs
	 * 
	 * @return array|bool
	 */
	public function getDownloads($iSortField = EDownloadsSortField::Date, $iSortOrder = ESortOrder::ASC,
		$iOffset = 0, $iRequestLimit = 20, $aFilters = [], $aIds = [])
	{
		return $this->oEavManager->getEntities(
			'CDownloadItem', 
			array(),
			$iOffset,
			$iRequestLimit,
			$aFilters,
			$iSortField === EDownloadsSortField::Date ? 'Date' : 'iObjectId',
			$iSortOrder
//			$aIds
		);
	}

	/**
	 * The method is used for saving created contact to the database. 
	 * 
	 * @param CContact $oContact
	 * 
	 * @return bool
	 */
	public function createDownload($oItem)
	{
		$res = $this->oEavManager->saveEntity($oItem);
		
		return $res;
	}

	/**
	 * Deletes one or multiple contacts from address book.
	 * 
	 * @param array $aContactUUIDs Array of strings
	 * 
	 * @return bool
	 */
	public function deleteDownloads($aIds)
	{
		
		return $this->oEavManager->deleteEntities($aIds);
	}
}
