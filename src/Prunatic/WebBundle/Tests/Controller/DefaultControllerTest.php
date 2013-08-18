<?php

namespace Prunatic\WebBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        //$crawler = $client->request('GET', '/');

        // ensure getLastShouts is called

        // ensure getBestShouts is called

        // ensure topCities is called

        $this->markTestIncomplete();
    }
}
