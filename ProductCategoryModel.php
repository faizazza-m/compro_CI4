<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductCategoryModel extends Model
{
    protected $table            = 'product_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['nama_kategori', 'slug', 'deskripsi'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getCategories()
    {
        return $this->orderBy('nama_kategori', 'ASC')->findAll();
    }

    public function getCategoryBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    public function getCategoriesWithCount()
    {
        return $this->select('product_categories.*, COUNT(products.id) as total_produk')
                    ->join('products', 'products.category_id = product_categories.id', 'left')
                    ->groupBy('product_categories.id')
                    ->orderBy('nama_kategori', 'ASC')
                    ->findAll();
    }
}
