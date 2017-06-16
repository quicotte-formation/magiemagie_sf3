<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PartieControllerControllerTest extends WebTestCase
{
    public function testListerparties()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lister_parties');
    }

}
