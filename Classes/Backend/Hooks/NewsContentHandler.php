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

use GeorgRinger\News\Domain\Model\News;
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
        bool $showDisclaimer
    ): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $fullDisclaimer = sprintf(
            '<em>%s</em><br><em>%s</em>',
            LocalizationUtility::translate('labelAiDisclaimer', 'mkcontentai'),
            LocalizationUtility::translate('labelAiDisclaimer2', 'mkcontentai')
        );
        $fullBodyText = $bodyText . ($showDisclaimer ? '<br><br>' . $fullDisclaimer : '');

        $newsRecordData = [
            'pid' => $record->getPid(),
            'title' => '(Transformed into '.$targetLanguageType.' language) '. strip_tags($title),
            'teaser' => strip_tags($teaser),
            'bodytext' => $fullBodyText,
            'tx_mkcontentai_original_news' => $record->getUid(),
            'datetime' => $record->getDatetime()->getTimestamp(),
            'crdate' => time(),
            'tstamp' => time(),
        ];

        if ($appendedContentUid > 0) {
            $newsRecordData['content_elements'] = $appendedContentUid;
        }

        $dataMap = [
            'tx_news_domain_model_news' => [
                'NEW_1' => $newsRecordData,
            ],
        ];

        $this->executeDataHandler($dataHandler, $dataMap);

        $newUid = $dataHandler->substNEWwithIDs['NEW_1'];
        $this->updateOriginalRecord($dataHandler, $record, $newUid);
    }

    private function updateOriginalRecord(DataHandler $dataHandler, News $record, int $translatedUid): void
    {
        $originalUpdateMap = [
            'tx_news_domain_model_news' => [
                $record->getUid() => [
                    'tx_mkcontentai_translated_news' => $translatedUid,
                ],
            ],
        ];
        $this->executeDataHandler($dataHandler, $originalUpdateMap);
    }

    private function executeDataHandler(DataHandler $dataHandler, array $map): void
    {
        $dataHandler->start($map, []);
        $dataHandler->process_datamap();
    }
}
