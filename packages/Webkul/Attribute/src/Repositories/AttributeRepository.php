<?php

namespace Webkul\Attribute\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Webkul\Core\Eloquent\Repository;

class AttributeRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeOptionRepository $attributeOptionRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'Webkul\Attribute\Contracts\Attribute';
    }

    /**
     * @return \Webkul\Attribute\Contracts\Attribute
     */
    public function create(array $data)
    {
        $options = isset($data['options']) ? $data['options'] : [];

        $attribute = $this->model->create($data);

        if (in_array($attribute->type, ['select', 'multiselect', 'checkbox']) && count($options)) {
            $sortOrder = 1;

            foreach ($options as $optionInputs) {
                $this->attributeOptionRepository->create(array_merge([
                    'attribute_id' => $attribute->id,
                    'sort_order' => $sortOrder++,
                ], $optionInputs));
            }
        }

        return $attribute;
    }

    /**
     * @param  int  $id
     * @param  string  $attribute
     * @return \Webkul\Attribute\Contracts\Attribute
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        $attribute = $this->find($id);

        $attribute->update($data);

        if (!in_array($attribute->type, ['select', 'multiselect', 'checkbox'])) {
            return $attribute;
        }

        if (!isset($data['options'])) {
            return $attribute;
        }

        foreach ($data['options'] as $optionId => $optionInputs) {
            $isNew = $optionInputs['isNew'] == 'true';

            if ($isNew) {
                $this->attributeOptionRepository->create(array_merge([
                    'attribute_id' => $attribute->id,
                ], $optionInputs));
            } else {
                $isDelete = $optionInputs['isDelete'] == 'true';

                if ($isDelete) {
                    $this->attributeOptionRepository->delete($optionId);
                } else {
                    $this->attributeOptionRepository->update($optionInputs, $optionId);
                }
            }
        }

        return $attribute;
    }

    /**
     * @param  string  $code
     * @return \Webkul\Attribute\Contracts\Attribute
     */
    public function getAttributeByCode($code)
    {
        static $attributes = [];

        if (array_key_exists($code, $attributes)) {
            return $attributes[$code];
        }

        return $attributes[$code] = $this->findOneByField('code', $code);
    }

    /**
     * @param  int  $lookup
     * @param  string  $query
     * @param  array  $columns
     * @return array
     */
    public function getLookUpOptions($lookup, $query = '', $columns = [])
    {
        Log::info("Debug: getLookUpOptions called", ['lookup' => $lookup, 'query' => $query]);

        $lookupData = config('attribute_lookups.' . $lookup);

        if (!$lookupData) {
            Log::warning("Lookup data configuration not found for key: $lookup");
            return [];
        }

        $repository = app($lookupData['repository']);
        $table = $repository->getModel()->getTable();
        $labelColumn = $lookupData['label_column'] ?? 'name';
        $valueColumn = $lookupData['value_column'] ?? 'id';

        $query = urldecode((string) $query);

        $dbQuery = DB::table($table);

        if ($query !== '') {
            $dbQuery->where($labelColumn, 'like', '%' . $query . '%');
        }

        $userIds = bouncer()->getAuthorizedUserIds();

        if ($userIds) {
            $column = Str::contains($lookupData['repository'], 'UserRepository') ? 'id' : 'user_id';

            // Check if column exists in table to avoid SQL crashing
            if (Schema::hasColumn($table, $column)) {
                $dbQuery->where(function ($q) use ($column, $userIds) {
                    $q->whereIn($column, $userIds)
                        ->orWhereNull($column)
                        ->orWhere($column, 0);
                });
            }
        }

        if ($lookup === 'users') {
            $dbQuery->where('status', 1);
        }

        // Standard Laravel logging for production reliability
        Log::info("Lookup Search Executed", [
            'lookup' => $lookup,
            'query' => $query,
            'table' => $table,
            'sql' => $dbQuery->toSql(),
            'binds' => $dbQuery->getBindings()
        ]);

        $results = $dbQuery->limit(20)->get([
            $valueColumn . ' as id',
            $labelColumn . ' as name',
        ]);

        return $results->toArray();
    }

    /**
     * @param  string  $lookup
     * @param  int|array  $entityId
     * @param  array  $columns
     * @return mixed
     */
    public function getLookUpEntity($lookup, $entityId = null, $columns = [])
    {
        Log::info("Debug: getLookUpEntity called", ['lookup' => $lookup, 'entityId' => $entityId]);

        if (!$entityId) {
            return;
        }

        $lookupData = config('attribute_lookups.' . $lookup);

        if (!$lookupData) {
            Log::warning("Lookup data configuration not found for key: $lookup");
            return;
        }

        if (!count($columns)) {
            $columns = [($lookupData['value_column'] ?? 'id') . ' as id', ($lookupData['label_column'] ?? 'name') . ' as name'];
        }

        try {
            if (is_array($entityId)) {
                return app($lookupData['repository'])->findWhereIn(
                    'id',
                    $entityId,
                    $columns
                );
            } else {
                return app($lookupData['repository'])->find($entityId, $columns);
            }
        } catch (\Exception $e) {
            Log::error("Lookup Entity Fetch Failed", ['lookup' => $lookup, 'id' => $entityId, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
