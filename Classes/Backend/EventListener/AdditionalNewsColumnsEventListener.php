<?php

declare(strict_types=1);

namespace DMK\MkContentAi\Backend\EventListener;

use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Add required EXT:news columns to database schema if EXT:news is installed.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 */
final class AdditionalNewsColumnsEventListener
{
    public function __invoke(AlterTableDefinitionStatementsEvent $event): void
    {
        if (ExtensionManagementUtility::isLoaded('news')) {
            $event->addSqlData(
                $this->generateNewsSqlData(),
            );
        }
    }

    private function generateNewsSqlData(): string
    {
        return <<<SQL
CREATE TABLE tx_news_domain_model_news
(
    tx_mkcontentai_original_news int(11) unsigned NOT NULL DEFAULT '0',
    tx_mkcontentai_translated_news int(11) unsigned NOT NULL DEFAULT '0'
);
SQL;
    }
}
