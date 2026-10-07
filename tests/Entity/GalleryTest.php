<?php

namespace App\Tests\Entity;

use App\Entity\Gallery;
use PHPUnit\Framework\TestCase;

class GalleryTest extends TestCase
{
    public function testGalleryEntity(): void
    {
        $gallery = new Gallery();

        $gallery->setEntityType('Article');
        $gallery->setEntityId(1);
        $gallery->setCaption('Sample Caption');
        $gallery->setType('image');
        $gallery->setUrl('https://example.com/image.jpg');
        $gallery->setThumbnailUrl('https://example.com/thumb.jpg');

        $this->assertEquals('Article', $gallery->getEntityType());
        $this->assertEquals(1, $gallery->getEntityId());
        $this->assertEquals('Sample Caption', $gallery->getCaption());
        $this->assertEquals('image', $gallery->getType());
        $this->assertEquals('https://example.com/image.jpg', $gallery->getUrl());
        $this->assertEquals('https://example.com/thumb.jpg', $gallery->getThumbnailUrl());

        // Test nullable properties
        $gallery->setCaption(null);
        $gallery->setThumbnailUrl(null);

        $this->assertNull($gallery->getCaption());
        $this->assertNull($gallery->getThumbnailUrl());
    }
}
