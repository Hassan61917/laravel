<?php

namespace Src\Main\Database\Exceptions\Eloquent;

class ModelNotFoundException extends RecordsNotFoundException
{
    protected string $model;
    protected array $ids;
    public function setModel(string $model, string ...$ids): static
    {
        $this->model = $model;

        $this->setIds($ids);

        $this->message = "No query results for model [{$model}]";

        if (count($this->ids) > 0) {
            $this->message .= ' ' . implode(', ', $this->ids);
        } else {
            $this->message .= '.';
        }

        return $this;
    }
    public function getModel(): string
    {
        return $this->model;
    }
    public function getIds(): array
    {
        return $this->ids;
    }
    public function setIds(array $ids): void
    {
        $this->ids = $ids;
    }
}
