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
    public function createNewsRecord(
        News $record,
        string $title,
        string $teaser,
        string $bodyText,
        string $targetLanguageType,
        ?int $appendedContentUid,
        bool $showDisclaimer,
        bool $showLinkInOriginalRecord,
        ?string $urlPath
    ): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $currentTimestamp = time() + 1;
        $fullBodyText = $bodyText . ($showDisclaimer ? '<br><br>' . LocalizationUtility::translate('labelAiDisclaimer', 'mkcontentai') : '');
        $url = ($urlPath === '' ? '' : $urlPath) . $record->getPathSegment();

        $newsRecordData = [
            'pid' => $record->getPid(),
            'title' => '(Transformed into '.$targetLanguageType.' language) '. strip_tags($title),
            'teaser' => strip_tags($teaser),
            'bodytext' => $fullBodyText,
            'tx_mkcontentai_original_news_uid' => $record->getUid(),
            'datetime' => $currentTimestamp,
            'crdate' => $currentTimestamp,
            'tstamp' => $currentTimestamp,
            'path_segment' => $url,
        ];

        if ($appendedContentUid) {
            $newsRecordData['content_elements'] = $appendedContentUid;
        }

        $dataMap = [
            'tx_news_domain_model_news' => [
                'NEW_1' => $newsRecordData,
            ],
        ];

        $this->executeDataHandler($dataHandler, $dataMap);
        $newUid = $dataHandler->substNEWwithIDs['NEW_1'];

        if ($showLinkInOriginalRecord) {
            $this->updateOriginalRecord($dataHandler, $record, $url, $targetLanguageType);
        }
    }

    private function executeDataHandler(DataHandler $dataHandler, array $map): void
    {
        $dataHandler->start($map, []);
        $dataHandler->process_datamap();
    }

    private function updateOriginalRecord(DataHandler $dataHandler, News $record, string $translationSlug): void
    {
        $siteUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        $translatedNewsUrl = $siteUrl . $translationSlug . '.html';
        $linkText = sprintf(
            '<a href="%s">%s</a>',
            $translatedNewsUrl,
            LocalizationUtility::translate('labelTranslationLinkEasy', 'mkcontentai')
        );

        $originalBodyText = $record->getBodytext();
        $updatedBodyText = $linkText . '<br><br>' .  $originalBodyText;
        $originalUpdateMap = [
            'tx_news_domain_model_news' => [
                $record->getUid() => [
                    'bodytext' => $updatedBodyText,
                ]
            ]
        ];
        $this->executeDataHandler($dataHandler, $originalUpdateMap);
    }
}
