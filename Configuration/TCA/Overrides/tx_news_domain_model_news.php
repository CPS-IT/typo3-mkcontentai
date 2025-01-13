<?php
if(TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('news')) {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'tx_news_domain_model_news',
        [
            'tx_mkcontentai_original_news_uid' => [
                'config' => [
                    'type' => 'passthrough',
                ]
            ]
        ]
    );
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'tx_news_domain_model_news',
        'tx_mkcontentai_original_news_uid',
        '',
        ''
    );
}
