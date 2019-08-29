<?php
/**
 * StoreRepository.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\ORM;

use DateTime;
use DateTimeZone;
use Fidesio\IsidoreBundle\Services\Client;
use Exception;

class StoreManager implements StoreInterface
{
    use StoreTrait;

    /**
     * @var Client
     */
    protected $_client;

    /**
     * @var null|string
     */
    protected $_storeName;

    public function __construct(Client $client, $store = null)
    {
        $this->_client = $client;
        $this->_storeName = $store;
    }

    public function __toString()
    {
        return $this->getStoreName();
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * @param string $action
     * @param array  $criteria
     * @param array  $postData
     *
     * @return array|void
     * @throws Exception
     */
    public function operate($action = '', array $criteria = [], array $postData = [])
    {
        if (!$this->_client->getAuth()->getUserData()) {
            throw new Exception('NOT AUTHENTICATED IN ISIDORE');
        }

        $url = 'proxies/ajax/' . $this->getStoreMetaName();

        return $this->_client->operate($url . '--' . $action, $criteria, $postData);
    }

    /**
     * @return StoreRepository
     */
    public function getRepository()
    {
        $repository = $this->_client->getContainer()->get('fidesio_isidore.store_repository');
        $repository->setStoreName($this->getStoreName());

        return $repository;
    }

    /**
     * Create data
     *
     * @param array $data
     *
     * @return array|null
     */
    public function create(array $data)
    {
        return $this->save($data);
    }

    /**
     * Update data
     *
     * @param array $data
     *
     * @return array|null
     */
    public function update(array $data)
    {
        return $this->save($this->formatData($data), 'update');
    }

    /**
     * Delete row
     *
     * @param array $data
     *
     * @return bool
     */
    public function delete(array $data)
    {
        $res = $this->operate('destroy', [], $this->formatData($data));

        if (isset($res['data'])) {
            return $res['data'];
        }

        return false;
    }

    /**
     * Récupère un store
     */
    public function getStore()
    {
        $res = $this->_client->operate('controller/Fidesio.webservice.ServiceStore--get', [
            'metaName' => $this->getStoreMetaName(),
        ]);

        if ($res === null) {
            $errorMessage = "STORE <<{$this->getStoreName()}>> NOT EXISTS";
            $this->_client->getLogger()->critical($errorMessage);
            throw new Exception($errorMessage);
        }

        return $res;
    }

    /**
     * Récupére les stores
     *
     * @return array
     */
    public function getStores()
    {
        $res = $this->_client->operate('controller/Fidesio.webservice.ServiceStore--getStores');

        return $res;
    }

    /**
     * Get store structure
     *
     * @return null
     * @throws Exception
     */
    public function getStoreStructure()
    {
        $store = $this->getStore();

        return isset($store['structure']) ? $store['structure'] : null;
    }

    /**
     * Get store name
     *
     * @return string
     */
    public function getStoreName()
    {
        if (empty($this->_storeName)) {
            throw new Exception('NO STORE SETTED');
        }

        return $this->_storeName;
    }

    /**
     * Set store name
     *
     * @param string $store
     *
     * @return $this
     */
    public function setStoreName($store)
    {
        $this->_storeName = $store;

        return $this;
    }

    /**
     * @param string $property
     * @param null   $value
     * @param bool   $checkValue
     *
     * @return bool
     * @throws Exception
     */
    public function checkProperty($property, $value = null, $checkValue = false)
    {
        $structure = $this->getStoreStructure();

        if (!$structure) {
            throw new Exception('Structure des données non initialisée.');
        }
        if (preg_match('/^@pk/', $property)) {
            return true;
        }
        if (!array_key_exists($property, $structure)) {
            throw new Exception('Le champ "' . $property . '" n\'existe pas.');
        }
        if (!$checkValue) {
            return true;
        }
        if ($structure[$property]['type'] == 'string' && !is_string($value) && $value !== null) {
            throw new Exception('La valeur du champ "' . $property . '" doit être une string.');
        }

        return true;
    }

    /**
     * Save a store data
     *
     * @param array  $data un tableau de type 'champ' => 'valeur'
     *
     * @param string $mode
     *
     * @return array|null
     * @throws \Exception
     */
    protected function save(array $data, $mode = 'create')
    {
        if (empty($data)) {
            throw new Exception('ERROR SAVE: empty data');
        }
        if (!in_array($mode, ['create', 'update'])) {
            throw new Exception('ERROR SAVE DATA TO STORE: save mode not allowed, allow create or update only.');
        }

        $structure = $this->getStoreStructure();

        foreach ($data as $property => $value) {
            if ($mode === 'create' && !array_key_exists($property, $structure) && !preg_match('/^@pk/', $property)) {
                throw new Exception("Le champ <<$property>> n'existe pas.");
            }

            if ($mode === 'update'
                && $property !== 'id'
                && !array_key_exists($property, $structure)
                && !preg_match('/^@pk/', $property)
            ) {
                throw new Exception("Le champ <<$property>> n'existe pas.");
            }

            if ($mode === 'create' || ($mode === 'update' && $property !== 'id')) {
                $this->checkProperty($property, $value, true);
            }
        }

        foreach ($structure as $property => $value) {
            if (!array_key_exists($property, $data) && $value['required'] === true) {
                throw new Exception("Le champ $property est requis.");
            }
        }

        $res = $this->operate($mode, [], $data);

        if (isset($res['data'])) {
            return $res['data'];
        }
        if (isset($res['exception']['message'])) {
            throw new Exception(
                $res['exception']['message'],
                (isset($res['exception']['code']) ? (int)$res['exception']['code'] : 0)
            );
        }

        return null;
    }

    /**
     * Format Data
     *
     * @param array $data
     * @param array $errors
     *
     * @return array
     */
    public function formatData(array $data, array &$errors = [])
    {
        $tmpData = [];

        if (isset($data[0])) {
            foreach ($data as $k => $_data) {
                $tmpData[$k] = $this->_formatData($_data, $errors);
            }
        } else {
            $tmpData = $this->_formatData($data, $errors);
        }

        return $tmpData;
    }

    protected function _formatData($data, &$errors = [])
    {
        $tmpData = [];
        $structure = $this->getStoreStructure();

        foreach ($data as $key => $value) {
            $tmpD = $value;
            if (array_key_exists($key, $structure)) {
                if (!empty($value) || $structure[$key]['required']) {
                    switch ($structure[$key]['type']) {
                        case 'date':
                            if (DateTime::createFromFormat('Y-m-d\TH:i:s', $value, new DateTimeZone('GMT')) == false) {
                                $errors[$key] = 'Time format is not valid (must be Y-m-d\TH:i:s ex "1989-12-16T12:45:00").';
                            }
                            $date = new DateTime();
                            $date->setTimestamp(strtotime($value));
                            $tmpD = $date->format("Y-m-d\TH:i:s");
                            break;
                        case 'boolean':
                            $tmpD = ($tmpD ? true : false);
                            break;
                        case 'float':
                            $tmpD = (float)$tmpD;
                            break;
                        case 'integer':
                            $tmpD = (int)$tmpD;
                            break;
                    }
                    $tmpData[$key] = $tmpD;
                }
            } else {
                $tmpData[$key] = $tmpD;
            }
        }

        return $tmpData;
    }
}
