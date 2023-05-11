<?php

namespace Tests\controllers;

use App\Controller\ProductController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\FixtureDataTool;

class ProductControllerTest extends WebTestCase
{
    /**
     * @group integration
     */
    public function testProductRoute(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/produkty-sluchawki');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('html body h1 span.name strong', 'Słuchawki');
        self::assertSelectorExists('meta[name="description"]');
    }

    public function testProductIndexAction(): void
    {
        $fixtureDataTool = new FixtureDataTool();

        $render = $fixtureDataTool->getData('products-render.txt');

        $mock = $this->createMock(ProductController::class);
        $mock
            ->method('indexAction')
            ->with('roboty-kuchenne/robot-planetarny-bosch-mum9bx5s65-optimum-zamow-z-dostawa-jutro-darmowy-transport')
            ->willReturn($render);

        $response = $mock->indexAction('roboty-kuchenne/robot-planetarny-bosch-mum9bx5s65-optimum-zamow-z-dostawa-jutro-darmowy-transport');

        $this->assertStringContainsString('Nowy robot kuchenny OptiMUM: elegancki design', $response);

    }
}