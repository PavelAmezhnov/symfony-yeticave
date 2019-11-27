<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BetControllerTest extends WebTestCase
{
    /**
     * @dataProvider getData
     */
    public function testNewBetOnDetailView(string $lotId, string $betValue, bool $isSuccess)
    {
        
        $client = static::createClient();
        
        $crawler = $client->xmlHttpRequest('POST', '/bet/new', ['id' => $lotId, 'value' => $betValue]);
        
        if ($isSuccess) {
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        } else {
            $this->assertEquals(500, $client->getResponse()->getStatusCode());
        }
        
    }
    
    public function getData()
    {
        return [
            'nonexistent_lot' => ['-1', '1', false]
        ];
    }
}
