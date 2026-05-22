<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['category_id', 'nama_produk', 'slug', 'deskripsi', 'harga', 'stok', 'gambar', 'status'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getProducts($status = 'aktif')
    {
        return $this->select('products.*, product_categories.nama_kategori')
                    ->join('product_categories', 'product_categories.id = products.category_id')
                    ->where('products.status', $status)
                    ->orderBy('products.created_at', 'DESC')
                    ->findAll();
    }

    public function getAllProducts()
    {
        return $this->select('products.*, product_categories.nama_kategori')
                    ->join('product_categories', 'product_categories.id = products.category_id')
                    ->orderBy('products.created_at', 'DESC')
                    ->findAll();
    }

    public function getProductBySlug($slug)
    {
        return $this->select('products.*, product_categories.nama_kategori, product_categories.slug as category_slug')
                    ->join('product_categories', 'product_categories.id = products.category_id')
                    ->where('products.slug', $slug)
                    ->where('products.status', 'aktif')
                    ->first();
    }

    public function getProductsByCategory($categoryId, $status = 'aktif')
    {
        return $this->select('products.*, product_categories.nama_kategori')
                    ->join('product_categories', 'product_categories.id = products.category_id')
                    ->where('products.category_id', $categoryId)
                    ->where('products.status', $status)
                    ->orderBy('products.created_at', 'DESC')
                    ->findAll();
    }
}
