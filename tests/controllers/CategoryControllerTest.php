<?php

namespace Tests\controllers;

use App\Controller\CategoryController;
use App\Lib\Breadcrumbs\BreadcrumbsBuilder;
use App\Lib\CategoriesAllowed;
use App\Lib\Repository\ProductsAsyncRepository;
use Mockery;
use Nokaut\ApiKit\ClientApi\Rest\RestClientApi;
use Nokaut\ApiKit\Config;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\FixtureDataTool;

class CategoryControllerTest extends WebTestCase
{
    /**
     * @group integration
     */
    public function testCategoryRoute(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/kategoria-muzyka');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('html body h1 span.name strong', 'Muzyka');
        self::assertSelectorExists('meta[name="description"]');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testCategoryIndexAction(): void
    {
        $categoryController = Mockery::mock(CategoryController::class);
        $fixtureDataTool = new FixtureDataTool();

        $categoryController->shouldReceive('fetchCategory')
            ->with('muzyka')
            ->andReturn($fixtureDataTool->getData('category-muzyka.txt'));

        $categoryController->shouldReceive('indexAction')
            ->with('muzyka')
            ->andReturn($fixtureDataTool->getData('category-render.txt'));


        $response = $categoryController->indexAction('muzyka');
        $this->assertStringContainsString('Znajdziesz tu wszystko to, czego potrzebujesz do muzykowania. Setki ofert różnorodnych produktów w najlepszy cech', $response);

    }
}