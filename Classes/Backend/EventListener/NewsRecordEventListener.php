<?php

declare(strict_types=1);

namespace DMK\MkContentAi\Backend\EventListener;

use DMK\MkContentAi\ContextMenu\ContentAiTranslationProvider;
use DMK\MkContentAi\Utility\PermissionsUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Recordlist\Event\ModifyRecordListRecordActionsEvent;

final class NewsRecordEventListener
{
    private ContentAiTranslationProvider $contentAiTranslationProvider;
    private IconFactory $iconFactory;
    private PermissionsUtility $permissionsUtility;
    private Typo3Version $typo3Version;

    public function __construct(IconFactory $iconFactory, PermissionsUtility $permissionsUtility, Typo3Version $typo3Version)
    {
        $this->iconFactory = $iconFactory;
        $this->permissionsUtility = $permissionsUtility;
        $this->typo3Version = $typo3Version;

        if (11 === $this->typo3Version->getMajorVersion()) {
            $this->contentAiTranslationProvider = GeneralUtility::makeInstance(ContentAiTranslationProvider::class, '', '');
        }

        if (12 === $this->typo3Version->getMajorVersion()) {
            $this->contentAiTranslationProvider = GeneralUtility::makeInstance(ContentAiTranslationProvider::class);
        }
    }

    public function __invoke(ModifyRecordListRecordActionsEvent $event): void
    {
        $itemsConfiguration = $this->contentAiTranslationProvider->getItemsConfiguration();
        $currentTable = $event->getTable();
        $record = $event->getRecord();
        $identifier = (int)$record['uid'];

        // Early return on unsupported table, if news was already processed for translation
        // or user does not have permission to perform translation
        if ($currentTable !== 'tx_news_domain_model_news'
            || (int)($record['tx_mkcontentai_original_news'] ?? 0) > 0
            || (int)($record['tx_mkcontentai_translated_news'] ?? 0) > 0
        ) {
            return;
        }

        if (!$event->hasAction('translateContentEasy')
            && isset($itemsConfiguration['translateContentEasy'])
            && $this->permissionsUtility->userHasAccessToNewsTranslationEasyLanguage()
        ) {
            $actionLink = $this->createAction('translateContentEasy', $identifier, $itemsConfiguration);
            $event->setAction($actionLink, 'translateContentEasy', 'secondary');
        }

        if (!$event->hasAction('translateContentPlain')
            && isset($itemsConfiguration['translateContentPlain'])
            && $this->permissionsUtility->userHasAccessToNewsTranslationPlainLanguage()
        ) {
            $actionLink = $this->createAction('translateContentPlain', $identifier, $itemsConfiguration);
            $event->setAction($actionLink, 'translateContentPlain', 'secondary');
        }
    }

    private function createAction(string $action, int $recordIdentifier, array $itemsConfiguration): string
    {
        $this->contentAiTranslationProvider->setContext('tx_news_domain_model_news', (string)$recordIdentifier);

        if ($this->typo3Version->getMajorVersion() === 11) {
            $classNames = 'btn btn-default';
        } else {
            $classNames = 'dropdown-item dropdown-item-spaced';
        }

        return sprintf(
            '<a href="%s" class="%s" title="%s">%s</a>',
            $this->contentAiTranslationProvider->generateUrl($action),
            $classNames,
            LocalizationUtility::translate($itemsConfiguration[$action]['label']),
            $this->iconFactory->getIcon('actions-translate', Icon::SIZE_SMALL)->render()
        );
    }
}
