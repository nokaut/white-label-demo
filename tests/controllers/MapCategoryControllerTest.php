<?php

namespace Tests\controllers;

use App\Controller\MapCategoryController;
use App\Lib\Breadcrumbs\BreadcrumbsFactory;
use App\Lib\Type\Breadcrumb;
use Mockery;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\FixtureDataTool;

class MapCategoryControllerTest extends WebTestCase
{
    /**
     * @group integration
     */
    public function testMapCategoryRoute(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/mapa-kategorii');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('html body h1', 'Mapa kategorii');
        self::assertSelectorExists('meta[name="description"]');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testIndexAction(): void
    {
        $fixtureDataTool = new FixtureDataTool();
        $factory = Mockery::mock(MapCategoryController::class);

        $factory->shouldReceive('indexAction')
            ->with('Mapa kategorii')
            ->andReturns([new Breadcrumb('Mapa kategorii')]);

        $response = $factory->indexAction('Mapa kategorii');
        $this->assertEquals($fixtureDataTool->getData('map-category.txt'), $response);
    }
}