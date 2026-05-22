<?php

namespace App\Controllers;

use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use App\Models\OrderModel;

class Admin extends BaseController
{
    public function index()
    {
        $productModel  = new ProductModel();
        $categoryModel = new ProductCategoryModel();
        $orderModel    = new OrderModel();

        $data = [
            'title'          => 'Dashboard Admin',
            'totalProduk'    => $productModel->countAllResults(),
            'totalKategori'  => $categoryModel->countAllResults(),
            'totalOrder'     => $orderModel->countAllResults(),
            'orderPending'   => $orderModel->countByStatus('pending'),
            'orderDiproses'  => $orderModel->countByStatus('diproses'),
            'orderSelesai'   => $orderModel->countByStatus('selesai'),
            'orderBatal'     => $orderModel->countByStatus('batal'),
            'recentOrders'   => $orderModel->orderBy('created_at', 'DESC')->findAll(5),
        ];

        return view('admin/dashboard', $data);
    }
}
