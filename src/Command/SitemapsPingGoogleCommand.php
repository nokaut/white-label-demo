<?php
namespace App\Command;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class SitemapsPingGoogleCommand extends Command
{
    private string $domain;

    public function __construct(
        private LoggerInterface       $logger,
        private ParameterBagInterface $parameterBag
    )
    {
        parent::__construct();
        $this->domain = $this->parameterBag->get('site_scheme') . '://' . $this->parameterBag->get('site_host');
    }

    protected function configure(): void
    {
        $this->setName('nokaut:sitemaps:ping')
            ->setDescription('Pings sitemaps to Google');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->pingGoogle();
        return 1;
    }

    protected function pingGoogle(): void
    {
        $siteMapUrl = urlencode($this->domain . '/sitemap.xml');
        $url = "https://www.google.com/webmasters/sitemaps/ping?sitemap=" . $siteMapUrl;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            throw new RuntimeException('SiteMap google ping failed (' . $url . '), http code: ' . $httpCode);
        }

        $this->logger->info('SiteMap google ping (' . $url . '): success');
    }
}