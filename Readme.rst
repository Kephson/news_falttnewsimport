TYPO3 extension "news_falttnewsimport"
===================================

This extension imports file and media references from `EXT:fal_ttnews` to `EXT:news`.

**Requirements**

* TYPO3 CMS >= 6.2.0
* Ext:news >= 3.0.0
* Ext:news_ttnewsimport >= 2.0.0
* Ext:tt_news >= 3.5
* Ext:fal_ttnews >= 0.0.1 (https://typo3.org/extensions/repository/view/fal_ttnews or https://github.com/Kephson/fal_ttnews)

**License**

GPL v2


Migrate records
---------------


The file and media references in `tt_news` are moved to `tx_news_domain_model_news`. 
This means the old tt_news records will not have any file and media references after migration!



Usage
^^^^^

* After installing the extension, switch to the extension manager and run the update script
* Select the wizard you need and press *Start*

