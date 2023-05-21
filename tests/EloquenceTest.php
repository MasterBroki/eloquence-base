<?php

namespace Sofa\Eloquence\Tests;

use Illuminate\Database\Eloquent\Model;
use Mockery;
use Sofa\Eloquence\Eloquence;

class EloquenceTest extends TestCase
{
    protected function setUp(): void {
        EloquenceStub::flushHooks();
    }
    
    /**
     * @test
     * @covers \Sofa\Eloquence\Eloquence::newEloquentBuilder
     */
    public function it_uses_custom_builder() {
        $query = Mockery::mock('\Illuminate\Database\Query\Builder');
        $builder = (new EloquenceStub)->newEloquentBuilder($query);
        
        $this->assertInstanceOf('\Sofa\Eloquence\Builder', $builder);
    }
    
    /**
     * @test
     * @covers \Sofa\Eloquence\Eloquence::hasColumn
     * @covers \Sofa\Eloquence\Eloquence::getColumnListing
     * @covers \Sofa\Eloquence\Eloquence::loadColumnListing
     */
    public function it_loads_and_checks_the_column_listing() {
        $schema = Mockery::mock('StdClass');
        $schema->shouldReceive('getColumnListing')->once()->andReturn(['foo', 'bar', 'baz']);
        
        $connection = Mockery::mock('StdClass');
        $connection->shouldReceive('getSchemaBuilder')->once()->andReturn($schema);
        
        $resolver = Mockery::mock('\Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->once()->andReturn($connection);
        
        EloquenceStub::setConnectionResolver($resolver);
        
        $model = new EloquenceStub;
        
        $this->assertTrue($model->hasColumn('foo'));
        $this->assertFalse($model->hasColumn('wrong'));
        $this->assertEquals(['foo', 'bar', 'baz'], $model->getColumnListing());
    }
    
    /**
     * @test
     * @covers \Sofa\Eloquence\Eloquence::hook
     */
    public function it_registers_and_call_hooks_on_eloquent_methods() {
        $model = new EloquenceStub;
        
        EloquenceStub::hook('__isset', $model->__issetExtensionStub());
        
        $this->assertFalse(isset($model->foo));
        
        $model->foo = 1;
        $this->assertFalse(isset($model->foo));
    }
}

class EloquenceStub extends Model
{
    use Eloquence, ExtensionStub;
}

trait ExtensionStub
{
    public function __issetExtensionStub() {
        return function () {
            return false;
        };
    }
}
