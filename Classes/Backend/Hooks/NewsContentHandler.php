<?php

declare(strict_types=1);

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of TYPO3 CMS-based extension "mkcontentai" by DMK E-BUSINESS GmbH.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace DMK\MkContentAi\Backend\Hooks;

use DMK\MkContentAi\Domain\Model\News;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class NewsContentHandler
{
    public function createNewsRecord(News $record, string $title, string $teaser, string $bodyText, string $targetLanguageType): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $currentTimestamp = time() + 1;

        $translationInfo = LocalizationUtility::translate(
            'labelNewsTranslation' . ucwords($targetLanguageType) .'Source',
            'mkcontentai'
        );
        $bodyTextWithLink = sprintf(
            $translationInfo . '</br>' . $bodyText,
            GeneralUtility::getIndpEnv('TYPO3_SITE_URL'),
            $record->getPathSegment(),
            $record->getTitle(),
            date('d.m.Y', $record->getCrDate())
        );

        $newsRecordData = [
            'pid' => $record->getPid(),
            'title' => '(Transformed into '.$targetLanguageType.' language) '. strip_tags($title),
            'teaser' => strip_tags($teaser),
            'bodytext' => $bodyTextWithLink,
            'tx_mkcontentai_original_news_uid' => $record->getUid(),
            'datetime' => $currentTimestamp,
            'crdate' => $currentTimestamp,
            'tstamp' => $currentTimestamp,
            'path_segment' => $targetLanguageType . '/' . $record->getPathSegment(),
        ];

        $dataMap = [
            'tx_news_domain_model_news' => [
                'NEW_1' => $newsRecordData,
            ],
        ];

        $dataHandler->start($dataMap, []);
        $dataHandler->process_datamap();

    }
}
