<?php
namespace Craft;


class Varnishpurge_PurgeTask extends BaseTask
{
    private $_uris;
    private $_locale;

    public function getDescription()
    {
        return Craft::t('Purging Varnish cache');
    }

    public function getTotalSteps()
    {
        $uris = $this->getSettings()->uris;
        $this->_locale = $this->getSettings()->locale;
        
        $this->_uris = array();
        $this->_uris = array_chunk($uris, 20);

        return count($this->_uris);
    }

    public function runStep($step)
    {
        VarnishpurgePlugin::log('Varnish purge task run step: ' . $step, LogLevel::Info, craft()->varnishpurge->getSetting('varnishLogAll'));
        
        $varnishUrlSetting = craft()->varnishpurge->getSetting('varnishUrl');
        if (is_array($varnishUrlSetting)) {
            $varnishUrl = $varnishUrlSetting[$this->_locale];
        } else {
            $varnishUrl = $varnishUrlSetting;
        }
        
        if (!$varnishUrl) {
            VarnishpurgePlugin::log('Varnish URL could not be found', LogLevel::Error);
            return false;
        }

        $batch = \Guzzle\Batch\BatchBuilder::factory()
          ->transferRequests(20)
          ->bufferExceptions()
          ->build();

        $client = new \Guzzle\Http\Client();
        $client->setDefaultOption('headers/Accept', '*/*');

        foreach ($this->_uris[$step] as $uri) {
            if ($uri == '__home__') {
                $url = $varnishUrl;
            } else {
                $url = $varnishUrl . $uri;
            }

            VarnishpurgePlugin::log('Adding url to purge: ' . $url, LogLevel::Info, craft()->varnishpurge->getSetting('varnishLogAll'));

            $request = $client->createRequest('PURGE', $url);
            $batch->add($request);
        }

        $requests = $batch->flush();

        foreach ($batch->getExceptions() as $e) {
            VarnishpurgePlugin::log('An exception occurred: ' . $e->getMessage(), LogLevel::Error);
        }

        $batch->clearExceptions();

        return true;
    }

    protected function defineSettings()
    {
        return array(
          'uris' => AttributeType::Mixed,
          'locale' => AttributeType::String
        );
    }

}
