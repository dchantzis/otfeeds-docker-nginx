<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Closure;

abstract class AbstractRepository implements RepositoryInterface
{

    /**
     * Eloquent model to back the repository with.
     *
     * @var Model
     */
    protected $model;

    /**
     * Current repository errors.
     *
     * @var array
     */
    protected $errorsStore;

    /**
     * Attributes that can be used for keyword search.
     *
     * @var array
     */
    protected $searchable = array(
        'id' => 'strict'
    );

    /**
     * Attribute to order search results by.
     *
     * @var array
     */
    protected $searchOrderBy = 'id';

    /**
     * Default search order.
     *
     * @var array
     */
    protected $searchOrder = 'asc';

    /**
     * Constructor.
     *
     * Accepts Eloquent model to use as backer. Can and should be overridden in
     * implementing class to typehint correct model.
     *
     * @param  Model $model
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->errorsStore = array();
    }

    /**
     * Fetch all entities from the repository.
     *
     * @param  mixed        $related Optional set of relationships to load with entities
     * @return Collection   Set of entities
     */
    public function all($related = array())
    {
        $this->errorsStore = array();

        return $this->model->with($related)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function chunk($size, Closure $cb)
    {
        return $this->model->chunk($size, $cb);
    }

    /**
     * Get a paginated set of entities from the repository.
     *
     * @param int $perPage Number of entities to retrieve
     * @param array $related Optional set of relationships to load with entities
     * @return LengthAwarePaginator|Paginator Set of entities
     */
    public function paginate($perPage = 15, $related = array())
    {
        $this->errorsStore = array();

        return $this->model->with($related)->paginate($perPage);
    }

    public function create(array $attributes = array(), $related = array())
    {
        $this->errorsStore = array();
        $instance = $this->model->create($attributes);

        if (!$instance->getKey()) {
            if ($instance instanceof Validatable) {
                $this->errorsStore = $instance->validationErrors();
            }

            return false;
        } else {
            return $instance->load($related);
        }
    }

    /**
     * Get a new entity, but don't persist it.
     *
     * @param  array  $attributes Attributes to set on entity
     * @return void
     */
    public function getNew(array $attributes = array())
    {
        $class = get_class($this->model);

        return new $class($attributes);
    }

    /**
     * Retrieve an entity from the repository.
     *
     * @param  mixed         $id      Primary key of entity to retrieve
     * @param  mixed         $related Optional set of relationships to load with entity
     * @return mixed|boolean          Found entity, or false if not found
     */
    public function find($id, $related = array())
    {
        $this->errorsStore = array();

        return $this->model->with($related)->find($id);
    }

    /**
     * Retrieve an entity from the repository if it can be retrieved by key,
     * but if not, create a new one.
     *
     * @param  mixed $id      Primary key
     * @param  mixed $related Optional set of relationships to load with entity
     * @return mixed          Found or new entity
     */
    public function findOrNew($id = null, $related = array())
    {
        $this->errorsStore = array();

        return $this->model->with($related)->firstOrNew(array(
            $this->model->getKeyName() => $id
        ));
    }

    /**
     * Update an entity with by its ID, with the given set of attributes.
     *
     * @param  mixed         $id         Primary key of entity to update
     * @param  array         $attributes Attributes to update on entity
     * @param  mixed         $related    Optional set of relationships to load with entity
     * @return mixed|boolean             Entity if updated successfully, false if not
     */
    public function update($id, array $attributes, $related = array())
    {
        $this->errorsStore = array();

        if ($instance = $this->model->find($id)) {
            $saved = $instance->update($attributes) ? $instance : false;

            if (!$saved && ($instance instanceof Validatable)) {
                $this->errorsStore = $instance->validationErrors();
            }

            return $saved ? $saved->load($related) : false;
        } else {
            return false;
        }
    }

    /**
     * Remove an entity or set of entities from the repository.
     *
     * @param  mixed   $id Primary key or keys of entity(ies) to destroy
     * @return boolean     True if destroyed successfully, false if not
     */
    public function destroy($id)
    {
        $this->errorsStore = array();

        return $this->model->destroy($id);
    }

    /**
     * Search the entities using a given term and options.
     *
     * @param  string   $term       Search term
     * @param  array    $options    Search options
     * @param  int      $limit      Per page limit
     * @param  null     $orderby    Field to order the search by
     * @param  string   $order      Order of results [asc/desc]
     * @return array                Paginated entities
     */
    public function search($term = '', $options = array(), $limit = 100, $orderBy = null, $order = null)
    {
        $search = $this->model;
        $searchable = $this->searchable;

        if (!$orderBy) $orderBy = $this->searchOrderBy;
        if (!$order) $order = $this->searchOrder;

        // Main search function
        if ($term != '') {
            $search = $search->where(function($query) use ($term, $searchable) {
                foreach ($searchable as $item => $type) {
                    if ($type == 'strict') {
                        $query->orWhere($item, '=', $term);

                    } elseif ($type == 'combine') {
                        $item = str_replace('+', ', " ", ', $item);
                        $query->orWhere(DB::raw('CONCAT('.$item.')'), 'like', '%'.$term.'%');

                    } elseif ($type == 'relation') {
                        $item = explode('->', $item);
                        $query->orWhereHas($item[0], function($q) use ($item, $term) {
                            $q->where($item[1], 'like', '%'.$term.'%');
                        });

                    } else {
                        $query->orWhere($item, 'like', '%'.$term.'%');
                    }
                }
            });
        }

        // Search ordering
        $search = $search->orderBy($orderBy, $order);

        // Apply filters
        if ($options != array()) {
            $search = $search->where(function($query) use ($options) {
                foreach ($options as $key => $value) {
                    if (substr($key, 0, 1) === '!') {
                        $key = substr($key, 1);
                        if (is_array($value)) {
                            $query->whereNotIn($key, $value);
                        } elseif ($value) {
                            $query->whereNotIn($key, array($value));
                        }
                    } else {
                        if (is_array($value)) {
                            $query->whereIn($key, $value);
                        } elseif ($value) {
                            $query->where($key, $value);
                        }
                    }
                }
            });
        }

        return $search->paginate($limit);
    }

    /**
     * Fetch any errors for the last operation that was performed on
     * repository.
     *
     * @return mixed Errors
     */
    public function errors()
    {
        return $this->errorsStore;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

}
