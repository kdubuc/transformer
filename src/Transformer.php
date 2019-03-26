<?php

namespace Kdubuc\Transformer;

use API\Domain\Collection;
use Pagerfanta\Pagerfanta;
use API\Domain\ValueObject\ValueObject;
use League\Fractal\Manager as FractalManager;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Serializer\ArraySerializer as FractalSerializer;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter as FractalPaginatorAdapter;

abstract class Transformer
{
    /**
     * Build Transformer with a manager (if not provided, default manager
     * will be used with ArraySerializer).
     */
    public function __construct(FractalManager $manager = null)
    {
        if (null === $manager) {
            $manager = new FractalManager();
            $manager->setSerializer(new FractalSerializer());
        }

        $this->manager = $manager;
    }

    /**
     * Fractal wants a callable to perform transformation.
     */
    public function __invoke(ValueObject $value_object) : array
    {
        return $this->transform($value_object);
    }

    /**
     * Item resource object as an array.
     */
    public function item(ValueObject $data, self $transformer = null) : array
    {
        $resource = new FractalItem($data, $transformer ?? $this);

        $scope = $this->manager->createData($resource);

        return $scope->toArray();
    }

    /**
     * Collection resource object as an array.
     */
    public function collection(Collection $data, self $transformer = null) : array
    {
        $resource = new FractalCollection($data, $transformer ?? $this);

        $resource->setMeta($data->getMeta());

        $scope = $this->manager->createData($resource);

        return $scope->toArray();
    }

    /**
     * Pagination (with Pagerfanta) object as an array.
     */
    public function pagination(Pagerfanta $paginator, self $transformer = null) : array
    {
        $collection = $paginator->getCurrentPageResults();

        $resource = new FractalCollection($collection, $transformer ?? $this);

        if ($collection instanceof Collection) {
            $resource->setMeta($collection->getMeta());
        }

        $resource->setPaginator(new FractalPaginatorAdapter($paginator, function (int $page) {
            return null;
        }));

        $data = $this->manager->createData($resource)->toArray();

        unset($data['meta']['pagination']['links']);

        return $data;
    }
}
