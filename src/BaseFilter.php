<?php

declare(strict_types=1);

namespace SimonAnkele\LaravelQueryPipelines;

abstract class BaseFilter extends BasePipe
{
    protected mixed $ignore;

    protected ?string $field = null;

    protected string $detector;

    protected ?string $searchColumn = null;

    protected mixed $searchValue = null;

    public function __construct()
    {
        parent::__construct();

        $this->ignore(null);
        $this->detector = config('query-pipelines.detect_key', '');
    }

    public function handle($query, \Closure $next)
    {
        $this->query = $query;
        if (! $this->shouldFilter($this->getFilterName())) {
            return $next($query);
        }
        $this->apply();

        return $next($this->query);
    }

    public function value(mixed $searchValue): static
    {
        $this->searchValue = $searchValue;

        return $this;
    }

    protected function getFilterName(): string
    {
        return "{$this->detector}{$this->field}";
    }

    /** @return array<mixed> */
    protected function getSearchValue(): array
    {
        if (! is_null($this->searchValue)) {
            $searchValue = $this->searchValue;
        } else {
            $searchValue = $this->request->input($this->getFilterName());
        }

        if (! is_array($searchValue)) {
            $searchValue = [$searchValue];
        }

        return $searchValue;
    }

    public function filterOn(string $searchColumn): static
    {
        $this->searchColumn = $searchColumn;

        return $this;
    }

    protected function getSearchColumn(): ?string
    {
        return $this->searchColumn ?? $this->field;
    }

    public function ignore(mixed $ignore = ''): static
    {
        $this->ignore = $ignore;

        return $this;
    }

    public function field(string $field = ''): static
    {
        $this->field = $field;

        return $this;
    }

    public function detectBy(string $detector): static
    {
        $this->detector = $detector;

        return $this;
    }

    protected function shouldFilter(string $key): bool
    {
        if (isset($this->searchValue)) {
            return true;
        }

        if (! $this->request->has($key)) {
            return false;
        }

        if ($this->ignore === $this->request->input($key)) {
            return false;
        }

        return true;
    }
}
