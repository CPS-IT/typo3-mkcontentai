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

namespace DMK\MkContentAi\Service;

use DMK\MkContentAi\Domain\Repository\TtContentRepository;
use GeorgRinger\News\Domain\Repository\NewsRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use DMK\MkContentAi\Http\Client\SummAiClient;

class AiTranslationContentService
{
    public SummAiClient $summAiClient;
    public TtContentRepository $ttContentRepository;
    public NewsRepository $newsRepository;

    public function __construct(SummAiClient $summAiClient, TtContentRepository $ttContentRepository, NewsRepository $newsRepository)
    {
        $this->summAiClient = $summAiClient;
        $this->ttContentRepository = $ttContentRepository;
        if (ExtensionManagementUtility::isLoaded('news')) {
            $this->newsRepository = $newsRepository;
        }
    }

    public function getTranslation(string $inputText, string $userEmail, string $inputTextType, string $targetLanguageType, string $separator): \stdClass
    {
        return $this->summAiClient->sendContentToTranslate($inputText, $userEmail, $inputTextType, $targetLanguageType, $separator);
    }

    public function getSummAiUserEmail(): string
    {
        return $this->summAiClient->getUserEmail();
    }

    public function getRecordToTranslate(int $recordUid)
    {
        return $this->ttContentRepository->findByUid($recordUid);
    }

    public function getNewsRecordToTranslate(int $recordUid)
    {
        return $this->newsRepository->findByUid($recordUid);
    }

    public function getNewsContentToTranslate(int $recordUid): ?string
    {
        $ttContents = $this->ttContentRepository->findRelatedNews($recordUid);
        $cTypesValid = $this->summAiClient->getNewsContentTypes();
        $bodytext = null;
        foreach ($ttContents as $ttContent) {
            if(in_array($ttContent['CType'], $cTypesValid)) {
                $bodytext .= $ttContent['bodytext'];
            }
        }
        return $bodytext;
    }

    public function getSummAiAppendedContentUid(): ?int
    {
        return $this->summAiClient->getSummAiAppendedContentUid();
    }

    public function getSummAiDisclaimer(): bool
    {
        return $this->summAiClient->getSummAiDisclaimer();
    }

    public function getNewsInternalLinkUid(int $uid): ?int
    {
        $record = $this->newsRepository->findByUid($uid);
        $url = $record->getInternalurl();
        if (empty($url)) {
            return null;
        }
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['query'])) {
            return null;
        }
        parse_str($parsedUrl['query'], $queryString);
        return (int) $queryString['uid'];
    }
}
