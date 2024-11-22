<?php

namespace Src\Main\Database\Eloquent;

use ArrayAccess;
use Illuminate\Support\Collection;
use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Eloquent\Relations\Pivot;
use Src\Main\Database\Eloquent\Traits\Model\GuardsAttributes;
use Src\Main\Database\Eloquent\Traits\Model\HandleBoot;
use Src\Main\Database\Eloquent\Traits\Model\HandleCrud;
use Src\Main\Database\Eloquent\Traits\Model\HasAttributes;
use Src\Main\Database\Eloquent\Traits\Model\HasEvents;
use Src\Main\Database\Eloquent\Traits\Model\HasGlobalScopes;
use Src\Main\Database\Eloquent\Traits\Model\HasRelationships;
use Src\Main\Database\Eloquent\Traits\Model\HasTimestamps;
use Src\Main\Database\Eloquent\Traits\Model\HasUniqueIds;
use Src\Main\Database\Eloquent\Traits\Model\HidesAttributes;
use Src\Main\Database\Exceptions\Eloquent\JsonEncodingException;
use Src\Main\Database\Exceptions\Eloquent\MassAssignmentException;
use Src\Main\Database\Exceptions\Eloquent\MissingAttributeException;
use Src\Main\Database\IConnectionResolver;
use Src\Main\Routing\IRouteParameter;
use Src\Main\Support\Traits\ForwardsCalls;
use Src\Main\Utils\Str;

abstract class Model implements IRouteParameter, ArrayAccess
{
    use HandleBoot,
        HandleCrud,
        HasAttributes,
        GuardsAttributes,
        HasUniqueIds,
        HasTimestamps,
        HasGlobalScopes,
        HasRelationships,
        HasEvents,
        HidesAttributes,
        ForwardsCalls;

    protected ?string $connection = null;
    protected string $primaryKey = "id";
    protected string $keyType = 'int';
    protected string $table;
    protected int $perPage = 10;

    public bool $exists = false;
    public bool $wasRecentlyCreated = false;
    protected bool $incrementing = true;
    protected array $with = [];
    protected static IConnectionResolver $resolver;
    public function __construct(array $attributes = [])
    {
        $this->init($attributes);
    }
    public static function setConnectionResolver(IConnectionResolver $resolver): void
    {
        static::$resolver = $resolver;
    }
    public static function getConnectionResolver(): IConnectionResolver
    {
        return static::$resolver;
    }
    public static function resolveConnection(string $connection = null): Connection
    {
        return static::$resolver->connection($connection);
    }
    public static function with(string ...$relations): EloquentBuilder
    {
        return static::query()->with(...$relations);
    }
    public function setConnectionName(?string $name): static
    {
        $this->connection = $name;

        return $this;
    }
    public function getConnectionName(): ?string
    {
        if (is_null($this->connection)) {
            $this->setConnectionName(self::resolveConnection()->getName());
        }

        return $this->connection;
    }
    public function getConnection(): Connection
    {
        return static::resolveConnection($this->getConnectionName());
    }
    public function setKeyName(string $key): static
    {
        $this->primaryKey = $key;

        return $this;
    }
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }
    public function getKeyType(): string
    {
        return $this->keyType;
    }
    public function setTable(string $table): static
    {
        $this->table = $table;

        return $this;
    }
    public function getTable(): string
    {
        return $this->table ?? Str::snake(Str::pluralStudly(class_basename($this)));
    }
    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }
    public function getPerPage(): int
    {
        return $this->perPage;
    }
    public function setIncrementing(bool $value): static
    {
        $this->incrementing = $value;

        return $this;
    }
    public function getIncrementing(): bool
    {
        return $this->incrementing;
    }
    public function fill(array $attributes): static
    {
        $this->checkGuard($attributes);

        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }
    public function forceFill(array $attributes): static
    {
        return static::unguarded(fn() => $this->fill($attributes));
    }
    public function newModelQuery(): EloquentBuilder
    {
        return $this->newEloquentBuilder()->setModel($this);
    }
    public function newQueryWithoutScopes(): EloquentBuilder
    {
        return $this->newModelQuery()
            ->with(...$this->with);
    }
    public function newQueryWithoutRelationships(): EloquentBuilder
    {
        return $this->registerGlobalScopes($this->newModelQuery());
    }
    public function getKey(): int
    {
        return $this->getAttribute($this->getKeyName());
    }
    public function getQualifiedKeyName(): string
    {
        return $this->qualifyColumn($this->getKeyName());
    }
    public function qualifyColumn(string $column): string
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        return $this->getTable() . '.' . $column;
    }
    public function qualifyColumns(string ...$columns): array
    {
        return array_map(fn($column) => $this->qualifyColumn($column), $columns);
    }
    public function newInstance(array $attributes = [], bool $exists = false): static
    {
        $model = new static($attributes);

        $model->exists = $exists;

        $model->setConnectionName($this->getConnectionName());

        $model->setTable($this->getTable());

        $model->mergeCasts($this->casts);

        return $model;
    }
    public function newFromBuilder(array $attributes = [], string $connection = null): static
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes($attributes, true);

        $model->setConnectionName($this->getConnectionName());

        return $model;
    }
    public function newCollection(array $models = []): Collection
    {
        return new Collection($models);
    }
    public function hydrate(array $array): Collection
    {
        return $this->newQuery()->hydrate($array);
    }
    public function is(Model $model): bool
    {
        return $this->getKey() === $model->getKey() &&
            $this->getTable() === $model->getTable() &&
            $this->getConnectionName() === $model->getConnectionName();
    }
    public function isNot(Model $model): bool
    {
        return !$this->is($model);
    }
    public function fresh(string ...$relations): ?static
    {
        if (!$this->exists) {
            return null;
        }

        return $this->setKeysForSave($this->newQueryWithoutScopes())
            ->with(...$relations)
            ->first();
    }
    public function toJson(): string
    {
        $json = json_encode($this->jsonSerialize());

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }
    public function toArray(): array
    {
        return $this->getAttributes();
        //         return array_merge($this->attributesToArray(), $this->relationsToArray());
    }
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
    public function getRouteKey(): int
    {
        return $this->getAttribute($this->getRouteKeyName());
    }
    public function getRouteKeyName(): string
    {
        return $this->getKeyName();
    }
    public function resolveRouteBinding(string $value, string $field = null): static
    {
        return $this->resolveRouteBindingQuery($this, $value, $field)->first();
    }
    public function newPivot(self $parent, array $attributes, string $table, bool $exists): Pivot
    {
        return Pivot::fromAttributes($parent, $attributes, $table, $exists);
    }
    protected function init(array $attributes): void
    {
        $this->bootIfNotBooted();
        $this->syncOriginal();
        $this->fill($attributes);
    }
    protected function checkGuard(array $attributes = []): void
    {
        if (empty($attributes)) {
            return;
        }

        $guardAll = $this->guardAll();

        $fillable = $this->fillableFromArray($attributes);

        if ($guardAll || $attributes !== $fillable) {
            $keys = array_diff(array_keys($attributes), array_keys($fillable));
            throw new MassAssignmentException($keys, $this);
        }
    }
    protected function newEloquentBuilder(): EloquentBuilder
    {
        return new EloquentBuilder(
            $this->getConnection()->query()
        );
    }
    public function newQuery(): EloquentBuilder
    {
        return $this->registerGlobalScopes($this->newQueryWithoutScopes());
    }
    protected function resolveRouteBindingQuery(Model $query, string $value, string $field = null)
    {
        return $query->where($field ?? $this->getRouteKeyName(), $value);
    }
    public function offsetExists($offset): bool
    {
        try {
            return $this->getAttribute($offset) != null;
        } catch (MissingAttributeException) {
            return false;
        }
    }
    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset], $this->relations[$offset]);
    }
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }
    public function __isset($key): bool
    {
        return $this->offsetExists($key);
    }
    public function __unset($key): void
    {
        $this->offsetUnset($key);
    }
    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo($this->newQuery(), $name, $arguments);
    }
    public static function __callStatic(string $method, array $parameters)
    {
        return (new static())->$method(...$parameters);
    }
}
