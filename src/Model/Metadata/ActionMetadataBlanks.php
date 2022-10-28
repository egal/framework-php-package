<?php

namespace Egal\Model\Metadata;

use Egal\Model\Enums\VariableType;

final class ActionMetadataBlanks
{

    public static function getMetadata(): ActionMetadata
    {
        return ActionMetadata::make('getMetadata');
    }

    public static function getItem(VariableType $keyType): ActionMetadata
    {
        return ActionMetadata::make('getItem')
            ->addParameters([
                ActionParameterMetadata::make('key', $keyType)
                    ->required(),
                ActionParameterMetadata::make('relations', VariableType::ARRAY)
                    ->nullable(),
            ]);
    }

    public static function getItems(): ActionMetadata
    {
        return ActionMetadata::make('getItems')
            ->addParameters([
                ActionParameterMetadata::make('pagination', VariableType::ARRAY)
                    ->nullable(),
                ActionParameterMetadata::make('relations', VariableType::ARRAY)
                    ->nullable(),
                ActionParameterMetadata::make('filter', VariableType::ARRAY)
                    ->nullable(),
                ActionParameterMetadata::make('order', VariableType::ARRAY)
                    ->nullable(),
            ]);
    }

    // TODO: actionGetCount

    public static function create(): ActionMetadata
    {
        return ActionMetadata::make('create')
            ->addParameters([
                ActionParameterMetadata::make('attributes', VariableType::ARRAY)
                    ->nullable(),
                ActionParameterMetadata::make('relations', VariableType::ARRAY)
                    ->nullable(),
            ]);
    }

    // TODO: actionCreateMany

    // TODO: actionUpdate

    public static function update(VariableType $keyType): ActionMetadata
    {
        return ActionMetadata::make('update')
            ->addParameters([
                ActionParameterMetadata::make('key', $keyType)
                    ->required(),
                ActionParameterMetadata::make('attributes', VariableType::ARRAY)
                    ->nullable(),
                ActionParameterMetadata::make('relations', VariableType::ARRAY)
                    ->nullable(),
            ]);
    }

    // TODO: actionUpdateMany

    // TODO: actionUpdateBatch

    public static function delete(VariableType $keyType): ActionMetadata
    {
        return ActionMetadata::make('delete')
            ->addParameters([
                ActionParameterMetadata::make('key', $keyType)
                    ->required(),
            ]);
    }

    // TODO: actionDeleteMany

    // TODO: actionDeleteBatch

}
