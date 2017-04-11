<?php
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Update script
 *
 * @package RENOLIT\NewsFalttnewsimport
 */
class ext_update
{

	/**
	 * @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue
	 * @inject
	 */
	protected $flashMessageQueue;

	/**
	 * @var string
	 */
	protected $extensionKey = 'news_falttnewsimport';

	/**
	 * Check if upgrade script is needed (this function is called from the extension manager)
	 *
	 * @return boolean
	 */
	public function access()
	{
		return $this->getBackendUserAuthentication()->isAdmin();
	}

	/**
	 * Run upgrade scripts (this function is called from the extension manager)
	 *
	 * @return string
	 */
	public function main()
	{
		$updateScriptLink = BackendUtility::getModuleUrl('tools_ExtensionmanagerExtensionmanager', [
				'tx_extensionmanager_tools_extensionmanagerextensionmanager' => [
					'extensionKey' => $this->extensionKey,
					'action' => 'show',
					'controller' => 'UpdateScript',
				],
		]);
		$view = $this->getView();
		$view->assignMultiple([
			'formAction' => $updateScriptLink,
		]);

		if ((int) $_POST['move-references'] === 1) {
			$this->moveReferences();
		}

		return $view->render();
	}

	/**
	 * Move file references from tt_news to news
	 *
	 * @return void
	 */
	protected function moveReferences()
	{
		$tables = [0 => 'tt_news', 1 => 'tx_news_domain_model_news', 2 => 'sys_file_reference'];
		$updatedNews = 0;
		$resetFileReferences = ['tx_falttnews_fal_images' => 0, 'tx_falttnews_fal_media' => 0];
		$movedReferences = 0;

		// get news records which were imported
		$news = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'uid, import_id, import_source', $tables[1], 'import_source LIKE \'TT_NEWS_IMPORT\''
		);
		//\TYPO3\CMS\Core\Utility\DebugUtility::debug($news);
		foreach ($news as $n) {
			$updateFields = [];
			// get original tt_news
			$tt_n = $this->getSingleTtNews($n['import_id']);
			if (!empty($tt_n)) {
				if (isset($tt_n['tx_falttnews_fal_images']) && (int) $tt_n['tx_falttnews_fal_images'] > 0) {
					$updateFields['fal_media'] = $tt_n['tx_falttnews_fal_images'];
				}
				if (isset($tt_n['tx_falttnews_fal_media']) && (int) $tt_n['tx_falttnews_fal_media'] > 0) {
					$updateFields['fal_related_files'] = $tt_n['tx_falttnews_fal_media'];
				}
			}
			// set the number of file references for news
			if (!empty($updateFields)) {
				$this->getDatabaseConnection()->exec_UPDATEquery($tables[1], 'uid = ' . (int) $n['uid'], $updateFields);
				$updatedNews++;
				$this->getDatabaseConnection()->exec_UPDATEquery($tables[0], 'uid = ' . (int) $n['import_id'], $resetFileReferences);
				// get file references
				$movedRef = $this->rewriteSingleNewsReferences($n['import_id'], $n['uid']);
				$movedReferences = $movedReferences + $movedRef;
			}
		}

		if ($updatedNews > 0) {
			$message = $this->getObjectManager()->get(
				'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', 'A total of ' . $movedReferences . ' file references for ' . $updatedNews . ' news has been moved.', 'Moving file references done', FlashMessage::OK
			);
		} else {
			$message = $this->getObjectManager()->get(
				'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', 'No file references for moving found.', 'Nothing done', FlashMessage::ERROR
			);
		}
		$this->getFlashMessageQueue()->enqueue($message);
	}

	/**
	 * rewrite sys_file_references for a single news entry
	 * @param integer $uid_foreign
	 * @param integer $uid_foreign_new
	 * @return integer
	 */
	protected function rewriteSingleNewsReferences($uid_foreign, $uid_foreign_new)
	{
		$movedReferences = 0;
		$table = 'sys_file_reference';
		// get single news record which were imported
		$references = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'uid, uid_foreign, tablenames, fieldname', $table, 'uid_foreign=' . (int) $uid_foreign . ' AND tablenames = \'tt_news\''
		);
		if (count($references) > 0) {
			foreach ($references as $reference) {
				if ($reference['fieldname'] === 'tx_falttnews_fal_images') {
					$updateFieldsMedia = [
						'uid_foreign' => $uid_foreign_new,
						'tablenames' => 'tx_news_domain_model_news',
						'fieldname' => 'fal_media'
					];
					$this->getDatabaseConnection()->exec_UPDATEquery($table, 'uid=' . (int) $reference['uid'] . ' AND tablenames=\'tt_news\' AND fieldname=\'tx_falttnews_fal_images\'', $updateFieldsMedia);
					$movedReferences++;
				}
				if ($reference['fieldname'] === 'tx_falttnews_fal_media') {
					$updateFieldsFiles = [
						'uid_foreign' => $uid_foreign_new,
						'tablenames' => 'tx_news_domain_model_news',
						'fieldname' => 'fal_related_files'
					];
					$this->getDatabaseConnection()->exec_UPDATEquery($table, 'uid=' . (int) $reference['uid'] . ' AND tablenames=\'tt_news\' AND fieldname=\'tx_falttnews_fal_media\'', $updateFieldsFiles);
					$movedReferences++;
				}
			}
		}
		return $movedReferences;
	}

	/**
	 * get single news by uid from table
	 * @param integer $uid
	 * @param string $table
	 * @return array
	 */
	protected function getSingleTtNews($uid)
	{
		// get single news record which were imported
		$news = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
			'uid, tx_falttnews_fal_images, tx_falttnews_fal_media', 'tt_news', 'uid=' . (int) $uid
		);
		if (isset($news['uid'])) {
			return $news;
		} else {
			return [];
		}
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager()
	{
		return GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection()
	{
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUserAuthentication()
	{
		return $GLOBALS['BE_USER'];
	}

	/**
	 * @return \TYPO3\CMS\Fluid\View\StandaloneView
	 */
	protected function getView()
	{
		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
		$view = $this->getObjectManager()->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:' . $this->extensionKey . '/Resources/Private/Templates/UpdateScript/Index.html'));
		$view->setLayoutRootPaths([GeneralUtility::getFileAbsFileName('EXT:' . $this->extensionKey . '/Resources/Private/Layouts')]);
		$view->setPartialRootPaths([GeneralUtility::getFileAbsFileName('EXT:' . $this->extensionKey . '/Resources/Private/Partials')]);
		return $view;
	}

	/**
	 * @return \TYPO3\CMS\Core\Messaging\FlashMessageQueue
	 */
	protected function getFlashMessageQueue()
	{
		if (!isset($this->flashMessageQueue)) {
			/** @var \TYPO3\CMS\Core\Messaging\FlashMessageService $flashMessageService */
			$flashMessageService = $this->getObjectManager()->get('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
			$this->flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('newsfalttnewsimport.errors');
		}
		return $this->flashMessageQueue;
	}
}
