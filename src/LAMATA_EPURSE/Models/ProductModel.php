<?php

namespace LAMATA_EPURSE\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProductModel extends BaseModel
{
    // use SoftDeletes;

    protected $table = 'products';

    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';
    const DELETED_AT = 'is_deleted';

    // protected $dateFormat = 'U';


    public function user()
    {
        return $this->belongsTo(UserModel::class, "added_by_user_id");
    }

    public function getStruct()
    {
        return self::select('id', 'title', 'product_category', 'photo', 'description', 'amount', 'added_by_user_id', 'created_at', 'updated_at');
    }


    public function getAll($page, $limit)
    {
        $start = ($page - 1) * $limit;

        $productExists  = $this->where('id', '>', 0)->limit($limit)->exists();
        if (!$productExists) {
            return ['data' => null, 'error' => 'empty'];
        }

        $allmodels = $this->getStruct()
            ->where('id', '>', $start)
            ->limit($limit)
            ->get();

        $total = $this->count();


        return ['data' => ["total" => $total, "all" => $allmodels,], 'error' => ''];
    }
}
