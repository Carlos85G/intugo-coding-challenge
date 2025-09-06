<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use App\Exceptions\ModelJsonFilter\InvalidBuilderException;
use App\Exceptions\ModelJsonFilter\InvalidJsonException;
/**
 * Helper class to create model query builders from JSON strings
 * 
 * @author Carlos González
 */
class ModelJsonFilter {
    /** @var string Separator for nested relationships */
    protected static string $separator = '.';

    /**
     * Public function to create a query builder from a JSON string and a model classname.
     * 
     * @param   string    $json     The filters to create in the builder.
     * @param   Builder   $query    The query to modify, filtered.
     * @return  Builder             The filtered builder.
     * @throws  Exception
     */
    public static function makeBuilderFromJson(string $json, Builder &$query) : void
    {
        /** @var mixed[] */
        $filters = self::parseJson($json);

        self::addWhereClausesByFilters($query, $filters);
    }

    protected static function getModelClassFromQuery(Builder $query) : string
    {
        return get_class($query->getModel());
    }

    /**
     * Protected function to get a tag from the model in the builder.
     * 
     * @param   Builder   $query    The builder.
     * @return  string              The tag.
     */
    protected static function getModelClassTag(Builder $query) : string
    {
        $class = self::getModelClassFromQuery($query);

        return strtolower(
            class_basename($class)
        );
    }

    /**
     * Protected function to generate a builder from a model class name.
     * 
     * @param   string                  $class  The model class name.
     * @return  Builder                         The builder.
     * @throws  InvalidClassException
     */
    protected static function validateQuery(Builder $query) : bool
    {
        $result = true;

        $class = self::getModelClassFromQuery($query);

        if (!is_a($class, Model::class, true)) {
            $result = false;
        }

        return $result;
    }

    /**
     * Protected function to add where clauses and groups of where clauses,
     * according to filter definitions.
     * 
     * @param   Builder   $query    The builder to add the clauses to, passed by reference.
     * @param   mixed[]   $filters  The filters. Keys are foreign and local property names,
     *                              values are the data to check against.
     * @return  void
     */
    protected static function addWhereClausesByFilters(Builder &$query, array $filters) : void
    {
        if (!self::validateQuery($query)) {
            throw new InvalidBuilderException('Invalid builder - Does not correspond to a model');
        }                    

        [
            /** @var string[] Relationship keys */
            $filterKeys,
            /** @var string[] Property data */
            $fieldData
        ] = Arr::divide($filters);

        foreach ($filterKeys as $index => $filterKey) {
            [
                /**
                 * Model relationship chain.
                 * 
                 * Empty means local model property
                 * 
                 * @var string
                 * */
                $relationship,
                /** @var string Field name at destination model */
                $field
            ] = self::getRelationshipAndFieldFromKey($filterKey, self::getModelClassTag($query));

            /** @var mixed Data to look for */
            $value = $fieldData[$index];

            /**
             * Check if there are multiple values to look for
             * in the same property.
             * 
             * @var bool
             */
            $isValueArray = is_array($value);

            /** @var bool Check if there's a relationship */
            $isRelationship = !empty($relationship);

            if ($isRelationship && $isValueArray) {
                /* Relationship property with multiple values */
                self::addNestedOrWhereHas($query, $relationship, $field, $value);
            } else if ($isValueArray) {
                /* Local property with multiple values */
                self::addNestedOrWhere($query, $field, $value);
            } else if ($isRelationship) {
                /* Relationship property with single value */
                self::addWhereHas($query, $relationship, $field, $value);
            } else {
                /* Local property with single value */
                self::addWhere($query, $field, $value);
            }
        }
    }

    /**
     * Protected function to parse JSON filters for the builder
     * 
     * @param   string              $json   The filter text
     * @return  mixed[]                     The parsed filter data
     * @throws  InvalidJsonException
     */
    protected static function parseJson(string $json) : array
    {
        $data = json_decode($json, true);

        if (is_null($data)) {
            throw new InvalidJsonException("Invalid JSON filter");
        }

        return $data;
    }

    /**
     * Protected function to separate relationships and target property names
     * 
     * @param   string    $key  The filter relationship key
     * @param   string    $tag  The model class name tag, to identify local class
     * @return  string[]        The relationship path, in dot syntax,
     *                          and the target property name
     */
    protected static function getRelationshipAndFieldFromKey(string $key, string $tag) : array
    {
        /** @var string[] */
        $chunks = explode(self::$separator, $key);

        /** @var string */
        $field = array_pop($chunks);

        if (!empty($chunks) && strtolower($chunks[0]) == $tag) {
            /* First as class ID defaults to current builder, so it's removed */
            array_shift($chunks);
        }

        /** @var string */
        $relationship = implode(self::$separator, $chunks);

        return [$relationship, $field];
    }

    /**
     * Protected function to add a where clause to the builder
     * 
     * @param   Builder $query    The query.
     * @param   string  $field    The property to compare.
     * @param   string  $value    The property value to compare against.
     * @return  void
     */
    protected static function addWhere(Builder &$query, string $field, string $value) : void
    {
        $query->where($field, $value);
    }

    /**
     * Protected function to add an orWhere clause to the builder
     * 
     * @param   Builder $query    The query.
     * @param   string  $field    The property to compare.
     * @param   string  $value    The property value to compare against.
     * @return  void
     */
    protected static function addOrWhere(Builder &$query, string $field, string $value) : void
    {
        $query->orWhere($field, $value);
    }

    /**
     * Protected function to add a whereHas clause to the builder
     * 
     * @param   Builder $query    The query.
     * @param   string  $field    The property to compare.
     * @param   string  $value    The property value to compare against.
     * @return  void
     */
    protected static function addWhereHas(Builder &$query, string $relationship, string $field, string $value) : void
    {
        $query->whereHas($relationship, function ($query) use ($field, $value) : void {
            self::addWhere($query, $field, $value);
        });
    }

    /**
     * Protected function to add an orWhereHas clause to the builder
     * 
     * @param   Builder $query    The query.
     * @param   string  $field    The property to compare.
     * @param   string  $value    The property value to compare against.
     * @return  void
     */
    protected static function addOrWhereHas(Builder &$query, string $relationship, string $field, string $value) : void
    {
        $query->orWhereHas($relationship, function ($query) use ($field, $value) : void {
            self::addWhere($query, $field, $value);
        });
    }

    /**
     * Protected function to add a group of nested orWhereHas clauses to the builder
     * 
     * @param   Builder $query          The query.
     * @param   string  $relationship   The property to compare.
     * @param   string  $field          The property to compare.
     * @param   mixed[] $values         The property values to compare against.
     * @return  void
     */
    protected static function addNestedOrWhereHas(Builder &$query, string $relationship, string $field, array $values) : void
    {
        $query->where(function ($query) use ($relationship, $field, $values) : void {
            $firstWhere = true;

            foreach ($values as $value) {
                self::{($firstWhere) ? "addWhereHas" : "addOrWhereHas"}($query, $relationship, $field, $value);

                if ($firstWhere) {
                    $firstWhere = false;
                }
            }
        });
    }

    /**
     * Protected function to add a group of nested orWhere clauses to the builder
     * 
     * @param   Builder $query          The query.
     * @param   string  $field          The property to compare.
     * @param   mixed[] $values         The property values to compare against.
     * @return  void
     */
    protected static function addNestedOrWhere(Builder &$query, string $field, array $values) : void
    {
        $query->where(function ($query) use ($field, $values) : void {
            $firstWhere = true;

            foreach ($values as $value) {
                self::{($firstWhere) ? "addWhere" : "addOrWhere"}($query, $field, $value);

                if ($firstWhere) {
                    $firstWhere = false;
                }
            }
        });
    }
}