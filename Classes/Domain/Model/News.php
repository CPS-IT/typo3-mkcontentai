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

namespace DMK\MkContentAi\Domain\Model;

class News extends \GeorgRinger\News\Domain\Model\News
{
    protected ?News $txMkcontentaiOriginalNews = null;
    protected ?News $txMkcontentaiTranslatedNews = null;

    public function getTxMkcontentaiOriginalNews(): ?News
    {
        return $this->txMkcontentaiOriginalNews;
    }

    public function setTxMkcontentaiOriginalNews(?News $txMkcontentaiOriginalNews): News
    {
        $this->txMkcontentaiOriginalNews = $txMkcontentaiOriginalNews;
        return $this;
    }

    public function getTxMkcontentaiTranslatedNews(): ?News
    {
        return $this->txMkcontentaiTranslatedNews;
    }

    public function setTxMkcontentaiTranslatedNews(?News $txMkcontentaiTranslatedNews): News
    {
        $this->txMkcontentaiTranslatedNews = $txMkcontentaiTranslatedNews;
        return $this;
    }
}
