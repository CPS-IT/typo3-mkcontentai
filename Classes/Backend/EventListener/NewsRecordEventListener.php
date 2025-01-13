<?php

namespace DMK\MkContentAi\Backend\EventListener;

use DMK\MkContentAi\ContextMenu\ContentAiTranslationProvider;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Recordlist\Event\ModifyRecordListRecordActionsEvent;

class NewsRecordEventListener
{
    protected ContentAiTranslationProvider $contentAiTranslationProvider;
    private Typo3Version $typo3Version;

    public function __construct(Typo3Version $typo3Version)
    {
        $this->typo3Version = $typo3Version;

        if (11 === $this->typo3Version->getMajorVersion()) {
            $this->contentAiTranslationProvider = GeneralUtility::makeInstance(ContentAiTranslationProvider::class, '', '');
        }

        if (12 === $this->typo3Version->getMajorVersion()) {
            $this->contentAiTranslationProvider = GeneralUtility::makeInstance(ContentAiTranslationProvider::class);
        }
    }

    public function modifyRecordListActions(ModifyRecordListRecordActionsEvent $event): void
    {
        $currentTable = $event->getTable();
        $identifier = $event->getRecord()['uid'];
        if ($currentTable === 'tx_news_domain_model_news' && !$event->hasAction('translateContentPlain')) {
            $itemsConfiguration = $this->contentAiTranslationProvider->getItemsConfiguration();
            if(isset($itemsConfiguration['translateContentPlain'])) {
                $this->contentAiTranslationProvider->setContext($currentTable, $identifier);
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
                $translateContentPlainAction = '
        <a href='.$uriGenerated.' class="btn btn-default" title="'.$labelActionName.'"><span class="t3js-icon icon icon-size-small icon-state-default">
	<span class="icon-markup">
<svg class="icon-color"><use xlink:href="/typo3/sysext/core/Resources/Public/Icons/T3Icons/sprites/actions.svg#actions-translate" /></svg>
	</span>
</span></a>';

                return $translateContentPlainAction;

            case 12:
                $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
                $translateContentPlainAction = '
        <a href='.$uriGenerated.' class="dropdown-item dropdown-item-spaced"  title="'.$labelActionName.'"><span class="t3js-icon icon icon-size-small icon-state-default icon-actions-translate">
	<span class="icon-markup">
	'.$iconFactory->getIcon('actions-translate', Icon::SIZE_SMALL)->getMarkup().'
	</span>
</span></a>';

                return $translateContentPlainAction;

            default:
                return '';
        }
    }
}
