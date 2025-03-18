<?php

declare(strict_types=1);

namespace DMK\MkContentAi\Backend\EventListener;

use DMK\MkContentAi\ContextMenu\ContentAiTranslationProvider;
use DMK\MkContentAi\Utility\PermissionsUtility;
use Psr\Http\Message\UriInterface;
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
        $currentTable = $event->getTable();
        $identifier = $event->getRecord()['uid'];

        if ($currentTable === 'tx_news_domain_model_news' &&
            !$event->hasAction('translateContentPlain') &&
            $this->permissionsUtility->userHasAccessToTextTranslationPromptButton()
        ) {
            $itemsConfiguration = $this->contentAiTranslationProvider->getItemsConfiguration();

            if (isset($itemsConfiguration['translateContentPlain'])) {
                $this->contentAiTranslationProvider->setContext($currentTable, (string)$identifier);
                $uriGenerated = $this->contentAiTranslationProvider->generateUrl('translateContentPlain');
                $labelActionName = LocalizationUtility::translate($itemsConfiguration['translateContentPlain']['label']);
                $translateContentPlainAction = $this->buildTranslateContentPlainAction($uriGenerated, $labelActionName);
                $event->setAction($translateContentPlainAction, 'translateContentPlain', 'secondary');
            }
        }
    }

    private function buildTranslateContentPlainAction(UriInterface $uriGenerated, ?string $labelActionName): string
    {
        switch ($this->typo3Version->getMajorVersion()) {
            case 11:
                return sprintf(
                    '<a href="%s" class="btn btn-default" title="%s">%s</a>',
                    $uriGenerated,
                    $labelActionName,
                    $this->iconFactory->getIcon('actions-translate', Icon::SIZE_SMALL)->render()
                );

            case 12:
                return sprintf(
                    '<a href="%s" class="dropdown-item dropdown-item-spaced" title="%s">%s</a>',
                    $uriGenerated,
                    $labelActionName,
                    $this->iconFactory->getIcon('actions-translate', Icon::SIZE_SMALL)->render()
                );

            default:
                return '';
        }
    }
}
