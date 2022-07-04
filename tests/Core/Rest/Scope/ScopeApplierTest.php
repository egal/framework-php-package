<?php

namespace Egal\Tests\Core\Rest\Scope;

use Carbon\Carbon;
use Egal\Core\Database\Model;
use Egal\Core\Database\Metadata\Field as FieldMetadata;
use Egal\Core\Database\Metadata\Model as ModelMetadata;
use Egal\Core\Rest\Scope\Applier as ScopeApplier;
use Egal\Core\Rest\Scope\ScopeFunction;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class ScopeApplierTest extends TestCase
{
    use DatabaseSchema;

    /**
     * @dataProvider scopeApplierDataProvider()
     */
    public function testFilterApplier(array $scopes, array|string $expected)
    {

        if (is_string($expected)) {
            $this->expectException($expected);
        }

        $model = new ModelScopeApplierTestPost();
        $query = ScopeApplier::apply($model::query(), $scopes);
        $result = array_column($query->get()->toArray(), 'id');

        $this->assertEquals($expected, $result);

    }

    public function scopeApplierDataProvider()
    {
        return [
            [
                [ScopeFunction::make("titleStartWithCharacter", [["key" => "character", "value" => "f"]])],
                [1]
            ],
            [
                [ScopeFunction::make("titleStartWithCharacter", [["key" => "character", "value" => "s"]])],
                [2]
            ],
            [
                [ScopeFunction::make("titleStartWithCharacter", [["key" => "character", "value" => "a"]])],
                []
            ],
        ];
    }

    protected function createSchema(): void
    {
        $this->schema()->create('channels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        $this->schema()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->foreign('channel_id')->on('channels')->references('id');

            $table->timestamps();
        });

        $this->schema()->create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->morphs('commentable');
            $table->timestamps();
        });

        ModelScopeApplierTestChannel::query()->insert(['title' => 'first chl']);
        ModelScopeApplierTestChannel::query()->insert(['title' => 'second chl']);

        ModelScopeApplierTestPost::query()->insert(['title' => 'first pst']);
        ModelScopeApplierTestPost::query()->insert(['title' => 'second pst', 'channel_id' => 1]);

        ModelScopeApplierTestComment::query()->insert([
            'commentable_type' => ModelScopeApplierTestChannel::class,
            'commentable_id' => 1,
            'content' => 'comment to 1 channel'
        ]);
        ModelScopeApplierTestComment::query()->insert([
            'commentable_type' => ModelScopeApplierTestChannel::class,
            'commentable_id' => 2,
            'content' => 'comment to 2 channel'
        ]);
        ModelScopeApplierTestComment::query()->insert([
            'commentable_type' => ModelScopeApplierTestPost::class,
            'commentable_id' => 1,
            'content' => 'comment to 1 post'
        ]);
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('comments');
        $this->schema()->drop('posts');
        $this->schema()->drop('channels');
    }
}

class ModelScopeApplierTestChannel extends Model
{
    protected $table = 'channels';

    public function initializeMetadata(): ModelMetadata
    {
        return ModelMetadata::make(static::class)
            ->fields(
                FieldMetadata::make('title')
                    ->required()
                    ->fillable()
            );
    }
}

class ModelScopeApplierTestPost extends Model
{
    protected $table = 'posts';

    public function initializeMetadata(): ModelMetadata
    {
        return ModelMetadata::make(static::class)
            ->fields(
                FieldMetadata::make('title')
                    ->required()
                    ->fillable(),
                FieldMetadata::make('channel_id')
                    ->required()
                    ->fillable()
            );
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(ModelScopeApplierTestChannel::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(ModelScopeApplierTestComment::class, 'commentable');
    }

    public function scopeTitleStartWithCharacter($query, $character)
    {
        return $query->where('title', 'like', $character . '%');
    }
}

class ModelScopeApplierTestComment extends Model
{
    protected $table = 'comments';

    public function initializeMetadata(): ModelMetadata
    {
        return ModelMetadata::make(static::class)
            ->fields(
                FieldMetadata::make('content')
                    ->required()
                    ->fillable(),
                FieldMetadata::make('commentable_type')
                    ->required()
                    ->fillable(),
                FieldMetadata::make('commentable_id')
                    ->required()
                    ->fillable()
            );
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
