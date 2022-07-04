<?php

namespace Egal\Tests\Core\Rest\Select;

use App\Models\Channel;
use App\Models\Comment;
use Carbon\Carbon;
use Egal\Core\Database\Metadata\Field as FieldMetadata;
use Egal\Core\Database\Metadata\Model as ModelMetadata;
use Egal\Core\Database\Model;
use Egal\Core\Exceptions\EmptySelectException;
use Egal\Core\Rest\Filter\Field;
use Egal\Core\Rest\Filter\RelationField;
use Egal\Core\Rest\Select\Applier as SelectApplier;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class SelectApplierTest extends TestCase
{
    use DatabaseSchema;

    /**
     * @dataProvider selectApplierDataProviderField()
     * @dataProvider selectApplierDataProviderRelationField()
     */
    public function testSelectApplier(array $fields, array|string $expected)
    {

        if (is_string($expected)) {
            $this->expectException($expected);
        }

        $model = new ModelSelectApplierTestPost();
        $query = SelectApplier::apply($model::query(), $fields);
        $result = $query->first()->toArray();

        $this->assertEquals($expected, array_keys($result));

    }

    public function selectApplierDataProviderField()
    {
        return [
//            [
//                [new Field("title")],
//                ["title"]
//            ],
//            [
//                [new Field("title"), new Field("id")],
//                ["title", "id"]
//            ],
//            [
//                [],
//                EmptySelectException::class
//            ],
//            [
//                [new Field("title"), new Field("id"), new Field("created_at")],
//                ["title", "id", "created_at"]
//            ],
//            [
//                [new Field("title"), new Field("id"), new Field("channel_id")],
//                ["title", "id", "channel_id"]
//            ],
        ];
    }

    public function selectApplierDataProviderRelationField()
    {
        return [
            [
                [new Field("channel_id"), new RelationField("id", "channel"), new RelationField("title", "channel")],
                ["channel_id", "channel"]
            ],
//            [
//                [new Field("title"), new RelationField("id", "channel"), new RelationField("title", "channel")],
//                ["title", "channel"]
//            ]
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

        ModelSelectApplierTestChannel::query()->insert(['title' => 'first chl']);
        ModelSelectApplierTestChannel::query()->insert(['title' => 'second chl']);

        ModelSelectApplierTestPost::query()->insert(['title' => 'first pst']);
        ModelSelectApplierTestPost::query()->insert(['title' => 'second pst', 'channel_id' => 1]);

        ModelSelectApplierTestComment::query()->insert([
            'commentable_type' => ModelSelectApplierTestChannel::class,
            'commentable_id' => 1,
            'content' => 'comment to 1 channel'
        ]);
        ModelSelectApplierTestComment::query()->insert([
            'commentable_type' => ModelSelectApplierTestChannel::class,
            'commentable_id' => 2,
            'content' => 'comment to 2 channel'
        ]);
        ModelSelectApplierTestComment::query()->insert([
            'commentable_type' => ModelSelectApplierTestPost::class,
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
class ModelSelectApplierTestChannel extends Model
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

class ModelSelectApplierTestPost extends Model
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
        return $this->belongsTo(Channel::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopeCreatedAfterToday($query)
    {
        return $query->where('created_at', '>', Carbon::today()->toDateString());
    }

    public function scopeTitleStartWithK($query)
    {
        return $query->where('title', 'like', 'К%');
    }

    public function scopeRandomOne($query)
    {
        return $query->inRandomOrder()->limit(1);
    }
}

class ModelSelectApplierTestComment extends Model
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
