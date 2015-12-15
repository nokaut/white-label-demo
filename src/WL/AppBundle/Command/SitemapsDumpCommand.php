<?php
namespace WL\AppBundle\Command;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nokaut\ApiKit\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SitemapPHP\Sitemap;
use WL\AppBundle\Lib\SiteMap\SiteMapUrls;


class SitemapsDumpCommand extends ContainerAwareCommand
{
    const API_MAX_FILTER_VALUES = 100;

    /**
     * @var Logger
     */
    protected $logger;

    protected function configure()
    {
        $this->setName('nokaut:sitemaps:dump')
            ->setDescription('Dumps sitemaps to given location')
            ->addArgument(
                'target',
                InputArgument::OPTIONAL,
                'Location where to dump sitemaps. Generated urls will not be related to this folder.',
                './'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dumpSiteMap($input, $output);
    }

    protected function dumpSiteMap(InputInterface $input, OutputInterface $output)
    {
        /** @var SiteMapUrls $siteMapUrls */
        $siteMapUrls = $this->getContainer()->get('sitemap.urls');
        $siteMapUrls->createSiteMap($input->getArgument('target'));

    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        if (!$this->logger) {
            $this->logger = $this->getContainer()->get('monolog.logger.cli');
        }
        return $this->logger;
    }
}

