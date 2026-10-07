<?php
// tests/Entity/CityTest.php

namespace App\Tests\Entity;

use App\Entity\City;
use PHPUnit\Framework\TestCase;

class CityTest extends TestCase
{
	public function testCreatedTimeTrackable()
	{
		$city = new City();
		$dateTime = new \DateTime();
		$createdBy = 'test_user';

		$city->setCreatedAt($dateTime);
		$city->setCreatedBy($createdBy);

		$this->assertEquals($dateTime, $city->getCreatedAt());
		$this->assertEquals($createdBy, $city->getCreatedBy());
	}
}
