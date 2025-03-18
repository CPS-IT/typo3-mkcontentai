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

namespace DMK\MkContentAi\ContextMenu;

use DMK\MkContentAi\Domain\Model\TtContent;
use DMK\MkContentAi\Domain\Repository\TtContentRepository;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Backend\ContextMenu\ItemProviders\AbstractProvider;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentAiTranslationProvider extends AbstractProvider
{
    /**
     * @var array<string, array{
     *     type: string,
     *     label: string,
     *     iconIdentifier: string,
     *     callbackAction: string
     * }>
     */
    protected $itemsConfiguration = [
        'translateContentEasy' => [
            'type' => 'item',
            'label' => 'LLL:EXT:mkcontentai/Resources/Private/Language/locallang_contentai.xlf:labelTextTranslateContentEasy',
            'iconIdentifier' => 'actions-edit-copy',
            'callbackAction' => 'translateContentEasy',
        ],
        'translateContentPlain' => [
            'type' => 'item',
            'label' => 'LLL:EXT:mkcontentai/Resources/Private/Language/locallang_contentai.xlf:labelTextTranslateContentPlain',
            'iconIdentifier' => 'actions-edit-copy',
            'callbackAction' => 'translateContentPlain',
        ],
    ];

    private TtContentRepository $ttContentRepository;
    private Typo3Version $typo3Version;
    private UriBuilder $uriBuilder;

    public function __construct(string $table, string $identifier, string $context = '')
    {
        parent::__construct($table, $identifier, $context);

        // Use DI once this is supported by TYPO3
        $this->ttContentRepository = GeneralUtility::makeInstance(TtContentRepository::class);
        $this->typo3Version = new Typo3Version();
        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
    }

    public function setContext(string $table, string $identifier, string $context = ''): void
    {
        $this->table = $table;
        $this->identifier = $identifier;
        $this->context = $context;
    }

    public function canHandle(): bool
    {
        return 'tt_content' === $this->table;
    }

    public function getPriority(): int
    {
        return 55;
    }

    /**
     * @param array<string, array{
     *     type: string,
     *     label: string,
     *     iconIdentifier: string,
     *     additionalAttributes: array<string,string>,
     *     callbackAction: string
     * }> $items
     *
     * @return array<string, array{
     *     type: string,
     *     label: string,
     *     iconIdentifier: string,
     *     additionalAttributes: array<string,string>,
     *     callbackAction: string
     * }>
     */
    public function addItems(array $items): array
    {
        $this->initDisabledItems();

        return $items + $this->prepareItems($this->itemsConfiguration);
    }

    public function getItemsConfiguration(): array
    {
        return $this->itemsConfiguration;
    }

    /**
     * This method is called for each item this provider adds and checks if given item can be added.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canRender(string $itemName, string $type): bool
    {
        if (in_array($itemName, ['translateContentEasy', 'translateContentPlain'], true)) {
            return $this->isPageContent() && $this->isValidTypeOfRecord((int) $this->identifier);
        }

        return false;
    }

    public function isPageContent(): bool
    {
        return 'tt_content' === $this->table;
    }

    public function generateUrl(string $itemName): UriInterface
    {
        $parameters = $this->typo3Version->getMajorVersion() === 11
            ? $this->getParametersForVersion11($itemName)
            : $this->getParametersForVersion12();
        $pathInfo = $this->getPathInfo($itemName);

        return $this->uriBuilder->buildUriFromRoutePath($pathInfo, $parameters);
    }

    /**
     * @return array<string>
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        $extendUrl = $this->generateUrl($itemName);

        switch ($this->typo3Version->getMajorVersion()) {
            case 12:
                return [
                    'data-callback-module' => '@t3docs/mkcontentai/context-menu-actions',
                    'data-navigate-uri' => (string) $extendUrl,
                ];
            case 11:
                return [
                    'data-callback-module' => 'TYPO3/CMS/Mkcontentai/ContextMenu',
                    'data-navigate-uri' => (string) $extendUrl,
                ];
            default:
                throw new \RuntimeException('TYPO3 version not supported');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getParametersForVersion11(string $itemName): array
    {
        if ($itemName === 'translateContentEasy' || $itemName === 'translateContentPlain') {
            return [
                'tx_mkcontentai_system_mkcontentaicontentai' => [
                    'controller' => 'AiTranslation',
                    'action' => $itemName,
                    'uid' => $this->identifier,
                    'table' => $this->table,
                ],
            ];
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function getParametersForVersion12(): array
    {
        return ['uid' => $this->identifier, 'table' => $this->table];
    }

    private function getPathInfo(string $itemName): string
    {
        $pathInfoMapping = [
            'translateContentEasy' => [
                12 => '/module/mkcontentai/AiTranslation/translateContentEasy',
                11 => '/module/system/MkcontentaiContentai',
            ],
            'translateContentPlain' => [
                12 => '/module/mkcontentai/AiTranslation/translateContentPlain',
                11 => '/module/system/MkcontentaiContentai',
            ],
        ];

        return $pathInfoMapping[$itemName][$this->typo3Version->getMajorVersion()];
    }

    private function isValidTypeOfRecord(int $uid): bool
    {
        /** @var TtContent|null $record */
        $record = $this->ttContentRepository->findByUid($uid);
        $recordType = $record === null ? '' : $record->getCtype();

        return in_array($recordType, ['text', 'textpic', 'textmedia'], true);
    }
}
