<?php
namespace App\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class SitemapsPingGoogleCommand extends Command
{
    public function __construct(
        private LoggerInterface       $logger,
        private ParameterBagInterface $parameterBag
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('nokaut:sitemaps:ping')
            ->setDescription('Pings sitemaps to Google');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pingGoogle();
        return 1;
    }

    protected function pingGoogle()
    {
        $siteMapUrl = urlencode($this->parameterBag->get('domain') . 'sitemap-index.xml');
        $url = "http://www.google.com/webmasters/tools/ping?sitemap=" . $siteMapUrl;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            throw new \RuntimeException('SiteMap google ping failed (' . $url . '), http code: ' . $httpCode);
        } else {
            $this->logger->info('SiteMap google ping (' . $url . '): success');
        }
    }
}