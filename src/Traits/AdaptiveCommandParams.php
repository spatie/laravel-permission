<?php

namespace Spatie\Permission\Traits;

use Illuminate\Support\Str;
use Spatie\Permission\Contracts\Role as RoleContract;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

trait AdaptiveCommandParams
{
    /**
     * The built-in columns.
     *
     * @var array
     */
    protected $builtInFields = ['id', 'name', 'guard_name', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * The required extra columns.
     *
     * @var array
     */
    protected $requiredExtraFields = [];

    /**
     * The optional extra columns.
     *
     * @var array
     */
    protected $optionalExtraFields = [];

    /**
     * Register arguments or options for extra columns in database.
     *
     * @param object $model The model instance
     *
     * @return void
     */
    protected function registerCustomColumns($model)
    {
        $table     = $model->getTable();
        $columns   = $model->getConnection()->getDoctrineSchemaManager()->listTableColumns($table);
        $modelType = ($model instanceof RoleContract) ? 'role' : 'permission';

        foreach ($columns as $column) {
            // We will look for columns that are fillable and
            // are not the inherent columns of the package

            $columnName      = $column->getName();
            $isAutoIncrement = $column->getAutoincrement();

            if (!in_array($columnName, $this->builtInFields) && $model->isFillable($columnName) && !$isAutoIncrement) {
                $isNotNullable = $column->getNotnull();
                $defaultValue  = $column->getDefault();
                $columnLength  = $column->getLength();
                $columnType    = $column->getType();
                $suffixTitle   = ('boolean' == Str::slug($columnType) && Str::startsWith($columnName, 'is')) ? 'status' : null;
                $columnTitle   = is_null($suffixTitle) ? str_replace(['-', '_'], ' ', $columnName) : ($columnName . ' ' . $suffixTitle);

                if ($isNotNullable && is_null($defaultValue)) {
                    // This is the case that the column needs to fill in the value.
                    // We will register it in the "requiredExtraFields" property

                    $this->requiredExtraFields[] = [
                        $columnName,
                        InputArgument::REQUIRED,
                        'The ' . Str::lower($columnTitle) . ' of the ' . $modelType,
                    ];
                } else {
                    // This is the case that the column does not require or has a default value.
                    // We will register it in the "optionalExtraFields" property

                    $this->optionalExtraFields[] = [
                        $columnName,
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'The ' . Str::lower($columnTitle) . ' of the ' . $modelType,
                        $defaultValue,
                    ];
                }
            }
        }
    }

    /**
     * Get the input values of the command parameters.
     *
     * @return array
     */
    protected function getInputParams()
    {
        $params = [];

        foreach ($this->getArguments() as $argument) {
            $key   = $argument[0];
            $value = $this->argument($key);

            $params[$key] = $value;
        }

        foreach ($this->getOptions() as $option) {
            $key   = $option[0];
            $value = $this->option($key);

            if ('guard' == $key) {
                $params['guard_name'] = $value;
            } else {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
