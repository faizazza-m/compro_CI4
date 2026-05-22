<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductCategoryModel;

class Produk extends BaseController
{
    protected $productModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->productModel  = new ProductModel();
        $this->categoryModel = new ProductCategoryModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Katalog Produk',
            'products'   => $this->productModel->getProducts(),
            'categories' => $this->categoryModel->getCategoriesWithCount(),
            'activeCategory' => null,
        ];

        return view('frontend/produk/index', $data);
    }

    public function kategori($slug)
    {
        $category = $this->categoryModel->getCategoryBySlug($slug);

        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        $data = [
            'title'          => 'Kategori: ' . $category['nama_kategori'],
            'products'       => $this->productModel->getProductsByCategory($category['id']),
            'categories'     => $this->categoryModel->getCategoriesWithCount(),
            'activeCategory' => $category,
        ];

        return view('frontend/produk/index', $data);
    }

    public function detail($slug)
    {
        $product = $this->productModel->getProductBySlug($slug);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        // Get related products from same category
        $relatedProducts = $this->productModel
            ->where('category_id', $product['category_id'])
            ->where('id !=', $product['id'])
            ->where('status', 'aktif')
            ->findAll(4);

        $data = [
            'title'           => $product['nama_produk'],
            'product'         => $product,
            'relatedProducts' => $relatedProducts,
        ];

        return view('frontend/produk/detail', $data);
    }
}
