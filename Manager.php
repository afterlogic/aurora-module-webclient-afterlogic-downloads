<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\AfterlogicDownloadsWebclient;

/**
 * CApiContactsManager class summary
 * 
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 * 
 * @package ContactsMain
 */
class Manager extends \Aurora\System\Managers\AbstractManager
{
	private $oEavManager = null;

	/**
	 * 
	 * @param \Aurora\System\Module\AbstractModule $oModule
	 */
	public function __construct(\Aurora\System\Module\AbstractModule $oModule = null)
	{
		parent::__construct($oModule);

		if ($oModule instanceof \Aurora\System\Module\AbstractModule)
		{
			$this->oEavManager = \Aurora\System\Managers\Eav::getInstance();
		}
	}
	
	/**
	 * 
	 * @param string $sUUID
	 * @return Aurora\Modules\AfterlogicDownloadsWebclient\Classes
	 */
	public function getDownload($sUUID)
	{
		return $this->oEavManager->getEntity($sUUID, \Aurora\Modules\AfterlogicDownloadsWebclient\Classes\DownloadItem::class);
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
			\Aurora\Modules\AfterlogicDownloadsWebclient\Classes\DownloadItem::class,
			$aFilters
		);
	}

	/**
	 * Returns list of contacts within specified range, sorted according to specified requirements. 
	 * 
	 * @param int $iSortField Sort field. Accepted values:
	 *
	 *		\Aurora\Modules\Contacts\Enums\SortField::Name
	 *		\Aurora\Modules\Contacts\Enums\SortField::Email
	 *		\Aurora\Modules\Contacts\Enums\SortField::Frequency
	 *
	 * Default value is **\Aurora\Modules\Contacts\Enums\SortField::Email**.
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
	public function getDownloads($aFields = array(), $iSortField = Enums\SortField::Date, $iSortOrder = \Aurora\System\Enums\SortOrder::ASC,
		$iOffset = 0, $iRequestLimit = 20, $aFilters = [], $aIds = [])
	{
		return $this->oEavManager->getEntities(
			\Aurora\Modules\AfterlogicDownloadsWebclient\Classes\DownloadItem::class,
			$aFields,
			$iOffset,
			$iRequestLimit,
			$aFilters,
			$iSortField === Enums\SortField::Date ? 'Date' : 'EntityId',
			$iSortOrder
//			$aIds
		);
	}

	/**
	 * The method is used for saving created contact to the database. 
	 * 
	 * @param \Aurora\Modules\Contacts\Classes\Contact $oContact
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
