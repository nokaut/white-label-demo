<?php
namespace WL\AppBundle\Command;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SitemapsPingGoogleCommand extends ContainerAwareCommand
{
    /**
     * @var Logger
     */
    protected $logger;

    protected function configure()
    {
        $this->setName('nokaut:sitemaps:ping')
            ->setDescription('Pings sitemaps to Google');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pingGoogle();
    }

    protected function pingGoogle()
    {
        $siteMapUrl = urlencode($this->getContainer()->getParameter('domain') . 'sitemap-index.xml');
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
            throw new \Exception('SiteMap google ping failed (' . $url . '), http code: ' . $httpCode);
        } else {
            $this->getLogger()->info('SiteMap google ping (' . $url . '): success');
        }
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