<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductCategoryModel;

class Home extends BaseController
{
    public function index()
    {
        $productModel  = new ProductModel();
        $categoryModel = new ProductCategoryModel();

        $data = [
            'title'      => 'Beranda',
            'products'   => $productModel->getProducts(),
            'categories' => $categoryModel->getCategoriesWithCount(),
        ];

        return view('frontend/home', $data);
    }
}
