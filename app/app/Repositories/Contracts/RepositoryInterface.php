<?php


namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

/**
 * This interface describes a repository for retrieving models.
 *
 * Information on the reasoning behind and benefits of using the repository
 * pattern can be found at the links below:
 *
 * - [http://ryantablada.com/post/the-repository-pattern-in-action](http://ryantablada.com/post/the-repository-pattern-in-action)
 * - [http://www.slashnode.com/reusable-repository-design-in-laravel/](http://www.slashnode.com/reusable-repository-design-in-laravel/)
 *
 * And this provides an interesting discussion:
 *
 * - [https://laracasts.com/forum/718-repository-pattern-and-eloquent-models/0](https://laracasts.com/forum/718-repository-pattern-and-eloquent-models/0)
 */
interface RepositoryInterface
{

    /**
     * Fetch all entities from the repository.
     *
     * @param  mixed               $related Optional set of relationships to load with entities
     * @return Collection          Set of entities
     */
    public function all($related = array());

    /**
     * Process entities in batches.
     *
     * @param  int      $size Batch size
     * @param  \Closure $cb   Callback. Passed batch of entities
     * @return void
     */
    public function chunk($size, \Closure $cb);

    /**
     * Get a paginated set of entities from the repository.
     *
     * @param  int      $perPage Number of entities to retrieve
     * @param  mixed    $related Optional set of relationships to load with entities
     * @return Paginator Paginated set of entities
     */
    public function paginate($perPage = 15, $related = array());

    /**
     * Create new entity, persisting it to the database at the same time.
     *
     * @param  array         $attributes Attributes to set on entity
     * @param  mixed         $related    Optional set of relationships to load on created entity
     * @return mixed|boolean Created entity, or false if could not be created
     */
    public function create(array $attributes = array(), $related = array());

    /**
     * Get a new entity, but don't persist it.
     *
     * @param  array  $attributes Attributes to set on entity
     * @return void
     */
    public function getNew(array $attributes = array());

    /**
     * Retrieve an entity from the repository.
     *
     * @param  mixed         $id      Primary key of entity to retrieve
     * @return mixed|boolean          Found entity, or false if not found
     */
    public function find($id);

    /**
     * Retrieve an entity from the repository if it can be retrieved by key,
     * but if not, create a new one.
     *
     * @param  mixed $id      Primary key
     * @param  mixed $related Optional set of relationships to load with entity
     * @return mixed          Found or new entity
     */
    public function findOrNew($id = null, $related = array());

    /**
     * Update an entity with by its ID, with the given set of attributes.
     *
     * @param  mixed         $id         Primary key of entity to update
     * @param  array         $attributes Attributes to update on entity
     * @param  mixed         $related    Optional set of relationships to load with entity
     * @return mixed|boolean             Entity if updated successfully, false if not
     */
    public function update($id, array $attributes, $related = array());

    /**
     * Remove an entity or set of entities from the repository.
     *
     * @param  mixed   $id Primary key or keys of entity(ies) to destroy
     * @return boolean     True if destroyed successfully, false if not
     */
    public function destroy($id);

    /**
     * Fetch any errors for the last operation that was performed on
     * repository.
     *
     * @return mixed Errors
     */
    public function errors();

}
