<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueValue implements Rule
{
    protected $table;
    protected $column;
    protected $exceptId;

    public function __construct($table, $column, $exceptId = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->exceptId = $exceptId;
    }

    public function passes($attribute, $value)
    {
        $query = DB::table($this->table)->where($this->column, $value);

        if ($this->exceptId !== null) {
            $query->where('id', '!=', $this->exceptId);
        }

        return $query->count() === 0;
    }

    public function message()
    {
        return 'The :attribute is not unique.';
    }
}
