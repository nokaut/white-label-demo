<?php

namespace Tests\controllers;

use App\Controller\ProductController;
use App\Controller\SearchController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\FixtureDataTool;

class SearchControllerTest extends WebTestCase
{
    /**
     * @group integration
     */
    public function testSearchProductRoute(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/produkty-produkt:słuchawki.html');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('html body h1 span span.badge span', 'słuchawki');
        self::assertSelectorExists('meta[name="description"]');
    }

    public function testSearchIndexAction(): void
    {
        $fixtureDataTool = new FixtureDataTool();

        $render = $fixtureDataTool->getData('search-sluchawki.txt');

        $mock = $this->createMock(SearchController::class);
        $mock
            ->method('indexAction')
            ->with('produkt:słuchawki.html')
            ->willReturn($render);

        $response = $mock->indexAction('produkt:słuchawki.html');

        $this->assertStringContainsString('słuchawki', $response);
    }
}