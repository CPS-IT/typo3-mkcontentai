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

use DMK\MkContentAi\Domain\Model\TtContent;
use DMK\MkContentAi\Domain\Repository\TtContentRepository;
use DMK\MkContentAi\Domain\Model\News;
use DMK\MkContentAi\Domain\Repository\NewsRepository;
use DMK\MkContentAi\Http\Client\SummAiClient;
use function _PHPStan_a3459023a\RingCentral\Psr7\str;

class AiTranslationContentService
{
    public SummAiClient $summAiClient;
    public TtContentRepository $ttContentRepository;
    public NewsRepository $newsRepository;

    public function __construct(SummAiClient $summAiClient, TtContentRepository $ttContentRepository, NewsRepository $newsRepository)
    {
        $this->summAiClient = $summAiClient;
        $this->ttContentRepository = $ttContentRepository;
        $this->newsRepository = $newsRepository;
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

    public function getNewsContentToTranslate(int $recordUid): string
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
}
