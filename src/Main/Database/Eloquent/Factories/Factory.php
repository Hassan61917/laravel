<?php

namespace Src\Main\Database\Eloquent\Factories;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Support\Traits\ForwardsCalls;
use Src\Main\Utils\Str;

abstract class Factory
{
    use ForwardsCalls;
    public static string $namespace = 'Database\\Factories\\';
    protected ?string $connection;
    protected Collection $states;
    public function __construct(
        protected int $count = 1,
    ) {
        $this->states = new Collection();
    }
    public static function factoryForModel(string $modelName): static
    {
        $factory = static::resolveFactoryName($modelName);

        return $factory::new();
    }
    public static function resolveFactoryName(string $modelName): string
    {
        $resolver = self::getNameResolver();

        return $resolver($modelName);
    }

    public static function new(): static
    {
        return (new static())->configure();
    }
    public static function times(int $count): static
    {
        return static::new()->count($count);
    }
    protected static function getNameResolver(): Closure
    {
        return  function (string $modelName) {
            $appNamespace = static::appNamespace();

            $modelNamespace = $appNamespace . '\\Models\\';

            $modelName = str_starts_with($modelName, $modelNamespace)
                ? Str::after($modelName, $modelNamespace)
                : Str::after($modelName, $appNamespace);

            return static::$namespace . $modelName . "Factory";
        };
    }
    protected static function appNamespace(): string
    {
        return app()->getNamespace();
    }
    public function configure(): static
    {
        return $this;
    }
    public function count(int $count): static
    {
        return $this->newInstance(['count' => $count]);
    }
    public function state(array $state = []): static
    {
        return $this->newInstance([
            'states' => $this->states->push($state),
        ]);
    }
    public function set(string $key, mixed $value): static
    {
        return $this->state([$key => $value]);
    }
    public function raw(array $attributes = [], ?Model $parent = null): array
    {
        if ($this->count == 1) {
            return $this->state($attributes)->getExpandedAttributes($parent);
        }

        return array_map(
            fn() =>
            $this->state($attributes)->getExpandedAttributes($parent),
            range(1, $this->count)
        );
    }
    public function newModel(array $attributes = []): Model
    {
        $model = $this->modelName();

        return new $model($attributes);
    }
    public function make(array $attributes = [], ?Model $parent = null): Collection
    {
        if (count($attributes) > 0) {
            return $this->state($attributes)->make([], $parent);
        }

        return $this->newModel()->newCollection(array_map(
            fn() => $this->makeModel($parent),
            range(1, $this->count)
        ));
    }
    public function makeOne(array $attributes = []): Model
    {
        return $this->count(1)->make($attributes)->get(0);
    }
    public function create(array $attributes = [], ?Model $parent = null)
    {
        if (count($attributes) > 0) {
            return $this->state($attributes)->create([], $parent);
        }

        $results = $this->make($attributes, $parent);

        $this->store($results);

        return $results;
    }
    public function createOne(array $attributes = [])
    {
        return $this->count(1)->create($attributes);
    }
    public function createMany(int $count): Collection
    {
        return collect(
            array_map(
                fn($record) => $this->state($record)->create(),
                range(0, $count)
            )
        );
    }
    protected function newInstance(array $arguments = []): static
    {
        $items = [
            'count' => $this->count,
            'states' => $this->states,
        ];

        return new static(...array_values(array_merge($items, $arguments)));
    }
    protected function getExpandedAttributes(?Model $parent): array
    {
        return $this->expandAttributes($this->getRawAttributes($parent));
    }
    protected function getRawAttributes(?Model $parent): array
    {
        return $this->states->reduce(
            function ($carry, $state) use ($parent) {

                return array_merge($carry, $state);
            },
            $this->definition()
        );
    }
    protected function expandAttributes(array $definition): array
    {
        $evaluateRelations = function ($attribute) {
            return $attribute instanceof Model ?  $attribute->getKey() : $attribute;
        };

        $callback = function ($attribute, $key) use (&$definition, $evaluateRelations) {

            if ($attribute instanceof Closure) {
                $attribute = $attribute($definition);
            }

            return $definition[$key] = $evaluateRelations($attribute);
        };

        return collect($definition)
            ->map($evaluateRelations)
            ->map($callback)
            ->all();
    }
    protected function modelName(): string
    {
        $resolver =  function (self $factory) {
            $namespacedFactoryBasename = Str::replaceLast(
                'Factory',
                '',
                Str::replaceFirst(static::$namespace, '', get_class($factory))
            );

            $factoryBasename = Str::replaceLast('Factory', '', class_basename($factory));

            $appNamespace = static::appNamespace();

            return class_exists($appNamespace . '\\Models\\' . $namespacedFactoryBasename)
                ? $appNamespace . '\\Models\\' . $namespacedFactoryBasename
                : $appNamespace . $factoryBasename;
        };

        return $resolver($this);
    }
    protected function makeModel(?Model $parent): Model
    {
        return Model::unguarded(function () use ($parent) {
            $instance = $this->newModel($this->getExpandedAttributes($parent));

            if (isset($this->connection)) {
                $instance->setConnectionName($this->connection);
            }

            return $instance;
        });
    }
    protected function store(Collection $results): void
    {
        $results->each(function (Model $model) {
            if (! isset($this->connection)) {
                $model->getConnectionName();
            }

            $model->save();

            foreach ($model->getRelations() as $name => $items) {
                if ($items instanceof Enumerable && $items->isEmpty()) {
                    $model->unsetRelation($name);
                }
            }
        });
    }
    public abstract function definition(): array;
}
