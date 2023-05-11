<?php

namespace Tests\controllers;

use App\Controller\DefaultController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\FixtureDataTool;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @group integration
     */
    public function testDefaultRoute(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('html body footer div.col-md-7 h5', 'Dla sklepów');
        self::assertSelectorExists('meta[name="description"]');
    }

    public function testDefaultIndexAction(): void
    {
        $fixtureDataTool = new FixtureDataTool();

        $render = $fixtureDataTool->getData('default_controller-render.txt');

        $mock = $this->createMock(DefaultController::class);
        $mock
            ->method('indexAction')
            ->willReturn($render);

        $response = $mock->indexAction();

        $this->assertStringContainsString('Porownywarka.co to serwis pozwalający na porównanie cen w najlepszych sklepach internetowych', $response);
    }
}