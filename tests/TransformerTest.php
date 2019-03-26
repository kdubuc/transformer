<?php

use API\Domain\Collection;
use Pagerfanta\Pagerfanta;
use API\Domain\ValueObject\ID;
use PHPUnit\Framework\TestCase;
use Kdubuc\Transformer\Transformer;
use Pagerfanta\Adapter\FixedAdapter;

class TransformerTest extends TestCase
{
    public function testItemTransformer()
    {
        $id = ID::generate();

        $transformer = new class() extends Transformer {
            public function transform(ID $id) : array
            {
                return [
                    'id' => $id->toString(),
                ];
            }
        };

        $this->assertEquals(['id' => $id->toString()], $transformer->item($id));
    }

    public function testEmbeddedTransformer()
    {
        $id = ID::generate();

        $transformer = new class() extends Transformer {
            public function transform(ID $id) : array
            {
                return [
                    'id' => $this->item($id, new class() extends Transformer {
                        public function transform(ID $id) : array
                        {
                            return [
                                'userid' => $id->toString(),
                            ];
                        }
                    }),
                ];
            }
        };

        $this->assertEquals(['id' => ['userid' => $id->toString()]], $transformer->item($id));
    }

    public function testCollectionTransformer()
    {
        $ids = new Collection([
            ID::generate(),
            ID::generate(),
        ]);

        $transformer = new class() extends Transformer {
            public function transform(ID $id) : array
            {
                return [
                    'id' => $id->toString(),
                ];
            }
        };

        $results = $transformer->collection($ids);

        $this->assertArrayHasKey('data', $results);
        $this->assertCount(2, $results['data']);
        $this->assertEquals(['id' => $ids[0]->toString()], $results['data'][0]);
    }

    public function testPaginationTransformer()
    {
        $ids = new Collection([
            ID::generate(),
            ID::generate(),
        ]);

        $adapter    = new FixedAdapter(2, $ids);
        $pagerfanta = new Pagerfanta($adapter);

        $transformer = new class() extends Transformer {
            public function transform(ID $id) : array
            {
                return [
                    'id' => $id->toString(),
                ];
            }
        };

        $results = $transformer->pagination($pagerfanta);

        $this->assertArrayHasKey('data', $results);
        $this->assertCount(2, $results['data']);
        $this->assertEquals(['id' => $ids[0]->toString()], $results['data'][0]);
        $this->assertArrayHasKey('meta', $results);
        $this->assertArrayHasKey('pagination', $results['meta']);
        $this->assertEquals(2, $results['meta']['pagination']['total']);
    }
}
