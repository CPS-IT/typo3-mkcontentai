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
    /**
     * @var int
     */
    protected int $txMkcontentaiOriginalNewsUid = 0;

    /**
     * @var int
     */
    protected int $txMkcontentaiTranslatedNewsUid = 0;

    /**
     * @return int
     */
    public function getTxMkcontentaiOriginalNewsUid(): int
    {
        return $this->txMkcontentaiOriginalNewsUid;
    }

    /**
     * @return int
     */
    public function getTxMkcontentaiTranslatedNewsUid(): int
    {
        return $this->txMkcontentaiTranslatedNewsUid;
    }
}

