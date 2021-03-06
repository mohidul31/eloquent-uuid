<?php

use Marquine\EloquentUuid\Uuid;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class UuidTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->migrateTable();
        $this->setEventDispatcher();
    }

    protected function setUpDatabase()
    {
        $database = new DB;

        $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $database->bootEloquent();
        $database->setAsGlobal();
    }

    protected function migrateTable()
    {
        DB::schema()->create('users', function ($table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });
    }

    protected function setEventDispatcher()
    {
        if (! Model::getEventDispatcher()) {
            Model::setEventDispatcher(new Dispatcher);
        }
    }

    /** @test */
    function it_auto_fills_an_uuid_in_the_primary_key_column()
    {
        $model = new User;
        $model->save();

        $this->assertTrue(is_string($model->id));
        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($model->id));
    }

    /** @test */
    function it_does_not_override_an_already_filled_uuid()
    {
        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();

        $model = new User;
        $model->id = $uuid;
        $model->save();

        $this->assertEquals($uuid, $model->id);
    }

    /** @test */
    function it_overrides_an_invalid_uuid()
    {
        $model = new User;
        $model->id = 1; // anything thats not an uuid
        $model->save();

        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($model->id));
    }

    /** @test */
    function it_sets_the_models_incrementing_to_false()
    {
        $model = new User;

        $this->assertFalse($model->getIncrementing());
    }
}

class User extends Model { use Uuid; }
