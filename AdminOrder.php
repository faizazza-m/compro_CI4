<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\ProductModel;

class AdminOrder extends BaseController
{
    protected $orderModel;
    protected $orderDetailModel;

    public function __construct()
    {
        $this->orderModel       = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
    }

    public function index()
    {
        $status = $this->request->getGet('status');

        $data = [
            'title'         => 'Kelola Pesanan',
            'orders'        => $this->orderModel->getOrders($status),
            'currentStatus' => $status,
        ];

        return view('admin/order/index', $data);
    }

    public function detail($id)
    {
        $order = $this->orderModel->find($id);

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pesanan tidak ditemukan.');
        }

        $data = [
            'title'   => 'Detail Pesanan #' . $order['nomor_order'],
            'order'   => $order,
            'details' => $this->orderDetailModel->getDetailsByOrderId($id),
        ];

        return view('admin/order/detail', $data);
    }

    public function updateStatus($id)
    {
        $order = $this->orderModel->find($id);

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pesanan tidak ditemukan.');
        }

        $newStatus = $this->request->getPost('status');
        $validStatuses = ['pending', 'diproses', 'selesai', 'batal'];

        if (!in_array($newStatus, $validStatuses)) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        // If cancelling, restore product stock
        if ($newStatus === 'batal' && $order['status'] !== 'batal') {
            $details = $this->orderDetailModel->getDetailsByOrderId($id);
            $productModel = new ProductModel();
            foreach ($details as $detail) {
                $product = $productModel->find($detail['product_id']);
                if ($product) {
                    $productModel->update($detail['product_id'], [
                        'stok' => $product['stok'] + $detail['jumlah'],
                    ]);
                }
            }
        }

        $this->orderModel->update($id, [
            'status' => $newStatus,
        ]);

        return redirect()->to('/admin/pesanan/detail/' . $id)->with('success', 'Status pesanan berhasil diubah menjadi "' . $newStatus . '".');
    }
}
