<?php

namespace App\Command;

use Psr\Log\LoggerInterface;
use App\Lib\SiteMap\SiteMapUrls;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SitemapsDumpCommand extends Command
{
    public function __construct(
        private LoggerInterface $logger,
        private SiteMapUrls     $siteMapUrls
    )
    {
        parent::__construct();
    }

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
        return 1;
    }

    protected function dumpSiteMap(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info('SiteMap dump start');
        $this->siteMapUrls->createSiteMap($input->getArgument('target'));
        $this->logger->info('SiteMap dump stop');
    }
}

