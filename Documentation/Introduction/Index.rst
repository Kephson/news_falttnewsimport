﻿.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
================

This extension (news_falttnewsimport) makes it possible to import the file references of fal_ttnews in tt_news into news.

Requires to have news, news_ttnewsimport, and tt_news to be installed.

After the migration of tt_news and tt_news categories to news you could run the updater script of this extension to move the file references of fal_ttnews to the migrated news records. 

Features
--------

- easy migration, only run the update script in extension manager

Requirements
------------

- TYPO3 CMS >= 6.2.0
- Ext:news >= 3.0
- Ext:tt_news >= 3.5.0
- Ext:fal_ttnews >= 0.0.1
- Ext:news_ttnewsimport >= 2.0.0