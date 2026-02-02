<?php

namespace Webkul\Attribute\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
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
        $lookupData = config('attribute_lookups.' . $lookup);

        if (!$lookupData) {
            return [];
        }

        $labelColumn = $lookupData['label_column'] ?? 'name';

        if (!count($columns)) {
            $columns = [($lookupData['value_column'] ?? 'id') . ' as id', $labelColumn . ' as name'];
        }

        $repository = app($lookupData['repository']);

        $query = urldecode($query);

        $queryBuilder = $repository->getModel()->newQuery();

        if ($query) {
            $queryBuilder->where($labelColumn, 'like', '%' . $query . '%');
        }

        $userIds = bouncer()->getAuthorizedUserIds();

        if ($userIds) {
            $column = Str::contains($lookupData['repository'], 'UserRepository') ? 'id' : 'user_id';

            if (in_array($lookup, ['persons', 'organizations', 'leads', 'users'])) {
                $queryBuilder->where(function ($q) use ($column, $userIds) {
                    $q->whereIn($column, $userIds)
                        ->orWhereNull($column);
                });
            }
        }

        if ($lookup === 'users') {
            $queryBuilder->where('status', 1);
        }

        return $queryBuilder->limit(20)->get($columns);
    }

    /**
     * @param  string  $lookup
     * @param  int|array  $entityId
     * @param  array  $columns
     * @return mixed
     */
    public function getLookUpEntity($lookup, $entityId = null, $columns = [])
    {
        if (!$entityId) {
            return;
        }

        $lookup = config('attribute_lookups.' . $lookup);

        if (!count($columns)) {
            $columns = [($lookup['value_column'] ?? 'id') . ' as id', ($lookup['label_column'] ?? 'name') . ' as name'];
        }

        if (is_array($entityId)) {
            return app($lookup['repository'])->findWhereIn(
                'id',
                $entityId,
                $columns
            );
        } else {
            return app($lookup['repository'])->find($entityId, $columns);
        }
    }
}
