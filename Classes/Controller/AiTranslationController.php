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

namespace DMK\MkContentAi\Controller;

use DMK\MkContentAi\Backend\Hooks\NewsContentHandler;
use DMK\MkContentAi\Backend\Hooks\PageContentHandler;
use DMK\MkContentAi\Service\AiTranslationContentService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class AiTranslationController extends BaseController
{
    private AiTranslationContentService $aiTranslationService;
    private PageContentHandler $pageContentHandler;
    private NewsContentHandler $newsContentHandler;

    public function __construct(AiTranslationContentService $aiTranslationService, PageContentHandler $pageContentHandler, NewsContentHandler $newsContentHandler, PageRenderer $pageRenderer)
    {
        $this->aiTranslationService = $aiTranslationService;
        $this->pageContentHandler = $pageContentHandler;
        $this->newsContentHandler = $newsContentHandler;
        $pageRenderer->addCssFile('EXT:mkcontentai/Resources/Public/Css/base.css');
    }

    public function translateContentEasyAction(int $uid = 0, string $table = 'tt_content', string $inputTextType = 'plain_text', string $targetLanguageType = 'easy', string $separator = 'hyphen'): ResponseInterface
    {
        return $this->translateContent($uid, $table, $inputTextType, $targetLanguageType, $separator);
    }

    public function translateContentPlainAction(int $uid = 0, string $table = 'tt_content', string $inputTextType = 'html', string $targetLanguageType = 'easy', string $separator = 'hyphen'): ResponseInterface
    {
        return $this->translateContent($uid, $table, $inputTextType, $targetLanguageType, $separator);
    }

    private function translateContent(int $uid, string $table, string $inputTextType, string $targetLanguageType, string $separator): ResponseInterface
    {
        if($table === 'tx_news_domain_model_news') {
            $record = $this->aiTranslationService->getNewsRecordToTranslate($uid);
            $linkedNewsUid = $this->aiTranslationService->getNewsInternalLinkUid($uid);
            if($linkedNewsUid) {
                $linkedRecord = $this->aiTranslationService->getNewsRecordToTranslate($linkedNewsUid);
            }
        } else {
            $record = $this->aiTranslationService->getRecordToTranslate($uid);
        }

        if(!$record) {
            return $this->processError('labelErrorRecordSelected');
        }

        if($table === 'tx_news_domain_model_news') {
            $bodyTextToTranslate = $this->aiTranslationService->getNewsContentToTranslate($linkedNewsUid ?? $uid);
            if (!$bodyTextToTranslate) {
                return $this->processError('labelErrorNewsContent');
            }
        } else {
            $bodyTextToTranslate = $record->getBodytext();
        }

        try {
            if($table === 'tx_news_domain_model_news') {
                $title = ($linkedRecord ?? $record)->getTitle();
                $teaser = ($linkedRecord ?? $record)->getTeaser();
                if($title) $translatedTitle = $this->aiTranslationService->getTranslation($title, $this->aiTranslationService->getSummAiUserEmail(), $inputTextType, $targetLanguageType, $separator);
                if($teaser) $translatedTeaser = $this->aiTranslationService->getTranslation($teaser, $this->aiTranslationService->getSummAiUserEmail(), $inputTextType, $targetLanguageType, $separator);
                $translatedText = $this->aiTranslationService->getTranslation($bodyTextToTranslate, $this->aiTranslationService->getSummAiUserEmail(), $inputTextType, $targetLanguageType, $separator);

                $appendedContentUid = $this->aiTranslationService->getSummAiAppendedContentUid();
                $showDisclaimer = $this->aiTranslationService->getSummAiDisclaimer();

                $this->newsContentHandler->createNewsRecord(
                    $record,
                    $translatedTitle->translated_text ?? '',
                    $translatedTeaser->translated_text ?? '',
                    $translatedText->translated_text ?? '',
                    $targetLanguageType,
                    $appendedContentUid,
                    $showDisclaimer,
                    $linkedNewsUid ?? null
                );
            } else {
                $translatedText = $this->aiTranslationService->getTranslation($bodyTextToTranslate, $this->aiTranslationService->getSummAiUserEmail(), $inputTextType, $targetLanguageType, $separator);
                $this->pageContentHandler->copyContentRecord($record->getUid(), $record->getPid(), $translatedText->translated_text, $targetLanguageType);
            }
        } catch (\Exception $e) {
            $this->addFlashMessage($e->getMessage(), '', AbstractMessage::ERROR);
            return $this->handleResponse();
        }

        return $this->buildUrl($record->getPid(), $table);
    }

    private function processError(string $msgKey): ResponseInterface
    {
        $response = new ForwardResponse('filelist');
        $translatedMessage = LocalizationUtility::translate($msgKey, 'mkcontentai') ?? '';
        $this->addFlashMessage($translatedMessage, '', AbstractMessage::ERROR);
        return $response->withControllerName('AiImage');
    }
    private function buildUrl(int $recordPid, $table): ResponseInterface
    {
        $routeName = $table === 'tx_news_domain_model_news' ? 'web_list' : 'web_layout';
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $recordUrl = $uriBuilder->buildUriFromRoute($routeName, [
            'id' => $recordPid,
        ]);

        $redirectResponse = GeneralUtility::makeInstance(RedirectResponse::class, $recordUrl);

        return $redirectResponse;
    }

    protected function handleResponse(): ResponseInterface
    {
        if (null === $this->moduleTemplateFactory) {
            $translatedMessage = LocalizationUtility::translate('labelErrorModuleTemplateFactory', 'mkcontentai') ?? '';
            throw new \Exception($translatedMessage, 1623345720);
        }

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($moduleTemplate->renderContent());
    }
}
