<?php

namespace WL\AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MonitoringControllerTest extends WebTestCase
{
    public function testLightcheck()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lightCheck');
    }

    public function testFullcheck()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/fullCheck');
    }

}
