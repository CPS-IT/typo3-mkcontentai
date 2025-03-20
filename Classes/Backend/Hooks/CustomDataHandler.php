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

use DMK\MkContentAi\Service\AiAltTextLogsService;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class CustomDataHandler
{
    private AiAltTextLogsService $altTextLogsService;
    private ConnectionPool $connectionPool;

    public function __construct(AiAltTextLogsService $altTextLogsService, ConnectionPool $connectionPool)
    {
        $this->altTextLogsService = $altTextLogsService;
        $this->connectionPool = $connectionPool;
    }

    /**
     * This method is called after the DataHandler has processed the field array.
     *
     * @param string                    $status      The status, such as 'new', 'update', 'delete'
     * @param string                    $table       The database table being processed
     * @param int                       $recordId    The record ID being processed
     * @param array<string, string|int> $fieldArray  The field array being processed
     * @param DataHandler               $dataHandler Reference to the DataHandler object
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * @return void
     */
    public function processDatamap_postProcessFieldArray($status, $table, $recordId, $fieldArray, DataHandler $dataHandler)
    {
        if ('update' === $status && 'sys_file_metadata' === $table && isset($fieldArray['alternative'])) {
            $fileMetadataUid = $recordId;
            /** @var string $alternativeNewMetadata */
            $alternativeNewMetadata = $fieldArray['alternative'];

            $this->altTextLogsService->deleteLogsByFileMetadata($fileMetadataUid, $alternativeNewMetadata);
        }
    }

    /**
     * Detach news translation from original news if translation is deleted.
     *
     * This method is called right before the news translation is deleted using DataHandler.
     * It queries the original news and, if found, detaches the translation from it.
     */
    public function processCmdmap_preProcess(string $command, string $table, int $id): void
    {
        // We only support deletions of news translations at the moment
        if ($command !== 'delete' || $table !== 'tx_news_domain_model_news') {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_news_domain_model_news');
        $queryBuilder->getRestrictions()->removeAll();
        $originalNews = $queryBuilder->select('tx_mkcontentai_original_news')
            ->from('tx_news_domain_model_news')
            ->where(
                $queryBuilder->expr()->gt('tx_mkcontentai_original_news', 0),
                $queryBuilder->expr()->eq('uid', $id)
            )
            ->execute()
            ->fetchOne();

        if ($originalNews > 0) {
            $this->detachTranslationFromOriginalNews($originalNews);
        }
    }

    private function detachTranslationFromOriginalNews(int $id): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_news_domain_model_news');
        $connection->update(
            'tx_news_domain_model_news',
            [
                'tx_mkcontentai_translated_news' => 0,
            ],
            [
                'uid' => $id,
            ],
            [
                'tx_mkcontentai_translated_news' => Connection::PARAM_INT,
                'uid' => Connection::PARAM_INT,
            ]
        );
    }
}
