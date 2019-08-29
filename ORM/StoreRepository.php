<?php
/**
 * StoreRepository.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\ORM;

use Cake\Utility\Hash;
use Fidesio\IsidoreBundle\Services\Client;
use Exception;

class StoreRepository implements StoreRepositoryInterface, StoreInterface
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

    /**
     * @var array
     */
    protected $_result = [];

    public function __construct(Client $client, $store = null)
    {
        $this->_client = $client;
        $this->_storeName = $store;

        if (!$this->_client->getAuth()->getUserData()) {
            throw new Exception('NOT AUTHENTICATED IN ISIDORE');
        }
    }

    public function __toString()
    {
        return $this->getStoreName();
    }

    /**
     * Get store name
     *
     * @return string
     */
    public function getStoreName()
    {
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
     * Finds all objects in the repository.
     *
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array|null
     */
    public function findAll(array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->find([], $orderBy, $limit, $offset);
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     *
     * @return array|null
     * @throws Exception
     */
    public function find(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $query = [];
        $filters = [];
        $sorts = [];

        if (!empty($criteria)) {
            foreach ($criteria as $property => $value) {
                $filters[] = $this->_prepareFilter($property, $value);
            }

            if (!empty($filters)) {
                $query['filter'] = json_encode($filters);
            }
        }

        if (!empty($orderBy)) {
            foreach ($orderBy as $property => $direction) {
                if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                    if ($this->_client->isDebugMode()) {
                        throw new Exception('"direction" du sorter doit être soit "ASC" ou "DESC".');
                    }
                    continue;
                }
                if ($this->getManager()->checkProperty($property)) {
                    $sorts[] = ['property' => $property, 'direction' => strtoupper($direction)];
                }
            }

            if (!empty($sorts)) {
                $query['sort'] = json_encode($sorts);
            }
        }

        if (is_int($limit)) {
            $query['limit'] = $limit;
        }

        if (is_int($offset)) {
            $query['start'] = $offset;
        }

        $this->_result = $this->operate('read', $query);

        return $this;
    }

    /**
     * @param string       $property
     * @param string|array $value
     *
     * @return array
     */
    protected function _prepareFilter($property, $value)
    {
        $filter = [];
        $comparisons = ['eq', 'seq', 'neq', 'gt', 'ge', 'gte', 'lt', 'le', 'lte', 'ct', 'sw', 'nct', 'ew', 'in', 'nin'];

        if (is_array($value) && !Hash::numeric(array_keys($value))) {
            foreach ($value as $comparison => $_value) {
                if (in_array($comparison, ['in', 'nin'])) { // IN or NOT IN
                    $filter = [
                        'property'   => $property,
                        'value'      => $this->_checkPropertyArrayValue($property,
                            is_array($_value) ? $_value : [$_value]),
                        'comparison' => $comparison,
                    ];
                } elseif (in_array($comparison, $comparisons)) { // OTHER CLAUSE
                    if ($this->getManager()->checkProperty($property, $_value, true)) {
                        $filter = [
                            'property'   => $property,
                            'value'      => $_value,
                            'comparison' => $comparison,
                        ];
                    }
                } else {
                    if ($this->_client->isDebugMode()) {
                        throw new Exception("La clause \"$comparison\" n'existe pas");
                    }
                }
            }
        } elseif (is_array($value)) { // IN
            $filter = [
                'property' => $property,
                'value'    => $this->_checkPropertyArrayValue($property, $value),
            ];
        } else { // EQUAL
            if ($this->getManager()->checkProperty($property, $value, true)) {
                $filter = [
                    'property' => $property,
                    'value'    => $value,
                ];
            }
        }

        return $filter;
    }

    /**
     * @param string $property
     * @param array  $value
     *
     * @return array
     */
    protected function _checkPropertyArrayValue($property, array $value = [])
    {
        $values = array_map(
            function ($v) use ($property) {
                if ($this->getManager()->checkProperty($property, $v, true)) {
                    return $v;
                }
            }, $value
        );

        return $values;
    }

    /**
     * @return StoreManager
     */
    public function getManager()
    {
        $manager = $this->_client->getContainer()->get('fidesio_isidore.store_manager');
        $manager->setStoreName($this->getStoreName());

        return $manager;
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

        return $this->getManager()->operate($action, $criteria, $postData);
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array      $criteria The criteria.
     * @param array|null $orderBy
     *
     * @return array|object
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->find($criteria, $orderBy, 1);
    }

    public function getResult()
    {
        return $this->_result;
    }

    public function getData()
    {
        return $this->getTotal() ? ($this->getTotal() == 1 ? $this->_result['data'][0] : $this->_result['data']) : null;
    }

    public function getTotal()
    {
        return isset($this->_result['total']) ? (int)$this->_result['total'] : 0;
    }

    /**
     * Data to choices array
     *
     * @param string|null $type list type
     * @param string      $key field used as array key
     * @param string      $displayField displayed field used for label
     *
     * @return array
     */
    public function toChoices($type = null, $key = '_id', $displayField = '_nom')
    {
        $choices = [];

        if ($this->getTotal()) {
            foreach ($this->_result['data'] as $row) {
                if (isset($row[$key], $row[$displayField])) {
                    switch ($type) {
                        case 'simple':
                            $choices[] = $row[$displayField];
                            break;
                        case 'select':
                            $choices[$row[$key]] = $row[$displayField];
                            break;
                        default:
                            $choices[] = [$row[$key] => $row[$displayField]];
                    }

                }
            }
        }

        return $choices;
    }
}
