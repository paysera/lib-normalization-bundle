<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class FunctionalDateTimeNormalizerTest extends FunctionalTestCase
{
    public function testDateTimeNormalizerWithTimestampFormat()
    {
        $container = $this->setUpContainer('date_time_timestamp.yml');
        $coreDenormalizer = $container->get('core_denormalizer');
        $coreNormalizer = $container->get('core_normalizer');

        $timestamp = 1514808794;

        $dateTime = new DateTime('now', new DateTimeZone('Europe/Vilnius'));
        $dateTime->setTimestamp($timestamp);
        $normalized = $coreNormalizer->normalize($dateTime);

        $this->assertSame((string)$timestamp, $normalized);

        /** @var DateTimeImmutable $denormalized */
        $denormalized = $coreDenormalizer->denormalize($normalized, DateTimeImmutable::class);
        $this->assertInstanceOf(DateTimeImmutable::class, $denormalized);
        $this->assertSame($timestamp, $denormalized->getTimestamp());

        /** @var DateTimeInterface $denormalized */
        $denormalized = $coreDenormalizer->denormalize($normalized, DateTimeInterface::class);
        $this->assertInstanceOf(DateTimeImmutable::class, $denormalized);
        $this->assertSame($timestamp, $denormalized->getTimestamp());

        /** @var DateTime $denormalized */
        $denormalized = $coreDenormalizer->denormalize($normalized, DateTime::class);
        $this->assertInstanceOf(DateTime::class, $denormalized);
        $this->assertSame($timestamp, $denormalized->getTimestamp());
        $this->tearDownTest();
    }

    public function testDateTimeNormalizer()
    {
        $container = $this->setUpContainer('date_time.yml');
        $coreDenormalizer = $container->get('core_denormalizer');
        $coreNormalizer = $container->get('core_normalizer');

        $timestamp = 1514808794;

        $dateTime = new DateTime('now', new DateTimeZone('Europe/Vilnius'));
        $dateTime->setTimestamp($timestamp);
        $normalized = $coreNormalizer->normalize($dateTime);

        $this->assertSame('2018-01-01T12:13:14+00:00', $normalized);

        /** @var DateTimeImmutable $denormalized */
        $denormalized = $coreDenormalizer->denormalize($normalized, DateTimeImmutable::class);
        $this->assertInstanceOf(DateTimeImmutable::class, $denormalized);
        $this->assertSame($timestamp, $denormalized->getTimestamp());

        /** @var DateTimeInterface $denormalized */
        $denormalized = $coreDenormalizer->denormalize($normalized, DateTimeInterface::class);
        $this->assertInstanceOf(DateTimeImmutable::class, $denormalized);
        $this->assertSame($timestamp, $denormalized->getTimestamp());

        /** @var DateTime $denormalized */
        $denormalized = $coreDenormalizer->denormalize($normalized, DateTime::class);
        $this->assertInstanceOf(DateTime::class, $denormalized);
        $this->assertSame($timestamp, $denormalized->getTimestamp());
        $this->tearDownTest();
    }
}
