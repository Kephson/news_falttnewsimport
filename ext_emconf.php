<?php
/* * *************************************************************
 * Extension Manager/Repository config file for ext "news_falttnewsimport".
 *
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 * ************************************************************* */

$EM_CONF[$_EXTKEY] = array(
	'title' => 'fal_ttnews importer',
	'description' => 'Importer of ext:fal_ttnews file references to ext:news',
	'category' => 'be',
	'author' => 'Ephraim Härer',
	'author_email' => 'ephraim.haerer@renolit.com',
	'company' => 'RENOLIT SE',
	'state' => 'stable',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearCacheOnLoad' => 1,
	'version' => '1.0.1',
	'constraints' =>
	array(
		'depends' =>
		array(
			'typo3' => '6.2.0-8.99.99',
			'php' => '5.3.0-7.1.99',
			'news' => '3.0.0-6.99.99',
			'tt_news' => '3.5.0-7.99.99',
			'fal_ttnews' => '0.0.1-1.99.99',
			'news_ttnewsimport' => '2.0.0-2.99.99',
		),
		'conflicts' =>
		array(
		),
		'suggests' =>
		array(
		),
	),
	'clearcacheonload' => true,
	'author_company' => NULL,
);

