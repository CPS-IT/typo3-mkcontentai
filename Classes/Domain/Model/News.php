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

class News extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var int
     */
    protected $uid;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $teaser;

    /**
     * @var string
     */
    protected $bodytext;

    /**
     * @var string
     */
    protected $pathSegment;

    /**
     * @var int
     */
    protected $contentElements;

    /**
     * @var int
     */
    protected $originalUid;

    /**
     * @var int
     */
    protected $sorting;

    /**
     * @var int
     */
    protected $crdate;

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTeaser(): string
    {
        return $this->teaser;
    }

    public function setTeaser(string $teaser): void
    {
        $this->teaser = $teaser;
    }

    public function getBodytext(): string
    {
        return $this->bodytext;
    }

    public function setBodytext(string $bodytext): void
    {
        $this->bodytext = $bodytext;
    }

    public function getPathSegment(): string
    {
        return $this->pathSegment;
    }

    public function setPathSegment(string $pathSegment): void
    {
        $this->pathSegment = $pathSegment;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }


    public function getContentElements(): int
    {
        return $this->contentElements;
    }

    public function setContentElements(int $contentElements): void
    {
        $this->contentElements = $contentElements;
    }

    public function getOriginalUid(): int
    {
        return $this->originalUid;
    }

    public function setOriginalUid(int $originalUid): void
    {
        $this->originalUid = $originalUid;
    }

    public function getCrDate(): int
    {
        return $this->crdate;
    }

    public function setCrDate(int $crdate): void
    {
        $this->crdate = $crdate;
    }
}
