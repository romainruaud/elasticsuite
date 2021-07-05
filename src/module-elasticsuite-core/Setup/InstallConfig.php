<?php


namespace Smile\ElasticsuiteCore\Setup;


use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Search\Setup\InstallConfigInterface;
use Magento\Setup\Model\SearchConfigOptionsList;

class InstallConfig implements InstallConfigInterface
{
    private const CATALOG_SEARCH = 'catalog/search/';

    private const ES_CLIENT      = 'smile_elasticsuite_core_base_settings/es_client/';

    private const ES_INDICES     = 'smile_elasticsuite_core_base_settings/indices_settings/';

    /**
     * @var array
     */
    private $searchConfigMapping = [
        SearchConfigOptionsList::INPUT_KEY_SEARCH_ENGINE => 'engine'
    ];

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @param WriterInterface $configWriter
     * @param array $searchConfigMapping
     */
    public function __construct(
        WriterInterface $configWriter,
        array $searchConfigMapping = [],
        array $legacyClientMapping = [],
        array $legacyIndicesMapping = []
    ) {
        $this->configWriter         = $configWriter;
        $this->searchConfigMapping  = array_merge($this->searchConfigMapping, $searchConfigMapping);
        $this->legacyClientMapping  = array_merge($this->legacyClientMapping, $legacyClientMapping);
        $this->legacyIndicesMapping = array_merge($this->legacyIndicesMapping, $legacyIndicesMapping);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(array $inputOptions)
    {
        foreach ($inputOptions as $inputKey => $inputValue) {
            if (null !== $inputValue && (isset($this->searchConfigMapping[$inputKey]))) {
                $configKey = $this->searchConfigMapping[$inputKey];
                $this->configWriter->save(self::CATALOG_SEARCH . $configKey, $inputValue);
            }
            if (null !== $inputValue && (isset($this->legacyClientMapping[$inputKey]))) {
                $configKey = $this->legacyClientMapping[$inputKey];
                $this->configWriter->save(self::ES_CLIENT . $configKey, $inputValue);
            }
            if (null !== $inputValue && (isset($this->legacyClientMapping[$inputKey]))) {
                $configKey = $this->legacyClientMapping[$inputKey];
                $this->configWriter->save(self::ES_INDICES . $configKey, $inputValue);
            }
        }

        if (isset($inputOptions['elasticsearch-host']) && isset($inputOptions['elasticsearch-port'])) {
            $esHosts = sprintf('%s:%s', $inputOptions['elasticsearch-host'], $inputOptions['elasticsearch-port']);
            $this->configWriter->save(self::ES_CLIENT . 'es-hosts', $inputValue);
        }
    }
}
