<?php

namespace Egal\Tests\Core\Rest\Filter;

use Egal\Core\Database\Metadata\Field as FieldMetadata;
use Egal\Core\Database\Metadata\Model as ModelMetadata;
use Egal\Core\Database\Model;
use Egal\Core\Rest\Filter\Applier as FilterApplier;
use Egal\Core\Rest\Filter\Combiner;
use Egal\Core\Rest\Filter\FieldCondition;
use Egal\Core\Rest\Filter\Field;
use Egal\Core\Rest\Filter\MorphRelationField;
use Egal\Core\Rest\Filter\Operator;
use Egal\Core\Rest\Filter\Query as FilterQuery;
use Egal\Core\Rest\Filter\RelationField;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class FilterApplierTest extends TestCase
{
    use DatabaseSchema;

    /**
     * @dataProvider filterApplierDataProviderField()
     * @dataProvider filterApplierDataProviderRelationField()
     */
    public function testFilterApplier(FilterQuery $filterQuery, array|string $expected)
    {

        if (is_string($expected)) {
            $this->expectException($expected);
        }

        $model = new ModelFilterApplierTestPost();
        FilterApplier::validateQuery($model->getMetadata(), $filterQuery);
        $query = FilterApplier::applyQuery($model::query(), $filterQuery);
        $result = array_column($query->get()->toArray(), 'id');

        $this->assertEquals($expected, $result);

    }

    /**
     * @dataProvider filterApplierDataProviderMorphRelationField()
     */
    public function testFilterByMorphRelationApplier(FilterQuery $filterQuery, array|string $expected)
    {

        if (is_string($expected)) {
            $this->expectException($expected);
        }

        $model = new ModelFilterApplierTestComment();
        FilterApplier::validateQuery($model->getMetadata(), $filterQuery);
        $query = FilterApplier::applyQuery($model::query(), $filterQuery);
        $result = array_column($query->get()->toArray(), 'id');

        $this->assertEquals($expected, $result);

    }

    public function filterApplierDataProviderField(): array
    {
        $fieldName = new Field('title');
        $fieldCategoryId = new Field('channel_id');

        return [
            [
                FilterQuery::make([
                    FieldCondition::make($fieldName, Operator::Equals, 'first pst'),
                ]),
                [1]
            ],
            [
                FilterQuery::make([
                    FieldCondition::make($fieldName, Operator::Equals, 'first pst'),
                    FieldCondition::make($fieldName, Operator::Equals, 'second pst', Combiner::Or)
                ]),
                [1, 2]
            ],
            [
                FilterQuery::make([
                    FieldCondition::make($fieldName, Operator::Equals, 'first pst'),
                    FieldCondition::make($fieldName, Operator::Equals, 'second pst')
                ]),
                []
            ],
            [
                FilterQuery::make([
                    FieldCondition::make($fieldCategoryId, Operator::Equals, null)
                ]),
                [1]
            ],
            [
                FilterQuery::make([
                    FieldCondition::make($fieldCategoryId, Operator::NotEquals, null),
                    FilterQuery::make([
                        FieldCondition::make($fieldName, Operator::Equals, 'first pst'),
                        FieldCondition::make($fieldName, Operator::Equals, 'second pst', Combiner::Or),
                    ], Combiner::And),
                ]),
                [2]
            ]
        ];
    }

    public function filterApplierDataProviderRelationField(): array
    {
        $fieldCategoryName = new RelationField('title', 'channel');

        return [
            [
                FilterQuery::make([
                    FieldCondition::make($fieldCategoryName, Operator::Equals, 'first chl'),
                ]),
                [2]
            ],
            [
                FilterQuery::make([
                    FieldCondition::make($fieldCategoryName, Operator::Equals, 'first chl'),
                    FieldCondition::make($fieldCategoryName, Operator::Equals, 'second chl', Combiner::Or)
                ]),
                [2]
            ],
            [
                FilterQuery::make([
                    FieldCondition::make($fieldCategoryName, Operator::Equals, 'first chl'),
                    FieldCondition::make($fieldCategoryName, Operator::Equals, 'second chl')
                ]),
                []
            ],
            [
                FilterQuery::make([
                    FieldCondition::make($fieldCategoryName, Operator::Equals, null)
                ]),
                []
            ]
        ];
    }

    public function filterApplierDataProviderMorphRelationField(): array
    {
        $fieldCategoryCommentContent = new MorphRelationField('content', 'commentable', [ModelFilterApplierTestChannel::class]);
        $fieldProductCommentContent = new MorphRelationField('content', 'commentable', [ModelFilterApplierTestPost::class]);

        return [
            [
                FilterQuery::make([
                    FieldCondition::make($fieldCategoryCommentContent, Operator::StartWithOperator, 'comment'),
                ]),
                [1, 2]
            ],
            [
                FilterQuery::make([
                    FieldCondition::make($fieldProductCommentContent, Operator::StartWithOperator, 'comment')
                ]),
                [3]
            ],
            [
                FilterQuery::make([
                    FieldCondition::make($fieldProductCommentContent, Operator::StartWithOperator, 'comment'),
                    FieldCondition::make($fieldCategoryCommentContent, Operator::StartWithOperator, 'comment')
                ]),
                []
            ],
            [
                FilterQuery::make([
                    FieldCondition::make($fieldProductCommentContent, Operator::StartWithOperator, 'not')
                ]),
                []
            ]
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

        ModelFilterApplierTestChannel::query()->insert(['title' => 'first chl']);
        ModelFilterApplierTestChannel::query()->insert(['title' => 'second chl']);

        ModelFilterApplierTestPost::query()->insert(['title' => 'first pst']);
        ModelFilterApplierTestPost::query()->insert(['title' => 'second pst', 'channel_id' => 1]);

        ModelFilterApplierTestComment::query()->insert([
            'commentable_type' => ModelFilterApplierTestChannel::class,
            'commentable_id' => 1,
            'content' => 'comment to 1 channel'
        ]);
        ModelFilterApplierTestComment::query()->insert([
            'commentable_type' => ModelFilterApplierTestChannel::class,
            'commentable_id' => 2,
            'content' => 'comment to 2 channel'
        ]);
        ModelFilterApplierTestComment::query()->insert([
            'commentable_type' => ModelFilterApplierTestPost::class,
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

class ModelFilterApplierTestChannel extends Model
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

class ModelFilterApplierTestPost extends Model
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
        return $this->belongsTo(ModelFilterApplierTestChannel::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(ModelFilterApplierTestComment::class, 'commentable');
    }

}

class ModelFilterApplierTestComment extends Model
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
